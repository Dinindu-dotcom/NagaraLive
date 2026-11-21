<?php
/**
 * NagaraLive - REST API Backend
 * Handles Public Append-Only Actions & Admin-Only Moderation
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Admin-Passcode');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/gemini.php';

// Helper to send standardized JSON response
function sendJsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Helper to parse input parameters (both JSON payload and $_REQUEST)
function getRequestData(): array {
    $data = $_REQUEST;
    $rawInput = file_get_contents('php://input');
    if ($rawInput) {
        $json = json_decode($rawInput, true);
        if (is_array($json)) {
            $data = array_merge($data, $json);
        }
    }
    return $data;
}

// Verify if the incoming request is authenticated as an Administrator
function isAdminAuthenticated(array $data): bool {
    $configuredPass = getEnvVar('ADMIN_PASSCODE', 'admin123');

    // Check custom HTTP header
    $headerPass = $_SERVER['HTTP_X_ADMIN_PASSCODE'] ?? null;
    if ($headerPass !== null && hash_equals($configuredPass, (string)$headerPass)) {
        return true;
    }

    // Check payload passcode
    if (!empty($data['admin_passcode']) && hash_equals($configuredPass, (string)$data['admin_passcode'])) {
        return true;
    }

    return false;
}

try {
    $pdo = getDbConnection();
} catch (PDOException $e) {
    sendJsonResponse([
        'success' => false,
        'error'   => 'Database connection failed: ' . $e->getMessage()
    ], 500);
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$requestData = getRequestData();
if (isset($requestData['action'])) {
    $action = (string)$requestData['action'];
}

switch ($action) {

    // -------------------------------------------------------------
    // LIST POSTS (Public Read)
    // -------------------------------------------------------------
    case 'list':
        try {
            $category    = $requestData['category'] ?? 'ALL';
            $targetGroup = $requestData['target_group'] ?? 'ALL';
            $area        = $requestData['area'] ?? 'ALL';
            $status      = $requestData['status'] ?? 'ACTIVE'; // 'ACTIVE', 'RESOLVED', 'ALL'
            $search      = trim($requestData['search'] ?? '');

            $sql = "SELECT id, area, category, target_group, raw_message, translated_message, 
                           is_eyewitness, confirmations, status, created_at 
                    FROM posts WHERE 1=1";
            $params = [];

            if ($status !== 'ALL') {
                $sql .= " AND status = :status";
                $params['status'] = $status;
            }

            if ($category !== 'ALL') {
                $sql .= " AND category = :category";
                $params['category'] = $category;
            }

            // Target group filtering (primarily for EVENT category)
            if ($targetGroup !== 'ALL') {
                $sql .= " AND target_group = :target_group";
                $params['target_group'] = $targetGroup;
            }

            if ($area !== 'ALL' && $area !== '') {
                $sql .= " AND area = :area";
                $params['area'] = $area;
            }

            if ($search !== '') {
                $sql .= " AND (raw_message LIKE :search1 OR translated_message LIKE :search2 OR area LIKE :search3)";
                $like = '%' . $search . '%';
                $params['search1'] = $like;
                $params['search2'] = $like;
                $params['search3'] = $like;
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $posts = $stmt->fetchAll();

            // Cast numeric/boolean fields properly
            foreach ($posts as &$p) {
                $p['id'] = (int)$p['id'];
                $p['is_eyewitness'] = (bool)$p['is_eyewitness'];
                $p['confirmations'] = (int)$p['confirmations'];
            }

            sendJsonResponse([
                'success' => true,
                'count'   => count($posts),
                'data'    => $posts
            ]);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
        break;

    // -------------------------------------------------------------
    // CREATE POST (Public Append-Only)
    // -------------------------------------------------------------
    case 'create':
        $area = trim($requestData['area'] ?? '');
        $category = trim($requestData['category'] ?? '');
        $targetGroup = trim($requestData['target_group'] ?? 'ALL');
        $rawMessage = trim($requestData['raw_message'] ?? '');

        if ($area === '' || $category === '' || $rawMessage === '') {
            sendJsonResponse([
                'success' => false,
                'error'   => 'Area, category, and message are required fields.'
            ], 400);
        }

        $validCategories = ['EVENT', 'NOTICE', 'WEATHER_EMERGENCY', 'POWER_WATER'];
        if (!in_array($category, $validCategories, true)) {
            sendJsonResponse(['success' => false, 'error' => 'Invalid category specified.'], 400);
        }

        $validTargetGroups = ['ALL', 'ELDERLY', 'CHILDREN', 'YOUTH'];
        if (!in_array($targetGroup, $validTargetGroups, true) || $category !== 'EVENT') {
            $targetGroup = 'ALL';
        }

        // Call Gemini AI analysis
        $aiAnalysis = analyzeReport($rawMessage);
        $translatedMessage = !empty($aiAnalysis['translated']) ? $aiAnalysis['translated'] : $rawMessage;
        $isEyewitness = !empty($aiAnalysis['is_eyewitness']) ? 1 : 0;

        try {
            $stmt = $pdo->prepare("
                INSERT INTO posts (area, category, target_group, raw_message, translated_message, is_eyewitness, confirmations, status, created_at)
                VALUES (:area, :category, :target_group, :raw_message, :translated_message, :is_eyewitness, 1, 'ACTIVE', NOW())
            ");
            $stmt->execute([
                'area'               => $area,
                'category'           => $category,
                'target_group'       => $targetGroup,
                'raw_message'        => $rawMessage,
                'translated_message' => $translatedMessage,
                'is_eyewitness'      => $isEyewitness
            ]);

            $newId = (int)$pdo->lastInsertId();

            $fetchStmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
            $fetchStmt->execute(['id' => $newId]);
            $newPost = $fetchStmt->fetch();
            if ($newPost) {
                $newPost['id'] = (int)$newPost['id'];
                $newPost['is_eyewitness'] = (bool)$newPost['is_eyewitness'];
                $newPost['confirmations'] = (int)$newPost['confirmations'];
            }

            sendJsonResponse([
                'success' => true,
                'message' => 'Neighborhood note posted successfully.',
                'post'    => $newPost
            ], 201);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
        break;

    // -------------------------------------------------------------
    // CONFIRM POST (+1 by Public Resident)
    // -------------------------------------------------------------
    case 'confirm':
        $id = (int)($requestData['id'] ?? 0);
        if ($id <= 0) {
            sendJsonResponse(['success' => false, 'error' => 'Valid Post ID is required.'], 400);
        }

        try {
            $updateStmt = $pdo->prepare("
                UPDATE posts 
                SET confirmations = confirmations + 1 
                WHERE id = :id
            ");
            $updateStmt->execute(['id' => $id]);

            if ($updateStmt->rowCount() === 0) {
                sendJsonResponse(['success' => false, 'error' => 'Post not found.'], 404);
            }

            $getStmt = $pdo->prepare("SELECT confirmations FROM posts WHERE id = :id");
            $getStmt->execute(['id' => $id]);
            $row = $getStmt->fetch();
            $newCount = (int)($row['confirmations'] ?? 1);

            sendJsonResponse([
                'success'       => true,
                'id'            => $id,
                'confirmations' => $newCount,
                'is_verified'   => $newCount >= 3
            ]);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
        break;

    // -------------------------------------------------------------
    // LIVE COMMUNITY SITUATION BRIEF (Gemini AI)
    // -------------------------------------------------------------
    case 'brief':
        try {
            // Fetch the 15 most recent active posts
            $stmt = $pdo->prepare("
                SELECT id, area, category, target_group, raw_message, translated_message, 
                       confirmations, created_at 
                FROM posts 
                WHERE status = 'ACTIVE' 
                ORDER BY created_at DESC 
                LIMIT 15
            ");
            $stmt->execute();
            $activePosts = $stmt->fetchAll();

            $brief = generateSituationBrief($activePosts);

            sendJsonResponse([
                'success'      => true,
                'brief'        => $brief,
                'active_count' => count($activePosts),
                'generated_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
        break;

    // -------------------------------------------------------------
    // ADMIN VERIFY PASSCODE
    // -------------------------------------------------------------
    case 'verify_admin':
        $isAuth = isAdminAuthenticated($requestData);
        sendJsonResponse([
            'success'    => true,
            'authorized' => $isAuth
        ]);
        break;

    // -------------------------------------------------------------
    // ADMIN UPDATE POST (Admin Only)
    // -------------------------------------------------------------
    case 'update':
        if (!isAdminAuthenticated($requestData)) {
            sendJsonResponse([
                'success' => false,
                'error'   => 'Unauthorized: Public residents cannot edit posts. Admin passcode required.'
            ], 403);
        }

        $id = (int)($requestData['id'] ?? 0);
        if ($id <= 0) {
            sendJsonResponse(['success' => false, 'error' => 'Valid Post ID is required.'], 400);
        }

        $area              = trim($requestData['area'] ?? '');
        $category          = trim($requestData['category'] ?? '');
        $targetGroup       = trim($requestData['target_group'] ?? 'ALL');
        $rawMessage        = trim($requestData['raw_message'] ?? '');
        $translatedMessage = trim($requestData['translated_message'] ?? '');
        $status            = trim($requestData['status'] ?? 'ACTIVE');

        if ($area === '' || $category === '' || $rawMessage === '') {
            sendJsonResponse(['success' => false, 'error' => 'Area, category, and message are required.'], 400);
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE posts 
                SET area = :area,
                    category = :category,
                    target_group = :target_group,
                    raw_message = :raw_message,
                    translated_message = :translated_message,
                    status = :status
                WHERE id = :id
            ");
            $stmt->execute([
                'area'               => $area,
                'category'           => $category,
                'target_group'       => $targetGroup,
                'raw_message'        => $rawMessage,
                'translated_message' => $translatedMessage ?: $rawMessage,
                'status'             => ($status === 'RESOLVED') ? 'RESOLVED' : 'ACTIVE',
                'id'                 => $id
            ]);

            sendJsonResponse([
                'success' => true,
                'message' => 'Post updated successfully by administrator.'
            ]);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
        break;

    // -------------------------------------------------------------
    // ADMIN TOGGLE RESOLVE STATUS (Admin Only)
    // -------------------------------------------------------------
    case 'resolve':
        if (!isAdminAuthenticated($requestData)) {
            sendJsonResponse([
                'success' => false,
                'error'   => 'Unauthorized: Public residents cannot modify post status. Admin passcode required.'
            ], 403);
        }

        $id = (int)($requestData['id'] ?? 0);
        $newStatus = ($requestData['status'] ?? 'RESOLVED') === 'ACTIVE' ? 'ACTIVE' : 'RESOLVED';

        if ($id <= 0) {
            sendJsonResponse(['success' => false, 'error' => 'Valid Post ID is required.'], 400);
        }

        try {
            $stmt = $pdo->prepare("UPDATE posts SET status = :status WHERE id = :id");
            $stmt->execute(['status' => $newStatus, 'id' => $id]);

            sendJsonResponse([
                'success'    => true,
                'id'         => $id,
                'new_status' => $newStatus,
                'message'    => "Post marked as {$newStatus}."
            ]);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
        break;

    // -------------------------------------------------------------
    // ADMIN DELETE POST (Admin Only)
    // -------------------------------------------------------------
    case 'delete':
        if (!isAdminAuthenticated($requestData)) {
            sendJsonResponse([
                'success' => false,
                'error'   => 'Unauthorized: Public residents cannot delete posts. Admin passcode required.'
            ], 403);
        }

        $id = (int)($requestData['id'] ?? 0);
        if ($id <= 0) {
            sendJsonResponse(['success' => false, 'error' => 'Valid Post ID is required.'], 400);
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() === 0) {
                sendJsonResponse(['success' => false, 'error' => 'Post not found.'], 404);
            }

            sendJsonResponse([
                'success' => true,
                'id'      => $id,
                'message' => 'Post permanently deleted by administrator.'
            ]);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
        break;

    // -------------------------------------------------------------
    // DISTRICT STATS SUMMARY
    // -------------------------------------------------------------
    case 'stats':
        try {
            $counts = [
                'total_active'       => 0,
                'power_water'        => 0,
                'weather_emergency'  => 0,
                'notices'            => 0,
                'events'             => 0,
                'resolved'           => 0
            ];

            $stmt = $pdo->query("
                SELECT category, status, COUNT(*) as count 
                FROM posts 
                GROUP BY category, status
            ");
            $rows = $stmt->fetchAll();

            foreach ($rows as $r) {
                $c = (int)$r['count'];
                if ($r['status'] === 'RESOLVED') {
                    $counts['resolved'] += $c;
                } else {
                    $counts['total_active'] += $c;
                    if ($r['category'] === 'POWER_WATER') $counts['power_water'] += $c;
                    if ($r['category'] === 'WEATHER_EMERGENCY') $counts['weather_emergency'] += $c;
                    if ($r['category'] === 'NOTICE') $counts['notices'] += $c;
                    if ($r['category'] === 'EVENT') $counts['events'] += $c;
                }
            }

            sendJsonResponse([
                'success' => true,
                'stats'   => $counts
            ]);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
        break;

    default:
        sendJsonResponse([
            'success' => false,
            'error'   => 'Unknown API action requested.'
        ], 400);
}
