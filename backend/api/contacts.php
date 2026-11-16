<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

$userId = requireAuth();
$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

function validateContactInput(array $in): array
{
    $alias = trim((string)($in['alias_name'] ?? ''));
    $phone = trim((string)($in['phone'] ?? ''));

    if ($alias === '' || $phone === '') {
        sendError(400, 'Nama dan nomor HP wajib diisi');
    }
    if (!preg_match('/^[^0-9]+$/u', $alias)) {
        sendError(400, 'Nama tidak boleh mengandung angka');
    }
    if (!preg_match('/^[0-9]{8,15}$/', $phone)) {
        sendError(400, 'Nomor HP hanya boleh angka (8-15 digit)');
    }
    return [$alias, $phone];
}

function findUserByPhone(PDO $db, string $phone): array
{
    $stmt = $db->prepare('SELECT id, name, phone, photo FROM users WHERE phone = ?');
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    if (!$user) {
        sendError(404, 'Nomor HP belum terdaftar sebagai pengguna');
    }
    return $user;
}

try {
    switch ($method) {

        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $db->prepare(
                    'SELECT c.id, c.alias_name, u.id AS user_id, u.phone, u.photo
                     FROM contacts c JOIN users u ON u.id = c.contact_user_id
                     WHERE c.id = ? AND c.owner_id = ?'
                );
                $stmt->execute([(int)$_GET['id'], $userId]);
                $row = $stmt->fetch();
                if (!$row) {
                    sendError(404, 'Kontak tidak ditemukan');
                }
                sendSuccess(200, 'Kontak ditemukan', $row);
            }

            $stmt = $db->prepare(
                'SELECT c.id, c.alias_name, u.id AS user_id, u.phone, u.photo
                 FROM contacts c JOIN users u ON u.id = c.contact_user_id
                 WHERE c.owner_id = ?
                 ORDER BY c.alias_name ASC'
            );
            $stmt->execute([$userId]);
            sendSuccess(200, 'Daftar kontak', $stmt->fetchAll());

        case 'POST':
            [$alias, $phone] = validateContactInput(getJsonInput());
            $target = findUserByPhone($db, $phone);

            if ($target['id'] === $userId) {
                sendError(400, 'Tidak bisa menambahkan nomor sendiri');
            }

            $cek = $db->prepare(
                'SELECT id FROM contacts WHERE owner_id = ? AND contact_user_id = ?'
            );
            $cek->execute([$userId, $target['id']]);
            if ($cek->fetch()) {
                sendError(409, 'Kontak sudah ada');
            }

            $ins = $db->prepare(
                'INSERT INTO contacts (owner_id, contact_user_id, alias_name) VALUES (?, ?, ?)'
            );
            $ins->execute([$userId, $target['id'], $alias]);

            sendSuccess(201, 'Kontak ditambahkan', [
                'id' => (int)$db->lastInsertId(),
                'alias_name' => $alias,
                'phone' => $target['phone'],
            ]);

        case 'PUT':
            $in = getJsonInput();
            $contactId = (int)($in['id'] ?? 0);
            if ($contactId <= 0) {
                sendError(400, 'ID kontak wajib diisi');
            }
            [$alias, $phone] = validateContactInput($in);
            $target = findUserByPhone($db, $phone);

            if ($target['id'] === $userId) {
                sendError(400, 'Tidak bisa menambahkan nomor sendiri');
            }

            $own = $db->prepare('SELECT id FROM contacts WHERE id = ? AND owner_id = ?');
            $own->execute([$contactId, $userId]);
            if (!$own->fetch()) {
                sendError(404, 'Kontak tidak ditemukan');
            }

            $dup = $db->prepare(
                'SELECT id FROM contacts
                 WHERE owner_id = ? AND contact_user_id = ? AND id <> ?'
            );
            $dup->execute([$userId, $target['id'], $contactId]);
            if ($dup->fetch()) {
                sendError(409, 'Kontak dengan nomor itu sudah ada');
            }

            $upd = $db->prepare(
                'UPDATE contacts SET alias_name = ?, contact_user_id = ?
                 WHERE id = ? AND owner_id = ?'
            );
            $upd->execute([$alias, $target['id'], $contactId, $userId]);

            sendSuccess(200, 'Kontak diperbarui', [
                'id' => $contactId,
                'alias_name' => $alias,
                'phone' => $target['phone'],
            ]);

        case 'DELETE':
            $in = getJsonInput();
            $contactId = (int)($in['id'] ?? ($_GET['id'] ?? 0));
            if ($contactId <= 0) {
                sendError(400, 'ID kontak wajib diisi');
            }
            $del = $db->prepare('DELETE FROM contacts WHERE id = ? AND owner_id = ?');
            $del->execute([$contactId, $userId]);
            if ($del->rowCount() === 0) {
                sendError(404, 'Kontak tidak ditemukan');
            }
            sendSuccess(200, 'Kontak dihapus');

        default:
            sendError(405, 'Method tidak diizinkan');
    }
} catch (PDOException $e) {
    sendError(500, 'Terjadi kesalahan server');
}