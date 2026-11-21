<?php
/**
 * NagaraLive - Command Line Interface (CLI) Companion
 *
 * Terminal interface to query community status, fetch live Gemini AI briefs,
 * and post verified citizen alerts directly into the MySQL database.
 *
 * Usage:
 *   php cli.php status
 *   php cli.php brief
 *   php cli.php post "Message here" "Area" CATEGORY [TARGET_GROUP]
 *   php cli.php help
 */

declare(strict_types=1);

// Ensure this script runs only in CLI mode
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "This script can only be executed via the command line interface (CLI).\n";
    exit(1);
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/gemini.php';

// ANSI color codes for enhanced terminal output
class CliTheme {
    public static bool $enableColor = true;

    public static function color(string $text, string $colorCode): string {
        if (!self::$enableColor) {
            return $text;
        }
        return "\033[{$colorCode}m{$text}\033[0m";
    }

    public static function bold(string $text): string { return self::color($text, '1'); }
    public static function red(string $text): string { return self::color($text, '31'); }
    public static function green(string $text): string { return self::color($text, '32'); }
    public static function yellow(string $text): string { return self::color($text, '33'); }
    public static function blue(string $text): string { return self::color($text, '34'); }
    public static function magenta(string $text): string { return self::color($text, '35'); }
    public static function cyan(string $text): string { return self::color($text, '36'); }
    public static function gray(string $text): string { return self::color($text, '90'); }
}

// Check Windows console support for ANSI
if (DIRECTORY_SEPARATOR === '\\' && !getenv('ANSICON') && !getenv('WT_SESSION') && strpos(getenv('TERM') ?: '', 'xterm') === false) {
    // Standard Windows cmd without virtual terminal can sometimes display raw escape characters;
    // but Windows 10/11 PowerShell / Terminal natively support ANSI. We enable color.
}

function printHeader(): void {
    echo "\n" . CliTheme::cyan(CliTheme::bold("==================================================================")) . "\n";
    echo CliTheme::cyan(CliTheme::bold("  NAGARALIVE - Real-Time Neighborhood Community Pulse & Alerts    ")) . "\n";
    echo CliTheme::gray("  District: Gampaha, Western Province, Sri Lanka | Powered by Gemini") . "\n";
    echo CliTheme::cyan(CliTheme::bold("==================================================================")) . "\n\n";
}

function printHelp(): void {
    printHeader();
    echo CliTheme::bold("Available Commands:\n\n");
    echo "  " . CliTheme::green("php cli.php status") . "\n";
    echo "      Displays the 8 latest community posts, categories, and verification status.\n\n";

    echo "  " . CliTheme::green("php cli.php brief") . "\n";
    echo "      Generates and outputs the live Google Gemini Situation Brief and hazard level.\n\n";

    echo "  " . CliTheme::green("php cli.php post <message> <area> <category> [target_group]") . "\n";
    echo "      Analyzes via Gemini (translating & checking eyewitness), then saves to database.\n";
    echo "      - " . CliTheme::bold("Categories:") . " POWER_WATER | WEATHER_EMERGENCY | NOTICE | EVENT\n";
    echo "      - " . CliTheme::bold("Target Groups (Optional, for EVENT):") . " ALL | ELDERLY | CHILDREN | YOUTH\n\n";

    echo CliTheme::bold("Examples:\n");
    echo '  php cli.php post "Transformer exploded near petrol shed, current cut" "Yakkala" POWER_WATER' . "\n";
    echo '  php cli.php post "Elderly eye clinic at Ganemulla Hall" "Ganemulla" EVENT ELDERLY' . "\n";
    echo '  php cli.php post "Heavy rain causing water accumulation on Kandy road" "Miriswatta" WEATHER_EMERGENCY' . "\n\n";
}

// --------------------------------------------------------------------
// COMMAND: STATUS
// --------------------------------------------------------------------
function handleStatus(): void {
    printHeader();
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT id, area, category, target_group, raw_message, translated_message, 
                   is_eyewitness, confirmations, status, created_at 
            FROM posts 
            ORDER BY created_at DESC 
            LIMIT 8
        ");
        $stmt->execute();
        $posts = $stmt->fetchAll();

        if (empty($posts)) {
            echo CliTheme::yellow("No posts found in database.\n\n");
            return;
        }

        echo CliTheme::bold("Displaying Latest 8 Community Posts:\n");
        echo CliTheme::gray(str_repeat("-", 70)) . "\n\n";

        foreach ($posts as $idx => $p) {
            $num = $idx + 1;
            $id = $p['id'];
            $area = CliTheme::bold(CliTheme::cyan("[{$p['area']}]"));

            // Category tag styling
            $catTag = match ($p['category']) {
                'POWER_WATER'       => CliTheme::yellow("[POWER & WATER]"),
                'WEATHER_EMERGENCY' => CliTheme::red("[WEATHER ALERT]"),
                'NOTICE'            => CliTheme::blue("[NOTICE]"),
                'EVENT'             => CliTheme::magenta("[EVENT" . ($p['target_group'] !== 'ALL' ? ": {$p['target_group']}" : "") . "]"),
                default             => "[{$p['category']}]"
            };

            // Status tag
            $statusTag = ($p['status'] === 'RESOLVED')
                ? CliTheme::gray("[RESOLVED]")
                : CliTheme::green("[ACTIVE]");

            // Verification badge
            $confCount = (int)$p['confirmations'];
            $verifiedTag = ($confCount >= 3)
                ? CliTheme::green("✓ Verified ({$confCount} confirms)")
                : CliTheme::gray("({$confCount} confirms)");

            $eyewitnessTag = $p['is_eyewitness'] ? CliTheme::cyan("👁 Eyewitness") : "";

            echo "{$num}. #{$id} {$area} {$catTag} {$statusTag} - {$verifiedTag} {$eyewitnessTag}\n";

            // Print primary translated/English message
            $displayMsg = !empty($p['translated_message']) ? $p['translated_message'] : $p['raw_message'];
            echo "   " . CliTheme::bold($displayMsg) . "\n";

            // If original raw message differs, print as secondary reference
            if (!empty($p['translated_message']) && $p['translated_message'] !== $p['raw_message']) {
                echo "   " . CliTheme::gray("Original: \"" . $p['raw_message'] . "\"") . "\n";
            }

            echo "   " . CliTheme::gray("Reported: " . $p['created_at']) . "\n\n";
        }

        echo CliTheme::gray(str_repeat("-", 70)) . "\n";
        echo "Tip: Run " . CliTheme::green("php cli.php brief") . " to view the live Gemini executive brief.\n\n";

    } catch (PDOException $e) {
        echo CliTheme::red("Database Error: " . $e->getMessage()) . "\n";
        echo "Please verify MySQL is running and credentials in .env are correct.\n\n";
        exit(1);
    }
}

// --------------------------------------------------------------------
// COMMAND: BRIEF
// --------------------------------------------------------------------
function handleBrief(): void {
    printHeader();
    try {
        $pdo = getDbConnection();
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

        echo CliTheme::bold("Generating Gemini Community Situation Brief...\n");
        echo CliTheme::gray("Synthesizing " . count($activePosts) . " active neighborhood alerts from Gampaha District...") . "\n\n";

        $brief = generateSituationBrief($activePosts);
        $level = $brief['hazard_level'] ?? 'Normal';

        $hazardBadge = match (strtoupper($level)) {
            'CRITICAL' => CliTheme::red(CliTheme::bold(" [!] HAZARD LEVEL: CRITICAL ")),
            'CAUTION'  => CliTheme::yellow(CliTheme::bold(" [!] HAZARD LEVEL: CAUTION ")),
            default    => CliTheme::green(CliTheme::bold(" [OK] HAZARD LEVEL: NORMAL "))
        };

        echo "Status: {$hazardBadge}\n";
        echo CliTheme::gray("Timestamp: " . date('Y-m-d H:i:s T')) . "\n";
        echo CliTheme::cyan(str_repeat("-", 70)) . "\n\n";

        if (!empty($brief['bullets'])) {
            foreach ($brief['bullets'] as $bullet) {
                echo " • " . $bullet . "\n\n";
            }
        }

        echo CliTheme::cyan(str_repeat("-", 70)) . "\n\n";

    } catch (Exception $e) {
        echo CliTheme::red("Error generating brief: " . $e->getMessage()) . "\n\n";
        exit(1);
    }
}

// --------------------------------------------------------------------
// COMMAND: POST
// --------------------------------------------------------------------
function handlePost(array $args): void {
    printHeader();

    if (count($args) < 3) {
        echo CliTheme::red("Error: Missing required arguments.") . "\n\n";
        echo "Usage:\n";
        echo '  php cli.php post "<Message>" "<Area>" <CATEGORY> [TARGET_GROUP]' . "\n\n";
        echo "Valid Categories:\n";
        echo "  POWER_WATER | WEATHER_EMERGENCY | NOTICE | EVENT\n\n";
        exit(1);
    }

    $rawMessage = trim($args[0]);
    $area       = trim($args[1]);
    $category   = strtoupper(trim($args[2]));
    $targetGroup = isset($args[3]) ? strtoupper(trim($args[3])) : 'ALL';

    $validCategories = ['EVENT', 'NOTICE', 'WEATHER_EMERGENCY', 'POWER_WATER'];
    if (!in_array($category, $validCategories, true)) {
        echo CliTheme::red("Error: Invalid category '{$category}'.") . "\n";
        echo "Must be one of: " . implode(', ', $validCategories) . "\n\n";
        exit(1);
    }

    $validTargetGroups = ['ALL', 'ELDERLY', 'CHILDREN', 'YOUTH'];
    if (!in_array($targetGroup, $validTargetGroups, true) || $category !== 'EVENT') {
        $targetGroup = 'ALL';
    }

    echo CliTheme::cyan("Analyzing report via Google Gemini (gemini-3.8-flash)...") . "\n";
    $aiResult = analyzeReport($rawMessage);

    $translated = !empty($aiResult['translated']) ? $aiResult['translated'] : $rawMessage;
    $isEyewitness = !empty($aiResult['is_eyewitness']) ? 1 : 0;

    echo "  • Translation : " . CliTheme::bold($translated) . "\n";
    echo "  • Eyewitness  : " . ($isEyewitness ? CliTheme::green("Yes (First-hand local observation)") : CliTheme::yellow("General/Informational")) . "\n\n";

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO posts (area, category, target_group, raw_message, translated_message, is_eyewitness, confirmations, status, created_at)
            VALUES (:area, :category, :target_group, :raw_message, :translated_message, :is_eyewitness, 1, 'ACTIVE', NOW())
        ");
        $stmt->execute([
            'area'               => $area,
            'category'           => $category,
            'target_group'       => $targetGroup,
            'raw_message'        => $rawMessage,
            'translated_message' => $translated,
            'is_eyewitness'      => $isEyewitness
        ]);

        $newId = (int)$pdo->lastInsertId();

        echo CliTheme::green(CliTheme::bold("SUCCESS: Post #{$newId} recorded to live pulse board!")) . "\n";
        echo "  - Area     : {$area}\n";
        echo "  - Category : {$category}\n";
        if ($category === 'EVENT') {
            echo "  - Target   : {$targetGroup}\n";
        }
        echo "  - Status   : ACTIVE\n\n";

    } catch (PDOException $e) {
        echo CliTheme::red("Database Insertion Error: " . $e->getMessage()) . "\n\n";
        exit(1);
    }
}

// --------------------------------------------------------------------
// ARGUMENT DISPATCHER
// --------------------------------------------------------------------
$command = isset($argv[1]) ? strtolower(trim($argv[1])) : 'help';

switch ($command) {
    case 'status':
        handleStatus();
        break;

    case 'brief':
        handleBrief();
        break;

    case 'post':
        handlePost(array_slice($argv, 2));
        break;

    case 'help':
    case '--help':
    case '-h':
    default:
        printHelp();
        break;
}
