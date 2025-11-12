<?php

declare(strict_types=1);

/**
 * Escape output for safe HTML rendering.
 */
function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken(): string
{
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;

    return $token;
}

function getCsrfToken(): string
{
    $token = $_SESSION['csrf_token'] ?? null;

    if (is_string($token) && $token !== '') {
        return $token;
    }

    return generateCsrfToken();
}

function isValidCsrfToken(?string $token): bool
{
    if ($token === null) {
        return false;
    }

    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!is_string($sessionToken) || $sessionToken === '') {
        return false;
    }

    return hash_equals($sessionToken, $token);
}
