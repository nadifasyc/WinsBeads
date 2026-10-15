<?php
require "config/init.php";
require "config/toko.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php?next=checkout");
    exit;
}

if (!isset($_SESSION['keranjang']) || count($_SESSION['keranjang']) === 0) {
    echo "<script>alert('Keranjang Anda masih kosong!'); window.location='produk.php';</script>";
    exit;
}

$total = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total += $item['harga'] * $item['jumlah'];
}

$pesanErr = [
    'data'   => 'Lengkapi nama, alamat, dan nomor telepon.',
    'lokasi' => 'Tandai lokasi pengiriman di peta terlebih dahulu.',
    'jarak'  => 'Lokasi di luar jangkauan pengiriman (maksimal ' . TOKO['maks_km'] . ' km dari toko).',
];
$err = $pesanErr[$_GET['err'] ?? ''] ?? '';

$pageTitle = "Checkout";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "head.php"; ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
</head>
<body>
  <?php require "navbar.php"; ?>

  <main>
    <div class="container" style="max-width: 760px;">
      <div class="page-head">
        <h1>Checkout</h1>
      </div>

      <form action="checkout-proses.php" method="post" class="glass-card panel" id="form-checkout">
<?= csrf_field(); ?>
        <input type="hidden" name="lat" id="lat">
        <input type="hidden" name="lng" id="lng">
        <?php if ($err): ?><div class="alert alert-danger"><?= e($err); ?></div><?php endif; ?>
        <div class="panel-title"><i class="fas fa-truck"></i> Data Pengiriman</div>

        <div class="mb-4">
          <label for="nama" class="form-label">Nama Lengkap</label>
          <input type="text" name="nama" id="nama" class="form-control" required>
        </div>
        <div class="mb-4">
          <label class="form-label">Lokasi Pengiriman</label>
          <div class="map-search">
            <input type="text" id="cari" class="form-control" placeholder="Cari alamat atau tempat, lalu tekan Enter">
            <button type="button" id="btn-cari" class="btn-ghost" aria-label="Cari"><i class="fas fa-magnifying-glass"></i></button>
            <button type="button" id="btn-lokasi" class="btn-ghost" title="Gunakan lokasi saya"><i class="fas fa-location-crosshairs"></i></button>
          </div>
          <div id="peta" class="map-box" aria-label="Peta pemilihan lokasi pengiriman"></div>
          <div id="info-lokasi" class="map-info">Klik peta atau geser pin untuk menandai lokasi pengiriman.</div>
        </div>
        <div class="mb-4">
          <label for="alamat" class="form-label">Alamat Lengkap</label>
          <textarea name="alamat" id="alamat" rows="3" class="form-control" placeholder="Nama jalan, nomor rumah, patokan" required></textarea>
        </div>
        <div class="mb-2">
          <label for="telepon" class="form-label">Nomor Telepon</label>
          <input type="text" name="telepon" id="telepon" class="form-control" required>
        </div>

        <h2 class="form-section-title">Ringkasan Pesanan</h2>
        <div class="table-wrap table-responsive">
          <table class="table-clean">
            <thead>
              <tr>
                <th>Produk</th>
                <th class="num">Harga</th>
                <th class="ctr">Jumlah</th>
                <th class="num">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($_SESSION['keranjang'] as $item):
                  $subtotal = $item['harga'] * $item['jumlah']; ?>
                <tr>
                  <td><?= e($item['nama']); ?></td>
                  <td class="num">Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                  <td class="ctr"><?= $item['jumlah']; ?></td>
                  <td class="num">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="num">Subtotal</td>
                <td class="num">Rp <?= number_format($total, 0, ',', '.'); ?></td>
              </tr>
              <tr>
                <td colspan="3" class="num">Ongkos kirim <span id="ket-jarak" class="text-muted"></span></td>
                <td class="num" id="val-ongkir">&ndash;</td>
              </tr>
              <tr>
                <td colspan="3" class="num"><strong>Total</strong></td>
                <td class="num"><strong id="val-total">Rp <?= number_format($total, 0, ',', '.'); ?></strong></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <button type="submit" id="btn-submit" class="btn-custom btn-block mt-5" disabled>
          <i class="fas fa-check"></i> Selesaikan Pesanan
        </button>
      </form>
    </div>
  </main>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
  <script>
    const TOKO = <?= json_encode(['lat' => TOKO['lat'], 'lng' => TOKO['lng'], 'nama' => TOKO['nama'],
                                  'radius_m' => (int) round(TOKO['maks_km'] / TOKO['faktor_jalan'] * 1000)]); ?>;
    const SUBTOTAL = <?= (int) $total; ?>;
  </script>
  <script src="assets/js/checkout-peta.js?v=<?= @filemtime(__DIR__ . '/assets/js/checkout-peta.js'); ?>"></script>
</body>
</html>
