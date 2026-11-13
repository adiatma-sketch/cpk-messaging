<?php
declare(strict_types=1);

// Salin file ini jadi jwt.php lalu isi JWT_SECRET (minimal 32 karakter acak)
const JWT_SECRET = 'GANTI_DENGAN_SECRET_ACAK_MINIMAL_32_KARAKTER';
const JWT_ALGO   = 'HS256';
const JWT_TTL    = 86400;