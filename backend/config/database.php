<?php
declare(strict_types=1);

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = '127.0.0.1';
        $db   = 'cpk_messaging';
        $user = 'root';
        $pass = '';

        $pdo = new PDO(
            "mysql:host=$host;dbname=$db;charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }

    return $pdo;
}