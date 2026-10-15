<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — ERROR LOGGING & SENTRY MONITORING HELPER
 * Provides unified file-based rotating logs and optional Sentry DSN exception reporting.
 */

require_once __DIR__ . '/../config/db.php';

function getLogDir(): string {
    $dir = dirname(__DIR__) . '/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir;
}

/**
 * Log message with severity and context
 */
function logMessage(string $level, string $message, array $context = [], ?Throwable $exception = null): void {
    $date = date('Y-m-d');
    $logFile = getLogDir() . "/app-{$date}.log";

    $timestamp = date('Y-m-d H:i:s T');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
    $uri = $_SERVER['REQUEST_URI'] ?? '-';

    $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
    $exceptionStr = '';
    if ($exception) {
        $exceptionStr = "\n  Exception: " . get_class($exception) . ": " . $exception->getMessage() . 
                        "\n  File: " . $exception->getFile() . ":" . $exception->getLine() . 
                        "\n  Trace: " . $exception->getTraceAsString();
    }

    $logEntry = "[{$timestamp}] [{$level}] [{$ip}] [{$method} {$uri}] {$message}{$contextStr}{$exceptionStr}\n";

    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    // If Sentry DSN configured, capture error
    $sentryDsn = getenv('SENTRY_DSN');
    if (!empty($sentryDsn) && ($level === 'ERROR' || $level === 'CRITICAL')) {
        captureSentryException($sentryDsn, $message, $exception, $context);
    }
}

function logError(string $message, array $context = [], ?Throwable $exception = null): void {
    logMessage('ERROR', $message, $context, $exception);
}

function logInfo(string $message, array $context = []): void {
    logMessage('INFO', $message, $context);
}

/**
 * Lightweight Sentry DSN HTTP Reporter (Zero Composer dependencies)
 */
function captureSentryException(string $dsn, string $message, ?Throwable $exception = null, array $context = []): void {
    try {
        $parsed = parse_url($dsn);
        if (!$parsed || empty($parsed['host']) || empty($parsed['user'])) {
            return;
        }

        $projectId = trim($parsed['path'] ?? '', '/');
        $endpoint = "https://{$parsed['host']}/api/{$projectId}/store/";
        $key = $parsed['user'];

        $event = [
            'event_id'    => md5(uniqid((string)mt_rand(), true)),
            'timestamp'   => gmdate('Y-m-d\TH:i:s\Z'),
            'platform'    => 'php',
            'level'       => 'error',
            'server_name' => gethostname() ?: 'mgi-production',
            'environment' => getenv('APP_ENV') ?: 'production',
            'message'     => $message,
            'extra'       => $context
        ];

        if ($exception) {
            $event['exception'] = [
                'values' => [
                    [
                        'type'  => get_class($exception),
                        'value' => $exception->getMessage(),
                        'stacktrace' => [
                            'frames' => array_map(function ($f) {
                                return [
                                    'filename' => $f['file'] ?? 'unknown',
                                    'lineno'   => $f['line'] ?? 0,
                                    'function' => $f['function'] ?? 'unknown'
                                ];
                            }, array_reverse($exception->getTrace()))
                        ]
                    ]
                ]
            ];
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($event),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                "X-Sentry-Auth: Sentry sentry_version=7, sentry_key={$key}, sentry_client=mgi-php/1.0"
            ]
        ]);
        @curl_exec($ch);
        @curl_close($ch);
    } catch (Throwable $e) {
        // Prevent recursive error
    }
}
