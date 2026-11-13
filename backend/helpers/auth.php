<?php
declare(strict_types=1);

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/jwt.php';

function getAuthorizationHeader(): string
{
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return (string)$_SERVER['HTTP_AUTHORIZATION'];
    }
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return (string)$_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                return (string)$value;
            }
        }
    }
    return '';
}

function requireAuth(): string
{
    $header = trim(getAuthorizationHeader());

    if ($header === '') {
        sendError(401, 'Header Authorization tidak diterima server');
    }
    if (!preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
        sendError(401, 'Format harus: Bearer <token>');
    }

    $userId = verifyToken($m[1]);
    if ($userId === null) {
        sendError(401, 'Token tidak valid atau kedaluwarsa');
    }

    return $userId;
}