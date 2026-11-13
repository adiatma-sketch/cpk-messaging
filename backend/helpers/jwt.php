<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../lib/php-jwt/JWTExceptionWithPayloadInterface.php';
require_once __DIR__ . '/../lib/php-jwt/BeforeValidException.php';
require_once __DIR__ . '/../lib/php-jwt/ExpiredException.php';
require_once __DIR__ . '/../lib/php-jwt/SignatureInvalidException.php';
require_once __DIR__ . '/../lib/php-jwt/Key.php';
require_once __DIR__ . '/../lib/php-jwt/JWT.php';

use Firebase\JWT\JWT;

function createToken(string $userId): string
{
    $now = time();
    $payload = [
        'iss' => 'cpk-messaging',
        'iat' => $now,
        'exp' => $now + JWT_TTL,
        'sub' => $userId,
    ];
    return JWT::encode($payload, JWT_SECRET, JWT_ALGO);
}