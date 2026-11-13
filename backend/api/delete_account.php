<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendError(405, 'Method tidak diizinkan');
}

$userId = requireAuth();

try {
    $stmt = getDB()->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$userId]);

    if ($stmt->rowCount() === 0) {
        sendError(404, 'Akun tidak ditemukan');
    }

    sendSuccess(200, 'Akun berhasil dihapus');
} catch (PDOException $e) {
    sendError(500, 'Terjadi kesalahan server');
}