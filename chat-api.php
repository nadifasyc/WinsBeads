<?php

require "config/init.php";
require "config/chat.php";

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function keluar(array $data, int $kode = 200): void
{
    http_response_code($kode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['user']['id'])) {
    keluar(['ok' => false, 'galat' => 'Silakan login.'], 401);
}
$uid = (int) $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produkId = isset($_POST['produk_id']) && ctype_digit((string) $_POST['produk_id']) ? (int) $_POST['produk_id'] : null;
    $hasil = chat_kirim($con, $uid, 'user', (string) ($_POST['isi'] ?? ''), $produkId);
    keluar($hasil, $hasil['ok'] ? 200 : 422);
}

$setelah = max(0, (int) ($_GET['after'] ?? 0));
keluar([
    'ok'     => true,
    'pesan'  => chat_ambil($con, $uid, $setelah, 'user'),
    'terbaca_sampai' => chat_terbaca_sampai($con, $uid, 'user'),
]);
