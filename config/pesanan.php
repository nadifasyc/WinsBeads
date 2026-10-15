<?php

const STATUS_PESANAN = [
    'baru'       => ['Baru',       'status-warn'],
    'diproses'   => ['Diproses',   'status-info'],
    'dikirim'    => ['Dikirim',    'status-info'],
    'selesai'    => ['Selesai',    'status-ok'],
    'dibatalkan' => ['Dibatalkan', 'status-bad'],
];

/** Status pembayaran (Midtrans) */
const STATUS_BAYAR = [
    'pending' => ['Menunggu Pembayaran', 'status-warn'],
    'paid'    => ['Lunas',               'status-ok'],
    'failed'  => ['Gagal',               'status-bad'],
    'expired' => ['Kedaluwarsa',         'status-muted'],
];

/** Daftar kurir yang bisa dipilih admin */
const KURIR = ['JNE', 'J&T', 'SiCepat', 'Pos Indonesia', 'AnterAja', 'GoSend', 'GrabExpress', 'Lainnya'];

function label_metode(?string $tipe): string
{
    $peta = [
        'bank_transfer' => 'Transfer Bank', 'echannel' => 'Mandiri Bill', 'qris' => 'QRIS',
        'gopay' => 'GoPay', 'shopeepay' => 'ShopeePay', 'credit_card' => 'Kartu Kredit',
        'cstore' => 'Minimarket', 'akulaku' => 'Akulaku', 'kredivo' => 'Kredivo',
    ];
    return $tipe ? ($peta[$tipe] ?? ucwords(str_replace('_', ' ', $tipe))) : '';
}

function pill(array $daftar, ?string $kunci): string
{
    [$label, $kelas] = $daftar[$kunci] ?? [$kunci ?: '-', 'status-muted'];
    return '<span class="status-pill ' . $kelas . '">' . e($label) . '</span>';
}

function tgl_id(?string $waktu, bool $denganJam = true): string
{
    if (!$waktu) {
        return '-';
    }
    $t = strtotime($waktu);
    if ($t === false) {
        return '-';
    }
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $teks  = date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
    return $denganJam ? $teks . ', ' . date('H:i', $t) : $teks;
}

function tgl_hari_id(?string $tanggal): string
{
    if (!$tanggal) {
        return '-';
    }
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return $hari[(int) date('w', strtotime($tanggal))] . ', ' . tgl_id($tanggal, false);
}

function db_rows(mysqli $con, string $sql, string $tipe = '', array $param = []): array
{
    $stmt = $con->prepare($sql);
    if ($tipe !== '') {
        $stmt->bind_param($tipe, ...$param);
    }
    $stmt->execute();
    $hasil = $stmt->get_result();
    $baris = [];
    while ($r = $hasil->fetch_assoc()) {
        $baris[] = $r;
    }
    $stmt->close();
    return $baris;
}

function ambil_item_pesanan(mysqli $con, array $idPesanan): array
{
    $idPesanan = array_values(array_unique(array_map('intval', $idPesanan)));
    if (!$idPesanan) {
        return [];
    }
    $daftar = implode(',', $idPesanan);
    $rows = db_rows($con, "SELECT d.pesanan_id, d.jumlah, d.harga, pr.nama, pr.foto
                           FROM pesanan_detail d LEFT JOIN produk pr ON pr.id = d.produk_id
                           WHERE d.pesanan_id IN ($daftar) ORDER BY d.id");
    $hasil = [];
    foreach ($rows as $r) {
        $hasil[(int) $r['pesanan_id']][] = $r;
    }
    return $hasil;
}

function total_pesanan(array $pesanan, array $items): int
{
    if ((int) $pesanan['total'] > 0) {
        return (int) $pesanan['total'];
    }
    $sum = 0;
    foreach ($items as $i) {
        $sum += (int) $i['harga'] * (int) $i['jumlah'];
    }
    return $sum;
}
