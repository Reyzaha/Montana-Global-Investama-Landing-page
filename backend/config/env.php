<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — ENVIRONMENT LOADER
 * Parses .env file into getenv(), $_ENV, and $_SERVER.
 */

function loadEnv(?string $filePath = null): void {
    if ($filePath === null) {
        $filePath = dirname(__DIR__, 2) . '/.env';
    }

    if (!file_exists($filePath) || !is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Parse KEY=VALUE
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $val = trim($parts[1]);

        // Remove wrapping quotes
        if (
            (str_starts_with($val, '"') && str_ends_with($val, '"')) ||
            (str_starts_with($val, "'") && str_ends_with($val, "'"))
        ) {
            $val = substr($val, 1, -1);
        }

        // Only set if not already set by system environment
        if (getenv($key) === false) {
            putenv("{$key}={$val}");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}

// Automatically load on include
loadEnv();
