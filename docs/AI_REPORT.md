# Laporan Penggunaan Generative AI

## Tools
- Claude (Anthropic): perencanaan, penjelasan langkah, kode backend dan frontend, dokumentasi
- GitHub Copilot (VS Code): saran kode, termasuk draf awal `schema.sql`

## Persentase keterlibatan AI
**Perkiraan keseluruhan: sekitar 85%.**

| Bagian | Perkiraan peran AI |
|---|---|
| Perencanaan dan pembagian langkah | 90% |
| Kode backend (PHP) | 95% |
| Kode frontend (HTML/CSS/JS) | 95% |
| Debugging dan pengujian | 50% (AI membantu mendiagnosis, pengujian dan eksekusi dilakukan sendiri) |
| Dokumentasi | 80% (draf dari AI, disunting sendiri) |

Bagian yang dikerjakan sendiri:
- Instalasi dan konfigurasi Laragon, PHP 8.4, MariaDB 11.4, dan Git
- Membuat struktur proyek, menempatkan dan menyatukan kode ke file yang tepat
- Menguji seluruh endpoint (RapidAPI, curl, browser) dan menjalankan skenario gagal
- Mencari tahu penyebab error di lingkungan sendiri (firewall, curl/nghttp2, port, dua akun GitHub)
- Memutuskan ruang lingkup (Kriteria 1-4) dan commit serta log harian

Catatan jujur: hampir seluruh baris kode ditulis oleh AI. Kontribusi pribadi ada pada penyiapan lingkungan, pengujian, debugging, integrasi, dan pengambilan keputusan.

## Contoh prompt kunci
1. "bantuan pengerjakan cpk praktikum pemrograman berbasis web"
2. "sebenarnya aku cukup selesain 4 kriteria saja" (menentukan ruang lingkup)
3. "laragon aja, mohon bantuannya ya" (setup lingkungan)
4. "kasih fullnya aja bingung aku sesudah apa sebelum apa nya" (meminta file utuh agar tidak salah sisip)
5. (tambahkan prompt lain yang menurutmu penting)

## Evaluasi kode hasil AI

| Temuan | Dampak | Perbaikan |
|---|---|---|
| Draf skema Copilot memakai nama kolom `password hash` (ada spasi) dan hanya berisi tabel `users` | Query sulit ditulis, fitur kontak dan pesan tidak punya tabel | Skema ditulis ulang: `password_hash`, tabel `contacts` dan `messages` lengkap dengan foreign key |
| Contoh header `Bearer <token>` membuat tanda `<` `>` ikut tertulis | Token ditolak (401) | Disadari bahwa itu hanya penanda, token ditempel polos |
| Kode awal membaca header `Authorization` dari satu sumber saja | Header tidak terbaca di Apache Laragon | Pembacaan berlapis (`HTTP_AUTHORIZATION`, `REDIRECT_...`, `getallheaders`) dan `.htaccess` |
| Pesan error generik "Token tidak ditemukan" untuk beberapa penyebab | Sulit mencari akar masalah | Pesan dibedakan: header tidak diterima, format salah, token tidak valid |
| Panduan awal mengarah ke PHP 8.5 dan MySQL bawaan Laragon | Tidak sesuai ketentuan soal (PHP 8.4, MariaDB) | Disesuaikan ke PHP 8.4.27 dan MariaDB 11.4 |
| Potongan kode foto profil tertempel dua kali | `contacts.js` error (`photoImg` dideklarasikan ganda) | Satu blok dihapus, dipakai file utuh |
| Pengujian upload lewat RapidAPI Client tidak bekerja | Server menerima file tidak utuh | Diuji dengan `curl -F` dan lewat halaman frontend |

### Celah umum kode AI yang diperiksa
- SQL injection: seluruh query memakai prepared statement
- Password plain text: memakai `password_hash`
- Validasi hanya di frontend: validasi dilakukan di server
- Upload hanya cek ekstensi: memakai `finfo` dan `getimagesize`, nama file diacak
- ID mudah ditebak: memakai ID acak dari `random_bytes`
- Rahasia di repo publik: `jwt.php` di-ignore Git
- XSS: data pengguna ditampilkan dengan `textContent`
- Akses data orang lain: query dibatasi `owner_id` atau pasangan pengirim/penerima dari token

### Kekurangan yang belum diperbaiki
- Belum ada rate limiting login
- Foto profil tidak ikut terhapus saat akun dihapus
- Race condition saat register bersamaan belum ditangani (Kriteria 5, tidak dikerjakan)
