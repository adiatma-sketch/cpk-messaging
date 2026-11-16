<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

$userId = requireAuth();
$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

function ensureUserExists(PDO $db, string $id): void
{
    $stmt = $db->prepare('SELECT id FROM users WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        sendError(404, 'Pengguna tidak ditemukan');
    }
}

try {
    switch ($method) {

        // Ambil percakapan dengan satu pengguna
        case 'GET':
            $with = trim((string)($_GET['with'] ?? ''));
            if ($with === '') {
                sendError(400, 'Parameter with wajib diisi');
            }
            ensureUserExists($db, $with);
            $afterId = (int)($_GET['after_id'] ?? 0);

            $stmt = $db->prepare(
                'SELECT * FROM (
                    SELECT id, sender_id, receiver_id, body, image, created_at
                    FROM messages
                    WHERE ((sender_id = ? AND receiver_id = ?)
                        OR (sender_id = ? AND receiver_id = ?))
                      AND id > ?
                    ORDER BY id DESC
                    LIMIT 200
                 ) t ORDER BY id ASC'
            );
            $stmt->execute([$userId, $with, $with, $userId, $afterId]);
            sendSuccess(200, 'Riwayat pesan', $stmt->fetchAll());

        // Kirim pesan
        case 'POST':
            $in         = getJsonInput();
            $receiverId = trim((string)($in['receiver_id'] ?? ''));
            $body       = trim((string)($in['body'] ?? ''));

            if ($receiverId === '' || $body === '') {
                sendError(400, 'Penerima dan isi pesan wajib diisi');
            }
            if (mb_strlen($body) > 2000) {
                sendError(400, 'Pesan maksimal 2000 karakter');
            }
            if ($receiverId === $userId) {
                sendError(400, 'Tidak bisa mengirim pesan ke diri sendiri');
            }
            ensureUserExists($db, $receiverId);

            $ins = $db->prepare(
                'INSERT INTO messages (sender_id, receiver_id, body) VALUES (?, ?, ?)'
            );
            $ins->execute([$userId, $receiverId, $body]);
            $newId = (int)$db->lastInsertId();

            $row = $db->prepare(
                'SELECT id, sender_id, receiver_id, body, image, created_at
                 FROM messages WHERE id = ?'
            );
            $row->execute([$newId]);
            sendSuccess(201, 'Pesan terkirim', $row->fetch());

        default:
            sendError(405, 'Method tidak diizinkan');
    }
} catch (PDOException $e) {
    sendError(500, 'Terjadi kesalahan server');
}