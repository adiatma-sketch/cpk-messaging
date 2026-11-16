# Dokumentasi Keputusan Arsitektur

Aplikasi pesan sederhana (simple messaging app) dengan HTML/CSS/JS murni di frontend dan PHP 8.4 murni + MariaDB 11.4 di backend. Satu-satunya library yang dipakai adalah `firebase/php-jwt` (diizinkan di soal).

> Catatan untuk penulis: ubah kalimat di bawah ke gaya bahasamu sendiri dan sesuaikan dengan pengalamanmu yang sebenarnya.

## 1. Alur pembuatan proyek

1. **Persiapan lingkungan**: Laragon, PHP 8.4.27, MariaDB 11.4.13, Git, repo GitHub publik.
2. **Database**: tiga tabel (`users`, `contacts`, `messages`) dengan foreign key `ON DELETE CASCADE`.
3. **Backend dasar**: koneksi PDO, helper respons JSON, generator ID, register, login (JWT), hapus akun.
4. **Kontak**: CRUD kontak dengan verifikasi nomor HP terdaftar.
5. **Frontend**: wrapper `fetch` (`api.js`), halaman login/register, halaman kontak.
6. **Pesan dan foto**: endpoint pesan, halaman chat dengan polling 3 detik, upload foto profil.
7. **Dokumentasi**: dokumen ini, laporan AI, README, log harian.

Setiap endpoint atau fitur dikerjakan dan di-commit terpisah (Conventional Commits).

## 2. Struktur proyek

```
frontend/   HTML, CSS, JS (index, contacts, chat)
backend/
  api/      endpoint (register, login, contacts, messages, photo, delete_account)
  config/   database.php, jwt.php (rahasia, tidak di-commit), jwt.example.php
  helpers/  response, auth, jwt, id_generator
  lib/php-jwt/
  uploads/  foto profil
database/   schema.sql
docs/       dokumen ini, laporan AI, log harian
```

Frontend dan backend dipisah tegas: frontend hanya berkomunikasi lewat `fetch` ke `backend/api`.

## 3. Keputusan teknis dan alasannya

| Keputusan | Alternatif yang dipertimbangkan | Alasan memilih |
|---|---|---|
| **JWT** untuk autentikasi | Session PHP | Soal mengizinkan php-jwt, API stateless, mudah diuji lewat header `Authorization`. Konsekuensi: token disimpan di `localStorage`. |
| **ID pengguna acak** (`USR-` + 16 hex dari `random_bytes`) | Auto-increment, UUID | Tidak bisa ditebak dan memenuhi Kriteria 4. Panjang 20 karakter muat di `VARCHAR(32)`. Kolom `id` berstatus `PRIMARY KEY`, jadi tabrakan ditolak database. |
| **PDO prepared statement** | Query digabung string, `mysqli` | Mencegah SQL injection. `EMULATE_PREPARES` dimatikan agar memakai prepared statement asli. |
| `password_hash` / `password_verify` | Hash MD5/SHA | Algoritma khusus password, otomatis memakai salt. |
| Pesan error login **sama** untuk HP tak ada dan password salah | Pesan berbeda | Penyerang tidak bisa menebak nomor mana yang terdaftar. |
| `sender_id` diambil dari **token**, bukan dari body | Kirim `sender_id` dari klien | Mencegah pengiriman pesan atas nama orang lain. |
| Semua query kontak menyertakan `owner_id` | Cek kepemilikan terpisah | Pengguna tidak bisa membaca atau mengubah kontak milik orang lain. |
| Validasi **di server** (nama tanpa angka, HP hanya angka) | Hanya di frontend | Validasi frontend bisa dilewati. Server tetap menjadi penentu. |
| Upload foto: cek MIME dengan `finfo`, `getimagesize`, batas 2 MB, nama file acak | Cek ekstensi saja | Ekstensi bisa dipalsukan (`.txt` diganti `.jpg`). Nama acak mencegah penimpaan file dan nama berbahaya. |
| Folder `uploads` dengan `php_flag engine off` | Tanpa pengaman | Kalau ada file berbahaya lolos, PHP di folder itu tidak dijalankan. |
| Frontend memakai `textContent`, bukan `innerHTML` untuk data pengguna | `innerHTML` | Mencegah XSS lewat nama kontak atau isi pesan. |
| **Polling** tiap 3 detik dengan `after_id` | WebSocket | Cukup untuk skala tugas, dan WebSocket butuh server tambahan (di luar PHP murni sederhana). `after_id` membuat tiap polling hanya mengambil pesan baru. |
| Satu file `contacts.php` untuk GET/POST/PUT/DELETE | Satu file per aksi | Lebih ringkas, mengikuti gaya REST. |
| Daftar kontak memakai JOIN biasa | Eager loading | Optimasi lanjutan adalah Kriteria 5, yang tidak dikerjakan. |
| `jwt.php` (rahasia) di-ignore Git, hanya `jwt.example.php` di repo | Commit rahasia | Repo publik, secret tidak boleh bocor. |

### Pilihan sulit

- **Pesan validasi satu per satu atau sekaligus**: versi sekarang berhenti di kesalahan pertama (sederhana). Versi "semua sekaligus" lebih nyaman bagi pengguna tapi kodenya lebih panjang. Dipilih yang sederhana, karena soal menilai adanya validasi dan status code yang tepat.
- **ID acak vs ID berurutan**: Kriteria 4 meminta ID yang tidak mudah ditebak, sedangkan Kriteria 5 (tidak dikerjakan) membahas pengecekan ID terakhir. Karena Kriteria 5 dilewati, dipilih ID acak murni.
- **Kriteria 5 dan 6**: sengaja tidak dikerjakan agar fokus pada kualitas Kriteria 1-4 dan karena ada tugas mata kuliah lain.

## 4. Kesulitan yang dihadapi

| Masalah | Penyebab | Solusi |
|---|---|---|
| MariaDB yang diunduh berupa `.msi` | Laragon butuh folder hasil ekstrak | Dibongkar dengan `msiexec /a` lalu dipindah ke `laragon/bin/mysql` |
| Apache error `Entry Point Not Found` (nghttp2) | `extension=curl` PHP 8.4 bentrok dengan DLL Apache lama | Ekstensi curl dimatikan (tidak dipakai proyek) |
| Header `Authorization` tidak terbaca | Apache membuang header itu | `.htaccess` dengan `SetEnvIf` dan pembacaan header berlapis di `auth.php` |
| Token ditolak | Tanda `<` `>` dari contoh ikut tertulis di nilai header | Dihapus, token ditempel polos |
| Skema database dari Copilot bermasalah | Nama kolom `password hash` pakai spasi, tabel kontak dan pesan tidak ada | Ditulis ulang manual, nama kolom `password_hash`, tabel lengkap |
| File helper salah folder | File dibuat di `backend/`, bukan `backend/helpers/` | Dipindahkan sebelum di-commit |
| RapidAPI Client gagal mengirim file multipart | Isi file tidak terkirim utuh | Upload diuji dengan `curl -F` dan lewat halaman frontend |
| `photoImg` dideklarasikan dua kali | Potongan kode tertempel ganda | Dihapus satu |
| Login gagal saat dibuka lewat Live Server | Beda origin (port 5500) memicu CORS | Frontend dibuka lewat `http://localhost/...` (Apache) |
| Banyak aturan firewall `mysqld` | Pop-up firewall tiap MariaDB dijalankan | Aturan dihapus, koneksi hanya lewat `127.0.0.1` |
| Dua akun GitHub, identitas commit berbeda | Email global Git milik akun lain | `git config` lokal khusus repo ini |

## 5. Keterbatasan yang diketahui

- Token disimpan di `localStorage`, rentan jika ada celah XSS (dimitigasi dengan `textContent`).
- Belum ada pembatasan percobaan login (rate limiting).
- Polling menambah beban server dibanding WebSocket.
- Belum ada penanganan race condition saat register bersamaan (Kriteria 5), meski `PRIMARY KEY` dan `UNIQUE phone` tetap menolak duplikat.
- Foto lama dihapus saat unggah baru, tetapi foto tidak ikut terhapus saat akun dihapus.
