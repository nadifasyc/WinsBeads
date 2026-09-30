<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['keranjang']) || count($_SESSION['keranjang']) === 0) {
    echo "<script>alert('Keranjang kosong!'); window.location='index.php';</script>";
    exit;
}

$nama = htmlspecialchars($_POST['nama']);
$alamat = htmlspecialchars($_POST['alamat']);
$telepon = htmlspecialchars($_POST['telepon']);
$tanggal = date("Y-m-d H:i:s");

// simpan ke tabel pesanan
$queryPesanan = mysqli_query($con, "INSERT INTO pesanan (nama, alamat, telepon, tanggal) VALUES ('$nama', '$alamat', '$telepon', '$tanggal')");

if (!$queryPesanan) {
    echo "<script>alert('Gagal menyimpan pesanan'); window.history.back();</script>";
    exit;
}

// ambil ID pesanan terakhir
$pesanan_id = mysqli_insert_id($con);

// simpan ke tabel pesanan_detail
foreach ($_SESSION['keranjang'] as $produk_id => $item) {
    $jumlah = $item['jumlah'];
    $harga = $item['harga'];

    mysqli_query($con, "INSERT INTO pesanan_detail (pesanan_id, produk_id, jumlah, harga) VALUES ('$pesanan_id', '$produk_id', '$jumlah', '$harga')");
}

unset($_SESSION['keranjang']);
echo "<script>alert('Pesanan berhasil disimpan!'); window.location='checkout-berhasil.php';</script>";