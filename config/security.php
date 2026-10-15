<?php

function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify(): void
{
    $kirim    = $_POST['csrf_token'] ?? '';
    $diharap  = $_SESSION['csrf_token'] ?? '';
    if ($diharap === '' || !is_string($kirim) || $kirim === '' || !hash_equals($diharap, $kirim)) {
        http_response_code(419);
        exit('Sesi formulir tidak valid atau sudah kedaluwarsa. Silakan kembali dan muat ulang halaman.');
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !defined('SKIP_CSRF')) {
    csrf_verify();
}

function simpan_gambar(array $file, string $folderTujuan, int $maksByte = 2097152): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new Exception('Upload gagal. Coba lagi.');
    }
    if ($file['size'] > $maksByte) {
        throw new Exception('Ukuran foto maksimal ' . ($maksByte / 1048576) . ' MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $izin  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($izin[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new Exception('File harus berupa gambar JPG, PNG, WEBP, atau GIF.');
    }

    $nama = bin2hex(random_bytes(8)) . '.' . $izin[$mime];
    if (!move_uploaded_file($file['tmp_name'], rtrim($folderTujuan, '/') . '/' . $nama)) {
        throw new Exception('Gagal menyimpan foto di server.');
    }
    return $nama;
}
