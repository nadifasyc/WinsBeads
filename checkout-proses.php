<?php
require "config/init.php";
require "config/midtrans.php";
require "config/toko.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php?next=checkout");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}
if (empty($_SESSION['keranjang'])) {
    header("Location: keranjang.php");
    exit;
}

$nama    = trim($_POST['nama'] ?? '');
$alamat  = trim($_POST['alamat'] ?? '');
$telepon = trim($_POST['telepon'] ?? '');
if ($nama === '' || $alamat === '' || $telepon === '') {
    header("Location: checkout.php?err=data");
    exit;
}

$lat = $_POST['lat'] ?? null;
$lng = $_POST['lng'] ?? null;
$hitung = hitung_ongkir($lat, $lng);
if (!$hitung['ok']) {
    header("Location: checkout.php?err=" . (is_numeric($lat) && is_numeric($lng) ? 'jarak' : 'lokasi'));
    exit;
}
$ongkir = $hitung['ongkir'];
$jarak  = $hitung['jarak_km'];
$lat    = round((float) $lat, 6);
$lng    = round((float) $lng, 6);

// 1. Hitung total
$items = [];
$total = 0;
foreach ($_SESSION['keranjang'] as $produk_id => $item) {
    $harga  = (int) round($item['harga']);
    $jumlah = (int) $item['jumlah'];
    $total += $harga * $jumlah;
    $items[] = [
        'id'       => (string) $produk_id,
        'price'    => $harga,
        'quantity' => $jumlah,
        'name'     => mb_substr($item['nama'], 0, 50),
    ];
}

// Ongkir ikut sebagai item agar jumlah item_details = gross_amount
$subtotal = $total;
if ($ongkir > 0) {
    $items[] = ['id' => 'ONGKIR', 'price' => $ongkir, 'quantity' => 1, 'name' => 'Ongkos kirim (' . $jarak . ' km)'];
}
$total = $subtotal + $ongkir;

// 2. order_id
$order_id = 'WB-' . date('ymdHis') . '-' . strtoupper(bin2hex(random_bytes(2)));
$tanggal  = date('Y-m-d H:i:s');

// 3. Simpan pesanan dengan status bayar 'pending'
$stmt = mysqli_prepare($con, "INSERT INTO pesanan (user_id, order_id, nama, alamat, telepon, total, ongkir, jarak_km, lat, lng, tanggal, status_bayar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
$user_id = (int) $_SESSION['user']['id'];
mysqli_stmt_bind_param($stmt, "issssiiddds", $user_id, $order_id, $nama, $alamat, $telepon, $total, $ongkir, $jarak, $lat, $lng, $tanggal);
if (!mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Gagal menyimpan pesanan'); window.history.back();</script>";
    exit;
}
$pesanan_id = mysqli_insert_id($con);

$stmtDetail = mysqli_prepare($con, "INSERT INTO pesanan_detail (pesanan_id, produk_id, jumlah, harga) VALUES (?, ?, ?, ?)");
foreach ($_SESSION['keranjang'] as $produk_id => $item) {
    $pid = (int) $produk_id;
    $jml = (int) $item['jumlah'];
    $hrg = (int) round($item['harga']);
    mysqli_stmt_bind_param($stmtDetail, "iiii", $pesanan_id, $pid, $jml, $hrg);
    mysqli_stmt_execute($stmtDetail);
}

// 4. Minta Snap token ke Midtrans.
$host = preg_match('/^[A-Za-z0-9.\-]+(:\d+)?$/', $_SERVER['HTTP_HOST'] ?? '') ? $_SERVER['HTTP_HOST'] : 'localhost';
$skema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$folder = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$urlSelesai = $skema . '://' . $host . $folder . '/checkout-berhasil.php';
try {
    $snap = midtrans_create_snap_token([
        'callbacks'           => ['finish' => $urlSelesai],
        'transaction_details' => ['order_id' => $order_id, 'gross_amount' => $total],
        'item_details'        => $items,
        'customer_details'    => ['first_name' => mb_substr($nama, 0, 50), 'phone' => $telepon],
    ]);
} catch (Exception $e) {
    error_log('[Midtrans] ' . $e->getMessage());
    echo "<script>alert('Pembayaran belum bisa diproses. Coba lagi sebentar.'); window.location='checkout.php';</script>";
    exit;
}

$stmtTok = mysqli_prepare($con, "UPDATE pesanan SET snap_token = ? WHERE id = ?");
mysqli_stmt_bind_param($stmtTok, "si", $snap['token'], $pesanan_id);
mysqli_stmt_execute($stmtTok);

// 5. Pesanan sudah tercatat -> kosongkan keranjang, arahkan ke halaman bayar
unset($_SESSION['keranjang']);
header("Location: bayar.php?order=" . urlencode($order_id));
exit;
