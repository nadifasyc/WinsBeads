<?php
require "config/init.php";

$pageTitle = "Produk";
$kategoriAktif = $_GET['kategori'] ?? '';
$keyword = trim($_GET['keyword'] ?? '');

$queryKategori = mysqli_query($con, "SELECT * FROM kategori");

if ($keyword !== '') {
    $like = "%" . $keyword . "%";
    $stmt = mysqli_prepare($con, "SELECT * FROM produk WHERE nama LIKE ?");
    mysqli_stmt_bind_param($stmt, "s", $like);
} elseif ($kategoriAktif !== '') {
    $stmt = mysqli_prepare($con, "SELECT p.* FROM produk p JOIN kategori k ON k.id = p.kategori_id WHERE k.nama = ?");
    mysqli_stmt_bind_param($stmt, "s", $kategoriAktif);
} else {
    $stmt = mysqli_prepare($con, "SELECT * FROM produk");
}
mysqli_stmt_execute($stmt);
$queryProduk = mysqli_stmt_get_result($stmt);
$countdata = mysqli_num_rows($queryProduk);

$judul = $keyword !== '' ? 'Hasil untuk "' . $keyword . '"' : ($kategoriAktif !== '' ? $kategoriAktif : 'Semua Produk');
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
        <h1><?= htmlspecialchars($judul); ?></h1>
        <p><?= $countdata; ?> produk ditemukan</p>
      </div>

      <!-- Filter kategori -->
      <nav class="filter-bar" aria-label="Filter kategori">
        <a href="produk.php" class="filter-chip<?= ($kategoriAktif === '' && $keyword === '') ? ' active' : ''; ?>">Semua</a>
        <?php while ($kategori = mysqli_fetch_array($queryKategori)) { ?>
          <a href="produk.php?kategori=<?= urlencode($kategori['nama']); ?>"
             class="filter-chip<?= $kategoriAktif === $kategori['nama'] ? ' active' : ''; ?>">
            <?= htmlspecialchars($kategori['nama']); ?>
          </a>
        <?php } ?>
      </nav>

      <!-- Katalog -->
      <?php if ($countdata < 1) { ?>
        <div class="glass-card empty-state">
          <h4>Produk tidak ditemukan.</h4>
          <p class="mb-4">Coba kata kunci lain atau lihat semua koleksi kami.</p>
          <a href="produk.php" class="btn-custom">Lihat Semua Produk</a>
        </div>
      <?php } ?>

      <div class="row g-4">
        <?php while ($produk = mysqli_fetch_array($queryProduk)) { ?>
          <div class="col-6 col-lg-4">
            <article class="glass-card product-card">
              <div class="product-image">
                <img src="assets/image/<?= htmlspecialchars($produk['foto']); ?>" alt="<?= htmlspecialchars($produk['nama']); ?>" loading="lazy">
              </div>
              <div class="product-body">
                <h3 class="product-title"><?= htmlspecialchars($produk['nama']); ?></h3>
                <p class="product-desc"><?= htmlspecialchars($produk['detail']); ?></p>
                <p class="product-price">Rp <?= number_format($produk['harga'], 0, ',', '.'); ?></p>
                <a href="produk-detail.php?nama=<?= urlencode($produk['nama']); ?>" class="btn-accent btn-block">Detail Produk</a>
              </div>
            </article>
          </div>
        <?php } ?>
      </div>
    </div>
  </main>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
