<?php
// File sementara untuk membersihkan OPcache di server hosting
// HAPUS file ini setelah digunakan!

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache berhasil dibersihkan. Silakan hapus file ini sekarang.';
} else {
    echo 'OPcache tidak aktif di server ini. Tidak perlu dibersihkan.';
}
