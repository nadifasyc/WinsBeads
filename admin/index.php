<?php
    require "session.php";
    require "../config/init.php";

    $queryKategori = mysqli_query($con, "SELECT * FROM kategori");
    $jumlahKategori = mysqli_num_rows($queryKategori);

    $queryProduk = mysqli_query($con, "SELECT * FROM produk");
    $jumlahProduk = mysqli_num_rows($queryProduk);

    $queryPesanan = mysqli_query($con, "SELECT * FROM pesanan");
    $jumlahPesanan = mysqli_num_rows($queryPesanan);
    $pageTitle = "Dashboard";
    $base = "../";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "../head.php"; ?>
</head>
<body class="admin-body">
  <?php require "navbar.php"; ?>

  <main class="admin-main">
    <div class="container" style="max-width: 960px;">
      <div class="page-head px-0">
        <h1>Dashboard</h1>
        <p>Ringkasan toko Wins Beads.</p>
      </div>

      <div class="row g-4">
        <div class="col-12 col-md-4">
          <a href="kategori.php" class="glass-card card-summary">
            <div class="icon"><i class="fas fa-th-large"></i></div>
            <h3>Kategori</h3>
            <p class="number"><?= $jumlahKategori; ?></p>
            <span class="link-detail">Lihat Detail &rarr;</span>
          </a>
        </div>
        <div class="col-12 col-md-4">
          <a href="produk.php" class="glass-card card-summary">
            <div class="icon"><i class="fas fa-box"></i></div>
            <h3>Produk</h3>
            <p class="number"><?= $jumlahProduk; ?></p>
            <span class="link-detail">Lihat Detail &rarr;</span>
          </a>
        </div>
        <div class="col-12 col-md-4">
          <a href="pesanan.php" class="glass-card card-summary">
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <h3>Pesanan</h3>
            <p class="number"><?= $jumlahPesanan; ?></p>
            <span class="link-detail">Lihat Detail &rarr;</span>
          </a>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
