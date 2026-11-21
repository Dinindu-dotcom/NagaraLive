<?php
/**
 * NagaraLive - Google Gemini API Integration
 * Model: gemini-3.8-flash via REST API (cURL)
 *
 * Provides real-time translation & eyewitness verification of community reports
 * and generates live situational intelligence briefs.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Execute a cURL request to the Google Gemini generateContent endpoint.
 *
 * @param array $payload Request payload
 * @return array|null Decoded JSON response or null on failure
 */
function callGeminiApi(array $payload): ?array {
    $apiKey = getEnvVar('GEMINI_API_KEY', '');
    
    // Check if key is missing or placeholder
    if (empty($apiKey) || $apiKey === '') {
        return null;
    }

    $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $endpoint,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json; charset=utf-8',
            'x-goog-api-key: ' . $apiKey
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        error_log("Gemini API call failed (HTTP {$httpCode}): " . ($curlError ?: (string)$response));
        return null;
    }

    $decoded = json_decode((string)$response, true);
    return is_array($decoded) ? $decoded : null;
}

/**
 * Extract clean JSON string from Gemini's candidate text response.
 */
function extractJsonFromGeminiCandidate(?array $geminiResponse): ?array {
    if (!$geminiResponse || empty($geminiResponse['candidates'][0]['content']['parts'][0]['text'])) {
        return null;
    }

    $rawText = trim($geminiResponse['candidates'][0]['content']['parts'][0]['text']);

    // Remove markdown code block wrappers if present (e.g. ```json ... ```)
    if (str_starts_with($rawText, '```')) {
        $rawText = preg_replace('/^```(?:json)?\s*/i', '', $rawText);
        $rawText = preg_replace('/\s*```$/', '', (string)$rawText);
        $rawText = trim((string)$rawText);
    }

    $data = json_decode($rawText, true);
    return is_array($data) ? $data : null;
}

/**
 * Analyze a newly submitted neighborhood report.
 * Translates Sinhala, Singlish, Tamil, or informal English into clear standard English
 * and detects whether the text describes a first-hand eyewitness account.
 *
 * @param string $rawMessage The original user report
 * @return array Array containing ['translated' => string, 'is_eyewitness' => bool]
 */
function analyzeReport(string $rawMessage): array {
    $rawMessage = trim($rawMessage);
    if ($rawMessage === '') {
        return [
            'translated' => '',
            'is_eyewitness' => false
        ];
    }

    $prompt = <<<PROMPT
You are a community intelligence assistant for NagaraLive, a neighborhood pulse board in Sri Lanka.
Analyze the following citizen report which may be written in Sinhala, Singlish (Sinhala in Latin script), Tamil, or English:

"""
{$rawMessage}
"""

Instructions:
1. "translated": Translate the message into clear, concise, fluent standard English. If the text is already clear English, refine and normalize it without losing meaning. Preserve local place names (e.g., Gampaha, Yakkala, Miriswatta).
2. "is_eyewitness": Return true if this appears to be a direct first-hand local observation or eyewitness account (e.g., "I just saw", "happening now", "transformer exploded right here", "tree fell blocking the road", "water cut here"). Return false if it is merely general commentary, forwarded rumor, or second-hand hearsay.

Respond ONLY with a valid JSON object matching this exact schema:
{
  "translated": "Clear English translation here",
  "is_eyewitness": true
}
PROMPT;

    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'responseMimeType' => 'application/json',
            'temperature'      => 0.2
        ]
    ];

    $response = callGeminiApi($payload);
    $parsed = extractJsonFromGeminiCandidate($response);

    if ($parsed && isset($parsed['translated'])) {
        return [
            'translated'    => (string)$parsed['translated'],
            'is_eyewitness' => !empty($parsed['is_eyewitness'])
        ];
    }

    // --- Graceful Rule-Based Fallback if Gemini API is offline or missing ---
    return fallbackAnalyzeReport($rawMessage);
}

/**
 * Rule-based fallback analyzer when Gemini API is unavailable.
 */
function fallbackAnalyzeReport(string $rawMessage): array {
    $lower = mb_strtolower($rawMessage, 'UTF-8');

    // Heuristics for eyewitness detection in Sinhala, Singlish, and English
    $eyewitnessKeywords = [
        'dakka', 'dan', 'balan', 'pipuruwa', 'kadunada', 'wuna', 'penawa',
        'mama', 'api', 'junction', 'road', 'here', 'now', 'just', 'saw',
        'tree fell', 'exploded', 'burst', 'happening', 'traffic', 'blocked',
        'දැක්කා', 'දැන්', 'කඩා', 'වැටී', 'පිපිරී', 'ඇනහිට'
    ];

    $isEyewitness = false;
    foreach ($eyewitnessKeywords as $kw) {
        if (str_contains($lower, $kw)) {
            $isEyewitness = true;
            break;
        }
    }

    // If no translation possible offline, keep original text
    return [
        'translated'    => $rawMessage,
        'is_eyewitness' => $isEyewitness ?: true // Default to true for citizen reports
    ];
}

/**
 * Generate a neighborhood situation brief summarizing recent active community posts.
 *
 * @param array $activePosts Up to 15 active posts from the database
 * @return array Array containing ['hazard_level' => 'Normal'|'Caution'|'Critical', 'bullets' => string[]]
 */
function generateSituationBrief(array $activePosts): array {
    if (empty($activePosts)) {
        return [
            'hazard_level' => 'Normal',
            'bullets'      => [
                'All neighborhood sectors reporting normal conditions.',
                'No active utility interruptions or weather emergencies reported.',
                'Check back for community events and official public notices.'
            ]
        ];
    }

    // Format active posts for Gemini prompt
    $postsSummary = [];
    foreach (array_slice($activePosts, 0, 15) as $idx => $p) {
        $area = $p['area'] ?? 'Unknown Area';
        $cat = $p['category'] ?? 'NOTICE';
        $target = $p['target_group'] ?? 'ALL';
        $msg = !empty($p['translated_message']) ? $p['translated_message'] : ($p['raw_message'] ?? '');
        $conf = $p['confirmations'] ?? 1;
        $postsSummary[] = ($idx + 1) . ". [Area: {$area}] [Category: {$cat}] [Target: {$target}] [Confirmations: {$conf}]: {$msg}";
    }

    $promptText = "Here are the most recent active neighborhood reports from Sri Lankan communities (Gampaha District):\n"
        . implode("\n", $postsSummary) . "\n\n"
        . "Synthesize these reports into an executive Community Situation Brief.\n"
        . "1. Determine 'hazard_level':\n"
        . "   - 'Critical' if there are severe weather hazards, flash floods, or blocked major roads.\n"
        . "   - 'Caution' if there are power/water disruptions, dengue fogging notices, or minor hazards.\n"
        . "   - 'Normal' if operations are normal with only routine notices or community events.\n"
        . "2. 'bullets': Provide 2 to 4 concise bullet points (each 1-2 clear sentences) summarizing active hazards/utilities and highlighting community events (including mentions of activities for elderly, children, or youth).\n\n"
        . "Respond ONLY with a JSON object matching this exact structure:\n"
        . "{\n"
        . "  \"hazard_level\": \"Normal\",\n"
        . "  \"bullets\": [\n"
        . "    \"Bullet 1...\",\n"
        . "    \"Bullet 2...\",\n"
        . "    \"Bullet 3...\"\n"
        . "  ]\n"
        . "}";

    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $promptText]
                ]
            ]
        ],
        'generationConfig' => [
            'responseMimeType' => 'application/json',
            'temperature'      => 0.2
        ]
    ];

    $response = callGeminiApi($payload);
    $parsed = extractJsonFromGeminiCandidate($response);

    if ($parsed && isset($parsed['hazard_level']) && isset($parsed['bullets']) && is_array($parsed['bullets'])) {
        $validLevels = ['Normal', 'Caution', 'Critical'];
        $level = in_array($parsed['hazard_level'], $validLevels, true) ? $parsed['hazard_level'] : 'Caution';
        return [
            'hazard_level' => $level,
            'bullets'      => array_values(array_map('strval', $parsed['bullets']))
        ];
    }

    // --- Graceful Rule-Based Fallback if Gemini API is offline or missing ---
    return fallbackSituationBrief($activePosts);
}

/**
 * Rule-based fallback situation brief generator when Gemini API is unavailable.
 */
function fallbackSituationBrief(array $activePosts): array {
    $hasWeather = false;
    $hasUtility = false;
    $weatherAreas = [];
    $utilityAreas = [];
    $eventSummaries = [];

    foreach ($activePosts as $post) {
        $cat = $post['category'] ?? '';
        $area = $post['area'] ?? '';
        $target = $post['target_group'] ?? 'ALL';
        $msg = $post['translated_message'] ?? $post['raw_message'] ?? '';

        if ($cat === 'WEATHER_EMERGENCY') {
            $hasWeather = true;
            if ($area && !in_array($area, $weatherAreas, true)) {
                $weatherAreas[] = $area;
            }
        } elseif ($cat === 'POWER_WATER') {
            $hasUtility = true;
            if ($area && !in_array($area, $utilityAreas, true)) {
                $utilityAreas[] = $area;
            }
        } elseif ($cat === 'EVENT') {
            $label = ($target !== 'ALL') ? " ({$target} focus)" : '';
            $eventSummaries[] = "{$area}: " . substr($msg, 0, 75) . "...{$label}";
        }
    }

    $hazardLevel = 'Normal';
    if ($hasWeather) {
        $hazardLevel = 'Critical';
    } elseif ($hasUtility) {
        $hazardLevel = 'Caution';
    }

    $bullets = [];

    if ($hasWeather) {
        $areasStr = implode(', ', $weatherAreas);
        $bullets[] = "Weather Emergency Alert: Active weather hazards and road obstructions reported in {$areasStr}. Exercise heightened caution.";
    } else {
        $bullets[] = "Weather & Infrastructure: No severe road blockages or weather emergencies currently reported.";
    }

    if ($hasUtility) {
        $areasStr = implode(', ', $utilityAreas);
        $bullets[] = "Utility Outages: Electricity or water supply interruptions active in {$areasStr}. Restoration crews notified.";
    } else {
        $bullets[] = "Utilities: Municipal power and water networks operating normally across monitored zones.";
    }

    if (!empty($eventSummaries)) {
        $bullets[] = "Community Pulse: " . implode(' | ', array_slice($eventSummaries, 0, 2));
    } else {
        $bullets[] = "Community Notice: Check notice board for health guidelines and upcoming municipal programs.";
    }

    return [
        'hazard_level' => $hazardLevel,
        'bullets'      => $bullets
    ];
}
