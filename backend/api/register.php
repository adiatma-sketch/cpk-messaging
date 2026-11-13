<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/id_generator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Method tidak diizinkan');
}

$input    = getJsonInput();
$name     = trim((string)($input['name'] ?? ''));
$phone    = trim((string)($input['phone'] ?? ''));
$password = (string)($input['password'] ?? '');

if ($name === '' || $phone === '' || $password === '') {
    sendError(400, 'Nama, nomor HP, dan password wajib diisi');
}
if (!preg_match('/^[^0-9]+$/u', $name)) {
    sendError(400, 'Nama tidak boleh mengandung angka');
}
if (!preg_match('/^[0-9]{8,15}$/', $phone)) {
    sendError(400, 'Nomor HP hanya boleh angka (8-15 digit)');
}
if (strlen($password) < 6) {
    sendError(400, 'Password minimal 6 karakter');
}

try {
    $db = getDB();

    $cek = $db->prepare('SELECT id FROM users WHERE phone = ?');
    $cek->execute([$phone]);
    if ($cek->fetch()) {
        sendError(409, 'Nomor HP sudah terdaftar');
    }

    $id   = generateUserId();
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare(
        'INSERT INTO users (id, name, phone, password_hash) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$id, $name, $phone, $hash]);

    sendSuccess(201, 'Registrasi berhasil', [
        'id' => $id, 'name' => $name, 'phone' => $phone,
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        sendError(409, 'Nomor HP sudah terdaftar');
    }
    sendError(500, 'Terjadi kesalahan server');
}