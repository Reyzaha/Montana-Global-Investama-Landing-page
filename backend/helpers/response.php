<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API RESPONSE HELPER
 * Standardized JSON response formatting, HTTP security headers, and exception masking
 */

function sendJsonResponse(mixed $data = null, int $statusCode = 200, string $message = '', bool $success = true): void {
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        // HTTP Security Headers
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Controlled CORS Policy (Restrictive, with credentials support)
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (!empty($origin)) {
            $originHost = parse_url($origin, PHP_URL_HOST);
            if ($originHost === $host || in_array($originHost, ['localhost', '127.0.0.1'])) {
                header("Access-Control-Allow-Origin: {$origin}");
                header('Access-Control-Allow-Credentials: true');
            }
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        exit(0);
    }

    $response = [
        'success' => $success,
        'status' => $statusCode,
        'message' => $message,
        'timestamp' => date('c'),
        'data' => $data
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function sendJsonError(string $message, int $statusCode = 400, mixed $errors = null): void {
    sendJsonResponse($errors, $statusCode, $message, false);
}

function sendJsonException(Throwable $e, string $publicMessage = 'Terjadi kesalahan sistem internal. Silakan coba beberapa saat lagi.'): void {
    // Log detailed technical error message internally
    error_log(sprintf(
        "[MGI Server Exception] %s in %s:%d\nStack Trace:\n%s",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));

    // Mask message in production, only show details if explicitly in debug mode
    $isDebug = getenv('APP_DEBUG') === 'true' || getenv('APP_ENV') === 'development';
    $message = $isDebug ? ('[Debug] ' . $e->getMessage()) : $publicMessage;

    sendJsonError($message, 500);
}

function getJsonInput(): array {
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return $_POST;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? array_merge($_POST, $decoded) : $_POST;
}

