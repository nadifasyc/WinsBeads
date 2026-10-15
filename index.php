<?php
require "config/init.php";
$queryProduk = mysqli_query($con, "SELECT id, nama, harga, foto, detail FROM produk ORDER BY id DESC LIMIT 6");
$pageTitle = "Home";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "head.php"; ?>
</head>
<body>
  <?php require "navbar.php"; ?>

  <main>
    <!-- Hero -->
    <section class="hero">
      <div class="container">
        <div class="hero-inner">
          <div>
            <h1>Wins Beads</h1>
            <p>Handmade with Love</p>
            <form method="get" action="produk.php" class="search-bar" role="search">
              <input type="text" name="keyword" placeholder="Cari gelang, bag charm, phone strap..." aria-label="Nama Produk">
              <button type="submit" class="btn-custom">Cari</button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Kategori -->
    <section class="section">
      <div class="container">
        <div class="section-head">
          <h2 class="section-title">Kategori Favorit</h2>
        </div>
        <div class="row g-4">
          <div class="col-12 col-md-4">
            <a href="produk.php?kategori=<?= urlencode('Fairy Bracelet'); ?>" class="category-tile" style="background-image:url('assets/image/fairybracelet.jpg');">
              <span>Fairy Bracelet</span>
            </a>
          </div>
          <div class="col-12 col-md-4">
            <a href="produk.php?kategori=<?= urlencode('Bag Charm'); ?>" class="category-tile" style="background-image:url('assets/image/bagcharm.jpg');">
              <span>Bag Charm</span>
            </a>
          </div>
          <div class="col-12 col-md-4">
            <a href="produk.php?kategori=<?= urlencode('Phone Strap'); ?>" class="category-tile" style="background-image:url('assets/image/phonestrap.jpg');">
              <span>Phone Strap</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Produk terbaru -->
    <section class="section" id="produk">
      <div class="container">
        <div class="section-head">
          <h2 class="section-title">Produk Terbaru</h2>
          <p class="section-sub">Koleksi manik-manik terbaru yang baru selesai dirangkai.</p>
        </div>
        <div class="row g-4">
          <?php while ($data = mysqli_fetch_array($queryProduk)) { ?>
            <div class="col-6 col-lg-4">
              <article class="glass-card product-card">
                <div class="product-image">
                  <img src="assets/image/<?= htmlspecialchars($data['foto']); ?>" alt="<?= htmlspecialchars($data['nama']); ?>" loading="lazy">
                </div>
                <div class="product-body">
                  <h3 class="product-title"><?= htmlspecialchars($data['nama']); ?></h3>
                  <p class="product-desc"><?= htmlspecialchars($data['detail']); ?></p>
                  <p class="product-price">Rp <?= number_format($data['harga'], 0, ',', '.'); ?></p>
                  <a href="produk-detail.php?nama=<?= urlencode($data['nama']); ?>" class="btn-custom btn-block">
                    <i class="fas fa-eye"></i> Lihat Detail
                  </a>
                </div>
              </article>
            </div>
          <?php } ?>
        </div>
        <div class="text-center mt-5">
          <a class="btn-ghost" href="produk.php">Lihat Semua Produk <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    </section>

    <!-- About -->
    <section class="section">
      <div class="container">
        <div class="glass-card about-card">
          <div class="about-icon"><i class="fa-solid fa-star"></i></div>
          <h2 class="section-title">About Us</h2>
          <p class="about-text">
            Selamat datang di <strong>Wins Beads</strong>! Kami adalah rumah bagi aksesoris handmade yang dibuat dengan penuh cinta dan imajinasi. Setiap karya adalah ekspresi keunikan dan detail yang berarti. Temukan pesonanya dalam setiap manik-manik!
          </p>
        </div>
      </div>
    </section>
  </main>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
