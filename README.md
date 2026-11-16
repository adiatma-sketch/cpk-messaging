# CPK Messaging

Aplikasi pesan sederhana: registrasi, login, kontak, kirim-terima pesan, dan foto profil.

- **Frontend**: HTML, CSS murni, JavaScript murni (`frontend/`)
- **Backend**: PHP 8.4 murni + MariaDB 11.4 (`backend/`)
- **Library**: hanya `firebase/php-jwt` (`backend/lib/php-jwt`)

## Persyaratan
- PHP 8.4 dengan ekstensi `pdo_mysql`, `fileinfo`, `mbstring`, `openssl`
- MariaDB 10 atau lebih baru
- Apache (Laragon)

## Cara menjalankan
1. Letakkan proyek di `C:\laragon\www\cpk-messaging`, jalankan Laragon (**Start All**).
2. Import database:
   ```
   mysql -u root -e "source database/schema.sql"
   ```
3. Buat file rahasia JWT (tidak ada di repo):
   - Salin `backend/config/jwt.example.php` menjadi `backend/config/jwt.php`
   - Isi `JWT_SECRET` dengan string acak minimal 32 karakter, misalnya:
     ```
     php -r "echo bin2hex(random_bytes(32));"
     ```
4. Pastikan koneksi di `backend/config/database.php` sesuai (default: `root`, tanpa password).
5. Buka `http://localhost/cpk-messaging/frontend/index.html`.

> Buka lewat Apache (`localhost`), bukan Live Server, agar tidak terkena CORS.

## Endpoint API (`backend/api/`)
| Endpoint | Method | Fungsi |
|---|---|---|
| `register.php` | POST | Daftar akun |
| `login.php` | POST | Login, mengembalikan JWT |
| `delete_account.php` | DELETE | Hapus akun (token) |
| `contacts.php` | GET/POST/PUT/DELETE | CRUD kontak (token) |
| `messages.php` | GET/POST | Ambil percakapan (`?with=`, `after_id`) dan kirim pesan (token) |
| `photo.php` | POST (FormData `photo`) | Unggah foto profil (token) |
| `ping.php` | GET | Cek koneksi database |

## Dokumentasi
- [Keputusan arsitektur](docs/ARSITEKTUR.md)
- [Laporan penggunaan AI](docs/AI_REPORT.md)
- [Log pengerjaan harian](docs/LOG_HARIAN.md)
