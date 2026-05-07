<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'databaseedumatch';
const DB_FALLBACK_NAME = 'database_edumatch';
const DB_USER = 'root';
const DB_PASSWORD = '';

// External Webhook for partner badges (n8n or Make.com)
const EXTERNAL_BADGE_WEBHOOK = 'http://localhost:5678/webhook-test/Badges';

function getConnexion(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $databaseNames = [DB_NAME, DB_FALLBACK_NAME];
    $lastException = null;

    foreach ($databaseNames as $databaseName) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_PORT,
                $databaseName
            );

            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return $pdo;
        } catch (PDOException $exception) {
            $lastException = $exception;
        }
    }

    throw new RuntimeException('Unable to connect to the database.', 0, $lastException);
}

function startSessionIfNeeded(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function getBasePath(): string
{
    static $cachedBasePath = null;

    if ($cachedBasePath !== null) {
        return $cachedBasePath;
    }

    $documentRoot = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $applicationRoot = realpath(__DIR__);

    if ($documentRoot !== false && $applicationRoot !== false) {
        $normalizedDocRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
        $normalizedAppRoot = rtrim(str_replace('\\', '/', $applicationRoot), '/');

        if ($normalizedDocRoot !== '' && strpos($normalizedAppRoot, $normalizedDocRoot) === 0) {
            $relative = substr($normalizedAppRoot, strlen($normalizedDocRoot));
            $relative = trim((string)$relative, '/');
            $cachedBasePath = $relative === '' ? '' : '/' . $relative;

            return $cachedBasePath;
        }
    }

    $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $fallbackBasePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    if ($fallbackBasePath === '.' || $fallbackBasePath === '/') {
        $cachedBasePath = '';
        return $cachedBasePath;
    }

    $cachedBasePath = $fallbackBasePath;
    return $cachedBasePath;
}

function appUrl(array $query = []): string
{
    $basePath = getBasePath();
    $url = ($basePath !== '' ? $basePath : '') . '/index.php';

    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    return $url;
}

function assetUrl(string $path): string
{
    $basePath = getBasePath();
    return ($basePath !== '' ? $basePath : '') . '/' . ltrim($path, '/');
}

function redirectTo(array $query = []): void
{
    header('Location: ' . appUrl($query));
    exit;
}

function addFlashMessage(string $type, string $message): void
{
    startSessionIfNeeded();

    if (!isset($_SESSION['flash_messages']) || !is_array($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }

    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pullFlashMessages(): array
{
    startSessionIfNeeded();

    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);

    return is_array($messages) ? $messages : [];
}

function rememberOldInput(array $input): void
{
    startSessionIfNeeded();
    $_SESSION['old_input'] = $input;
}

function pullOldInput(): array
{
    startSessionIfNeeded();

    $oldInput = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);

    return is_array($oldInput) ? $oldInput : [];
}

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Loads environment variables from .env file
 */
function loadEnv(): void
{
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) return;

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
        putenv(trim($name) . "=" . trim($value));
    }
}

// Load .env variables on startup
loadEnv();
