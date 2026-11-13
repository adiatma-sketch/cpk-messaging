<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Method tidak diizinkan');
}

$input    = getJsonInput();
$phone    = trim((string)($input['phone'] ?? ''));
$password = (string)($input['password'] ?? '');

if ($phone === '' || $password === '') {
    sendError(400, 'Nomor HP dan password wajib diisi');
}

try {
    $stmt = getDB()->prepare(
        'SELECT id, name, phone, photo, password_hash FROM users WHERE phone = ?'
    );
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    // Pesan sama untuk "HP tidak ada" dan "password salah"
    if (!$user || !password_verify($password, $user['password_hash'])) {
        sendError(401, 'Nomor HP atau password salah');
    }

    sendSuccess(200, 'Login berhasil', [
        'token' => createToken($user['id']),
        'user'  => [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'phone' => $user['phone'],
            'photo' => $user['photo'],
        ],
    ]);
} catch (PDOException $e) {
    sendError(500, 'Terjadi kesalahan server');
}