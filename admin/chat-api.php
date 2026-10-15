<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require "../config/init.php";
require "../config/chat.php";

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function keluar(array $data, int $kode = 200): void
{
    http_response_code($kode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SESSION['login'] ?? false) !== true) {
    keluar(['ok' => false, 'galat' => 'Silakan login admin.'], 401);
}

function pelanggan_ada(mysqli $con, int $id): bool
{
    return $id > 0 && (bool) db_rows($con, "SELECT id FROM user WHERE id = ?", "i", [$id]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int) ($_POST['user_id'] ?? 0);
    if (!pelanggan_ada($con, $uid)) {
        keluar(['ok' => false, 'galat' => 'Pelanggan tidak ditemukan.'], 404);
    }
    $hasil = chat_kirim($con, $uid, 'admin', (string) ($_POST['isi'] ?? ''));
    keluar($hasil, $hasil['ok'] ? 200 : 422);
}

$aksi = $_GET['aksi'] ?? 'pesan';
if ($aksi === 'daftar') {
    keluar(['ok' => true, 'daftar' => chat_daftar_percakapan($con), 'belum' => chat_belum_dibaca_admin($con)]);
}

$uid = (int) ($_GET['user_id'] ?? 0);
if (!pelanggan_ada($con, $uid)) {
    keluar(['ok' => false, 'galat' => 'Pelanggan tidak ditemukan.'], 404);
}
keluar([
    'ok'     => true,
    'pesan'  => chat_ambil($con, $uid, max(0, (int) ($_GET['after'] ?? 0)), 'admin'),
    'terbaca_sampai' => chat_terbaca_sampai($con, $uid, 'admin'),
]);
