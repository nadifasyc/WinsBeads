<?php
require "config/init.php";
require "config/pesanan.php";
require "config/midtrans.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$orderId = $_GET['order'] ?? '';
// Hanya pemilik pesanan yang boleh melihat
$hasil = db_rows($con, "SELECT * FROM pesanan WHERE order_id = ? AND user_id = ?", "si", [$orderId, (int) $_SESSION['user']['id']]);
if (!$hasil) {
    header("Location: pesanan-saya.php");
    exit;
}
$p = $hasil[0];

if ($p['status_bayar'] === 'pending' && !empty($p['snap_token']) && midtrans_sinkron_pesanan($con, $orderId)) {
    $p = db_rows($con, "SELECT * FROM pesanan WHERE id = ?", "i", [(int) $p['id']])[0];
}
$items = ambil_item_pesanan($con, [$p['id']])[(int) $p['id']] ?? [];

$rank = ['baru' => 0, 'diproses' => 1, 'dikirim' => 2, 'selesai' => 3][$p['status']] ?? 0;
$batal = $p['status'] === 'dibatalkan';
$bayar = $p['status_bayar'];

$langkah = [];
$langkah[] = ['judul' => 'Pesanan dibuat', 'waktu' => $p['tanggal'], 'state' => 'done', 'ket' => ''];

if ($bayar === 'paid') {
    $langkah[] = ['judul' => 'Pembayaran diterima', 'waktu' => $p['dibayar_at'], 'state' => 'done', 'ket' => $p['payment_type'] ? 'via ' . label_metode($p['payment_type']) : ''];
} elseif ($bayar === 'pending') {
    $langkah[] = ['judul' => 'Menunggu pembayaran', 'waktu' => null, 'state' => 'current', 'ket' => 'Selesaikan pembayaran agar pesanan diproses.'];
} else {
    $langkah[] = ['judul' => $bayar === 'expired' ? 'Pembayaran kedaluwarsa' : 'Pembayaran gagal', 'waktu' => null, 'state' => 'error', 'ket' => ''];
}

$sudahBayar = $bayar === 'paid' && !$batal;
$tahap = [
    ['Pesanan diproses', 1, $p['diproses_at'], 'Penjual sedang menyiapkan pesananmu.'],
    ['Pesanan dikirim',  2, $p['dikirim_at'],  ''],
    ['Pesanan selesai',  3, $p['selesai_at'],  ''],
];
$sudahAdaCurrent = false;
foreach ($tahap as [$judul, $butuhRank, $waktu, $ket]) {
    if (!$sudahBayar) {
        $state = 'todo';
    } elseif ($rank >= $butuhRank) {
        $state = 'done';
    } elseif (!$sudahAdaCurrent) {
        $state = 'current';
        $sudahAdaCurrent = true;
    } else {
        $state = 'todo';
    }
    if ($butuhRank === 2) {
        $ket = trim(($p['kurir'] ? 'Kurir ' . $p['kurir'] : '') . ($p['no_resi'] ? ' · Resi ' . $p['no_resi'] : ''), ' ·');
    }
    $langkah[] = ['judul' => $judul, 'waktu' => ($state === 'done' ? $waktu : null), 'state' => $state, 'ket' => ($state === 'todo' ? '' : $ket)];
}
if ($batal) {
    $langkah = array_slice($langkah, 0, 2);
    $langkah[] = ['judul' => 'Pesanan dibatalkan', 'waktu' => null, 'state' => 'error', 'ket' => ''];
}

$perluBayar = $bayar === 'pending' && !empty($p['snap_token']);
$pageTitle = "Detail Pesanan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "head.php"; ?>
</head>
<body>
  <?php require "navbar.php"; ?>

  <main>
    <div class="container" style="max-width: 860px;">
      <div class="page-head">
        <a href="pesanan-saya.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Semua pesanan</a>
        <h1 class="mt-3"><?= e($p['order_id']); ?></h1>
        <p><?= e(tgl_id($p['tanggal'])); ?></p>
      </div>

      <?php if (!$batal && $p['estimasi_tiba'] && in_array($p['status'], ['diproses', 'dikirim'], true)): ?>
        <div class="glass-card eta-banner">
          <i class="fas fa-truck-fast"></i>
          <div>
            <div class="small text-muted">Estimasi tiba</div>
            <strong><?= e(tgl_hari_id($p['estimasi_tiba'])); ?></strong>
          </div>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <div class="col-lg-7">
          <div class="glass-card panel">
            <div class="panel-title"><i class="fas fa-route"></i> Status Pesanan</div>
            <ol class="timeline">
              <?php foreach ($langkah as $l): ?>
                <li class="tl-<?= e($l['state']); ?>">
                  <span class="tl-dot"></span>
                  <div class="tl-body">
                    <strong><?= e($l['judul']); ?></strong>
                    <?php if ($l['waktu']): ?><div class="small text-muted"><?= e(tgl_id($l['waktu'])); ?></div><?php endif; ?>
                    <?php if ($l['ket']): ?><div class="small"><?= e($l['ket']); ?></div><?php endif; ?>
                  </div>
                </li>
              <?php endforeach; ?>
            </ol>
            <?php if ($perluBayar): ?>
              <a href="bayar.php?order=<?= urlencode($p['order_id']); ?>" class="btn-custom btn-block mt-4"><i class="fas fa-credit-card"></i> Bayar Sekarang</a>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="glass-card panel">
            <div class="panel-title"><i class="fas fa-bag-shopping"></i> Ringkasan</div>
            <?php foreach ($items as $it): ?>
              <div class="sum-line">
                <span><?= e($it['nama'] ?? 'Produk'); ?> <span class="text-muted">×<?= (int) $it['jumlah']; ?></span></span>
                <span>Rp <?= number_format((int) $it['harga'] * (int) $it['jumlah'], 0, ',', '.'); ?></span>
              </div>
            <?php endforeach; ?>
            <?php if ((int) ($p['ongkir'] ?? 0) > 0): ?>
              <div class="sum-line">
                <span>Ongkos kirim <span class="text-muted">(<?= e(number_format((float) $p['jarak_km'], 1, ',', '.')); ?> km)</span></span>
                <span>Rp <?= number_format((int) $p['ongkir'], 0, ',', '.'); ?></span>
              </div>
            <?php endif; ?>
            <div class="sum-line sum-total">
              <span>Total</span>
              <span>Rp <?= number_format(total_pesanan($p, $items), 0, ',', '.'); ?></span>
            </div>
            <hr>
            <div class="small text-muted">Dikirim ke</div>
            <div><strong><?= e($p['nama']); ?></strong> · <?= e($p['telepon']); ?></div>
            <div class="mt-1"><?= nl2br(e($p['alamat'])); ?></div>
            <?php if ($p['lat'] !== null && $p['lng'] !== null): ?>
              <a class="link-peta" target="_blank" rel="noopener" href="https://www.openstreetmap.org/?mlat=<?= e($p['lat']); ?>&amp;mlon=<?= e($p['lng']); ?>#map=16/<?= e($p['lat']); ?>/<?= e($p['lng']); ?>"><i class="fas fa-location-dot"></i> Lihat titik di peta</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
