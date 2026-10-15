<?php
require_once "config/init.php";

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

if (!function_exists('hitung_total_item')) {
    function hitung_total_item($keranjang)
    {
        return array_sum(array_column($keranjang, 'jumlah'));
    }
}
$pageTitle = "Keranjang";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "head.php"; ?>
</head>
<body>
  <?php require "navbar.php"; ?>

  <main>
    <div class="container" style="max-width: 920px;">
      <div class="page-head">
        <h1>Keranjang</h1>
      </div>

      <?php if (empty($_SESSION['keranjang'])): ?>
        <div class="glass-card empty-state">
          <i class="fas fa-shopping-cart fa-2x mb-3" style="color: var(--primary)"></i>
          <h4>Keranjang belanja kamu masih kosong.</h4>
          <p class="mb-4">Yuk pilih aksesoris favoritmu dulu.</p>
          <a href="produk.php" class="btn-custom">Mulai Belanja</a>
        </div>
      <?php else: ?>
        <div class="glass-card panel">
          <div class="panel-title">
            <i class="fas fa-shopping-basket"></i>
            <span><?= hitung_total_item($_SESSION['keranjang']); ?> item di keranjang</span>
          </div>

          <div class="table-wrap table-responsive">
            <table class="table-clean">
              <thead>
                <tr>
                  <th>Produk</th>
                  <th class="num">Harga</th>
                  <th class="ctr">Jumlah</th>
                  <th class="num">Subtotal</th>
                  <th class="ctr"></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $total = 0;
                foreach ($_SESSION['keranjang'] as $id => $item):
                    $subtotal = $item['harga'] * $item['jumlah'];
                    $total += $subtotal;
                ?>
                <tr>
                  <td><?= e($item['nama']); ?></td>
                  <td class="num">Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                  <td class="ctr">
                    <div class="qty">
                      <form method="post" action="keranjang-update.php">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="aksi" value="kurang"><input type="hidden" name="id" value="<?= (int) $id; ?>">
                        <button type="submit" aria-label="Kurangi">&minus;</button>
                      </form>
                      <strong><?= (int) $item['jumlah']; ?></strong>
                      <form method="post" action="keranjang-update.php">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="aksi" value="tambah"><input type="hidden" name="id" value="<?= (int) $id; ?>">
                        <button type="submit" aria-label="Tambah">+</button>
                      </form>
                    </div>
                  </td>
                  <td class="num">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                  <td class="ctr">
                    <form method="post" action="keranjang-update.php" onsubmit="return confirm('Hapus produk ini dari keranjang?');">
                      <?= csrf_field(); ?>
                      <input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int) $id; ?>">
                      <button type="submit" class="btn-remove" aria-label="Hapus"><i class="fas fa-trash-alt"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="num">Total Pembayaran</td>
                  <td colspan="2" class="num">Rp <?= number_format($total, 0, ',', '.'); ?></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="panel-actions">
            <a href="produk.php" class="btn-ghost"><i class="fas fa-arrow-left"></i> Tambah Produk Lain</a>
            <?php if (isset($_SESSION['user'])): ?>
              <a href="checkout.php" class="btn-custom">Lanjut Checkout <i class="fas fa-arrow-right"></i></a>
            <?php else: ?>
              <a href="login.php?next=checkout" class="btn-custom"><i class="fas fa-sign-in-alt"></i> Login untuk Checkout</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
