<?php
require "config/init.php";
require "config/pesanan.php";
require "config/midtrans.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['user']['id'];

foreach (db_rows($con, "SELECT order_id FROM pesanan WHERE user_id = ? AND status_bayar = 'pending' AND snap_token IS NOT NULL ORDER BY id DESC LIMIT 3", "i", [$userId]) as $menunggu) {
    midtrans_sinkron_pesanan($con, $menunggu['order_id']);
}
$daftar = db_rows($con, "SELECT * FROM pesanan WHERE user_id = ? ORDER BY tanggal DESC, id DESC LIMIT 50", "i", [$userId]);
$items  = ambil_item_pesanan($con, array_column($daftar, 'id'));

$pageTitle = "Pesanan Saya";
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
        <h1>Pesanan Saya</h1>
        <p>Pantau pembayaran dan pengiriman pesananmu.</p>
      </div>

      <?php if (!$daftar): ?>
        <div class="glass-card empty-state">
          <i class="fas fa-box-open fa-2x mb-3" style="color: var(--primary)"></i>
          <h4>Belum ada pesanan.</h4>
          <p class="mb-4">Pesanan yang kamu buat akan muncul di sini.</p>
          <a href="produk.php" class="btn-custom">Mulai Belanja</a>
        </div>
      <?php endif; ?>

      <div class="order-list">
        <?php foreach ($daftar as $p):
            $id = (int) $p['id'];
            $itemP = $items[$id] ?? [];
            $perluBayar = $p['status_bayar'] === 'pending' && !empty($p['snap_token']) && !empty($p['order_id']);
        ?>
          <article class="glass-card order-card">
            <div class="order-top">
              <div>
                <strong><?= e($p['order_id'] ?: '#' . $id); ?></strong>
                <div class="text-muted small"><?= e(tgl_id($p['tanggal'])); ?></div>
              </div>
              <div class="order-pills">
                <?= pill(STATUS_BAYAR, $p['status_bayar']); ?>
                <?= pill(STATUS_PESANAN, $p['status']); ?>
              </div>
            </div>

            <div class="order-items">
              <?php foreach (array_slice($itemP, 0, 4) as $it): ?>
                <?php if (!empty($it['foto'])): ?>
                  <img src="assets/image/<?= e($it['foto']); ?>" alt="<?= e($it['nama'] ?? ''); ?>" title="<?= e($it['nama'] ?? ''); ?>" loading="lazy">
                <?php endif; ?>
              <?php endforeach; ?>
              <span class="text-muted small">
                <?= e(implode(', ', array_map(fn($i) => ($i['nama'] ?? 'Produk') . ' ×' . (int) $i['jumlah'], $itemP))); ?>
              </span>
            </div>

            <?php if ($p['status'] === 'dikirim' && $p['estimasi_tiba']): ?>
              <p class="order-eta"><i class="fas fa-truck-fast"></i> Estimasi tiba <strong><?= e(tgl_hari_id($p['estimasi_tiba'])); ?></strong></p>
            <?php endif; ?>

            <div class="order-bottom">
              <strong>Rp <?= number_format(total_pesanan($p, $itemP), 0, ',', '.'); ?></strong>
              <div class="order-actions">
                <?php if ($perluBayar): ?>
                  <a href="bayar.php?order=<?= urlencode($p['order_id']); ?>" class="btn-custom">Bayar Sekarang</a>
                <?php endif; ?>
                <?php if ($p['order_id']): ?>
                  <a href="pesanan-detail.php?order=<?= urlencode($p['order_id']); ?>" class="btn-ghost">Lihat Detail</a>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </main>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
