<?php

define('SKIP_CSRF', true);
require "config/init.php";
require "config/midtrans.php";

$notif = json_decode(file_get_contents('php://input'), true);
if (!is_array($notif)) {
    http_response_code(400);
    exit('bad request');
}

// 1. notifikasi asli dari Midtrans
if (!midtrans_signature_valid($notif, midtrans_config()['server_key'])) {
    http_response_code(403);
    exit('invalid signature');
}

// 2. status (cek nominal, jangan menurunkan pesanan 'paid')
$hasil = midtrans_terapkan_status($con, $notif);
if ($hasil === 'nominal_beda') {
    http_response_code(400);
    exit('amount mismatch');
}

http_response_code(200);
echo $hasil === 'ok' ? 'OK' : 'order not found';
