<?php
session_start();
require_once "koneksi.php";

// inisialisasi awal
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$produk_id = $_GET['id'] ?? $_POST['produk_id'] ?? null;
$aksi = $_GET['aksi'] ?? 'tambah';

if ($produk_id) {
    $produk_id = intval($produk_id);

    // jika belum ada di keranjang, ambil datanya dari DB
    if (!isset($_SESSION['keranjang'][$produk_id])) {
        $stmt = mysqli_prepare($con, "SELECT id, nama, harga FROM produk WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $produk_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $produk = mysqli_fetch_assoc($result);

        if ($produk) {
            $_SESSION['keranjang'][$produk_id] = [
                'nama' => $produk['nama'],
                'harga' => $produk['harga'],
                'jumlah' => 0
            ];
        } else {
            header("Location: produk.php");
            exit;
        }
    }

    switch ($aksi) {
        case 'tambah':
            $_SESSION['keranjang'][$produk_id]['jumlah'] += 1;
            break;

        case 'kurang':
            $_SESSION['keranjang'][$produk_id]['jumlah'] -= 1;
            if ($_SESSION['keranjang'][$produk_id]['jumlah'] <= 0) {
                unset($_SESSION['keranjang'][$produk_id]);
            }
            break;

        case 'hapus':
            unset($_SESSION['keranjang'][$produk_id]);
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Location: keranjang.php");
} else {
    header("Location: keranjang.php");
}
exit;