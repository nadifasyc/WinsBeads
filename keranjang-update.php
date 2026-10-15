<?php
require_once "config/init.php";

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: keranjang.php");
    exit;
}
$produk_id = $_POST['id'] ?? null;
$aksi = $_POST['aksi'] ?? 'tambah';
if (!in_array($aksi, ['tambah', 'kurang', 'hapus'], true)) {
    $aksi = 'tambah';
}

if ($produk_id) {
    $produk_id = intval($produk_id);

    // 1. Jika produk belum ada di keranjang, ambil dari database
    if (!isset($_SESSION['keranjang'][$produk_id])) {
        $stmt = mysqli_prepare($con, "SELECT id, nama, harga FROM produk WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $produk_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $produk = mysqli_fetch_assoc($result);

        if ($produk) {
            $_SESSION['keranjang'][$produk_id] = [
                'nama'   => $produk['nama'],
                'harga'  => $produk['harga'],
                'jumlah' => 0
            ];
        } else {
            // Jika ID produk tidak valid di database
            header("Location: produk.php");
            exit;
        }
    }

    // 2. Jalankan aksi sesuai permintaan (tambah/kurang/hapus)
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

header("Location: keranjang.php");
exit;