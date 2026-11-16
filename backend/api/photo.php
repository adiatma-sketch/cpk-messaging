<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Method tidak diizinkan');
}

$userId = requireAuth();

if (!isset($_FILES['photo'])) {
    sendError(400, 'File foto wajib dikirim (field: photo)');
}

$file = $_FILES['photo'];

if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
    sendError(413, 'Ukuran file terlalu besar (maks. 2 MB)');
}
if ($file['error'] !== UPLOAD_ERR_OK) {
    sendError(400, 'Upload gagal');
}
if ($file['size'] > 2 * 1024 * 1024) {
    sendError(413, 'Ukuran file terlalu besar (maks. 2 MB)');
}

// Cek tipe asli dari isi file, bukan dari ekstensi
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];
if (!isset($allowed[$mime])) {
    sendError(400, 'File harus berupa gambar. Terbaca: ' . $mime . ', ukuran: ' . $file['size']);
}

// Pastikan benar-benar gambar yang valid
if (@getimagesize($file['tmp_name']) === false) {
    sendError(400, 'File bukan gambar yang valid');
}

$uploadDir = __DIR__ . '/../uploads/';
$newName   = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
    sendError(500, 'Gagal menyimpan file');
}

try {
    $db  = getDB();
    $old = $db->prepare('SELECT photo FROM users WHERE id = ?');
    $old->execute([$userId]);
    $oldPhoto = $old->fetchColumn();

    $upd = $db->prepare('UPDATE users SET photo = ? WHERE id = ?');
    $upd->execute([$newName, $userId]);

    // Hapus foto lama agar folder tidak menumpuk
    if ($oldPhoto && is_file($uploadDir . $oldPhoto)) {
        unlink($uploadDir . $oldPhoto);
    }

    sendSuccess(200, 'Foto profil diperbarui', ['photo' => $newName]);
} catch (PDOException $e) {
    sendError(500, 'Terjadi kesalahan server');
}