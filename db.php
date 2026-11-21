<?php
/**
 * NagaraLive - Database & Environment Connection
 * Lightweight .env loader & PDO MySQL Connection (utf8mb4)
 */

declare(strict_types=1);

/**
 * Lightweight .env loader without external dependencies.
 * Parses KEY=VALUE pairs, strips surrounding quotes, and stores in $_ENV and putenv().
 */
function loadEnv(string $filePath = __DIR__ . '/.env'): void {
    if (!file_exists($filePath)) {
        // Also check if .env.example exists if .env is missing
        $examplePath = __DIR__ . '/.env.example';
        if (file_exists($examplePath)) {
            $filePath = $examplePath;
        } else {
            return;
        }
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        // Ignore comments and empty lines
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);

            // Strip enclosing quotes (single or double)
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $val;
                putenv("{$key}={$val}");
            }
        }
    }
}

// Auto-load environment on include
loadEnv();

/**
 * Retrieve environment variable with a fallback default.
 */
function getEnvVar(string $key, string $default = ''): string {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string)$_ENV[$key];
    }
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return (string)$val;
    }
    return $default;
}

/**
 * Establish a singleton or fresh PDO connection to the MySQL database.
 *
 * @throws PDOException
 * @return PDO
 */
function getDbConnection(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = getEnvVar('DB_HOST', 'localhost');
    $port = getEnvVar('DB_PORT', '3306');
    $db   = getEnvVar('DB_NAME', 'nagaralive');
    $user = getEnvVar('DB_USER', 'root');
    $pass = getEnvVar('DB_PASS', 'J@123Janithya?');

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
}
