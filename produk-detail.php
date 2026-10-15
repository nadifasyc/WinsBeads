<?php
require "config/init.php";

// Menangkap nama produk dari URL
$namaParam = $_GET['nama'] ?? '';

if (empty($namaParam)) {
    header("Location: produk.php");
    exit;
}

// 1. Ambil data produk berdasarkan nama menggunakan Prepared Statement (Aman SQLi)
$stmt = mysqli_prepare($con, "SELECT * FROM produk WHERE nama = ?");
mysqli_stmt_bind_param($stmt, "s", $namaParam);
mysqli_stmt_execute($stmt);
$resultProduk = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_array($resultProduk, MYSQLI_ASSOC);

if (!$produk) {
    header("Location: produk.php");
    exit;
}

// 2. Ambil produk terkait (kategori sama, kecuali produk yang sedang dibuka)
$stmtTerkait = mysqli_prepare($con, "SELECT * FROM produk WHERE kategori_id = ? AND id != ? LIMIT 4");
mysqli_stmt_bind_param($stmtTerkait, "ii", $produk['kategori_id'], $produk['id']);
mysqli_stmt_execute($stmtTerkait);
$queryProdukTerkait = mysqli_stmt_get_result($stmtTerkait);
$pageTitle = $produk['nama'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "head.php"; ?>
</head>
<body>
  <?php require "navbar.php"; ?>

  <main>
    <div class="container">
      <div class="page-head">
        <a href="produk.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Kembali ke produk</a>
      </div>

      <div class="row g-4 detail-grid">
        <div class="col-lg-5">
          <img src="assets/image/<?= htmlspecialchars($produk['foto']); ?>" class="detail-image" alt="<?= htmlspecialchars($produk['nama']); ?>">
        </div>

        <div class="col-lg-7">
          <div class="glass-card detail-card">
            <span class="stock-pill"><?= htmlspecialchars($produk['ketersediaan_stok']); ?></span>
            <h1 class="mt-3"><?= htmlspecialchars($produk['nama']); ?></h1>
            <p class="detail-text"><?= nl2br(htmlspecialchars($produk['detail'])); ?></p>
            <p class="text-harga">Rp <?= number_format($produk['harga'], 0, ',', '.'); ?></p>

            <div class="detail-actions">
              <form action="keranjang-update.php" method="post">
<?= csrf_field(); ?>
                <input type="hidden" name="aksi" value="tambah">
                <input type="hidden" name="id" value="<?= $produk['id']; ?>">
                <button type="submit" class="btn-custom"><i class="fas fa-cart-plus"></i> Tambah ke Keranjang</button>
              </form>
              <a href="<?= isset($_SESSION['user']) ? 'chat.php?produk=' . (int) $produk['id'] : 'login.php?next=chat'; ?>" class="btn-ghost"><i class="fas fa-comments"></i> Tanya Admin</a>
              <a href="produk.php" class="btn-ghost">Lihat Produk Lain</a>
            </div>
          </div>
        </div>
      </div>

      <?php if (mysqli_num_rows($queryProdukTerkait) > 0): ?>
      <section class="section pb-0">
        <div class="glass-card related-card">
          <div class="section-head">
            <h2 class="section-title">Produk Terkait</h2>
          </div>
          <div class="row g-4">
            <?php while ($data = mysqli_fetch_array($queryProdukTerkait, MYSQLI_ASSOC)): ?>
              <div class="col-6 col-lg-3">
                <a class="related-link" href="produk-detail.php?nama=<?= urlencode($data['nama']); ?>" title="<?= htmlspecialchars($data['nama']); ?>">
                  <img src="assets/image/<?= htmlspecialchars($data['foto']); ?>" class="produk-terkait-image" alt="<?= htmlspecialchars($data['nama']); ?>" loading="lazy">
                </a>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      </section>
      <?php endif; ?>
    </div>
  </main>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
