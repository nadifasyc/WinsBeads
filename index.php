<?php
require "init.php";
$queryProduk = mysqli_query($con, "SELECT id, nama, harga, foto, detail FROM produk LIMIT 6");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home | Winsbeads</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Outfit:wght@100..900&display=swap" rel="stylesheet">
  

  <style>
    body {
      font-family: "Outfit", sans-serif !important;
      background: linear-gradient(135deg, #fbeff2, #f2e5e5);
      color: #4a2c2c;
    }

    .banner {
      height: 86vh;
      background: url('image/cover.jpg') center/cover no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: #fff;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.4);
    }

    .banner h1 {
      font-family: 'Fredoka', sans-serif !important;
      font-size: 3.5rem;
      font-weight: 700;
    }

    .banner p {
      font-family: 'Fredoka', sans-serif !important;
      font-size: 1.2rem;
      margin-top: 15px;
    }

    @media (max-width: 576px) {
      .banner h1 { font-size: 2.2rem; }
      .banner p { font-size: 1rem; }
    }
  .input-group {
    max-width: 600px;
    width: 100%;
    margin: 0 auto;
  }
    .btn-custom {
      background-color: #9c5b86 !important;
      color: white !important;
      border-radius: 30px;
      padding: 8px 22px !important;
      text-align: center !important;
      transition: background-color 0.3s ease !important;
      border: none !important;
      font-weight: 600 !important;
      text-decoration: none !important;
    }

    .btn-custom:hover {
      background-color: #7d4264 !important;
      color: #fff !important;
    }

    .section-title {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 30px;
      font-family: 'Fredoka', sans-serif !important;
      color: #fff;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.4);
    }

    .highlighted-kategori {
      height: 200px;
      border-radius: 20px;
      background-size: cover;
      background-position: center;
      position: relative;
      overflow: hidden;
      transition: transform 0.3s ease;
    }

    .highlighted-kategori:hover {
      transform: scale(1.05);
    }

    .highlighted-kategori h5 {
      position: absolute;
      bottom: 0;
      width: 100%;
      text-align: center;
      background: rgba(0, 0, 0, 0.4);
      color: #fff;
      padding: 10px;
      margin: 0;
      font-weight: 600;
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.4);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 6px 20px rgba(85, 21, 21, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    }

    .about-text {
      max-width: 700px;
      font-size: 1.1rem;
      line-height: 1.8;
      font-family: 'Fredoka', sans-serif;
      color: #4a2c2c;
    }

    .about-section p {
      font-size: 1.1rem;
      line-height: 1.7;
    }

    .product-card {
      border: none;
      border-radius: 20px;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.4);
      backdrop-filter: blur(12px);
      box-shadow: 0 6px 20px rgba(85, 21, 21, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    }

    .product-card img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      display: block;
      border-radius: 20px 20px 0 0;
      background-color: transparent;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-card img:hover {
      transform: scale(1.03);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .product-card.appear {
      opacity: 1;
      transform: translateY(0);
    }

    .card-text.text-truncate {
      max-height: 60px;
      overflow: hidden;
    }

    .card-text.fw-bold {
      color: #7d4264;
    }

    footer {
      background-color: #f2e5e5;
      padding: 20px 0;
      text-align: center;
      margin-top: 60px;
      color: #4a2c2c;
    }
  </style>
</head>
<body>

  <?php require "navbar.php"; ?>

  <div class="container-fluid banner d-flex align-items-center justify-content-center">
    <div class="container text-center">
      <h1>Wins Beads</h1>
      <p>Handmade with Love</p>
      <div class="col-10 col-md-8 col-lg-6 mx-auto">
        <form method="get" action="produk.php">
          <div class="input-group my-4">
            <input type="text" class="form-control" placeholder="Nama Produk" aria-label="Nama Produk" name="keyword">
            <button type="submit" class="btn-custom">Cari</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="container text-center my-5">
    <h2 class="section-title">Kategori Favorit</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="highlighted-kategori" style="background-image: url('image/fairybracelet.jpg');">
          <a href="produk.php?kategori=Fairy Bracelet" class="stretched-link"></a>
          <h5>Fairy Bracelet</h5>
        </div>
      </div>
      <div class="col-md-4">
        <div class="highlighted-kategori" style="background-image: url('image/bagcharm.jpg');">
          <a href="produk.php?kategori=Bag Charm" class="stretched-link"></a>
          <h5>Bag Charm</h5>
        </div>
      </div>
      <div class="col-md-4">
        <div class="highlighted-kategori" style="background-image: url('image/phonestrap.jpg');">
          <a href="produk.php?kategori=Phone Strap" class="stretched-link"></a>
          <h5>Phone Strap</h5>
        </div>
      </div>
    </div>
  </div>

  <div class="container my-5">
    <div class="glass-card p-5 shadow-lg text-center">
      <div class="mb-4">
        <i class="fa-solid fa-star fa-2x text-warning"></i>
      </div>
      <h2 class="section-title">About Us</h2>
      <p class="mx-auto about-text">
        Selamat datang di <strong>Wins Beads</strong>! Kami adalah rumah bagi aksesoris handmade yang dibuat dengan penuh cinta dan imajinasi. Setiap karya adalah ekspresi keunikan dan detail yang berarti. Temukan pesonanya dalam setiap manik-manik!
      </p>
      <div class="mt-4">
        <i class="fa-solid fa-sparkles fa-lg text-primary"></i>
      </div>
    </div>
  </div>

  <div class="container text-center my-5" id="produk">
    <h2 class="section-title">Produk Terbaru</h2>
    <div class="row g-4">
      <?php while($data = mysqli_fetch_array($queryProduk)) { ?>
        <div class="col-md-4">
          <div class="card glass-card product-card">
            <img src="image/<?php echo $data['foto']; ?>" class="card-img-top" alt="<?php echo $data['nama']; ?>">
            <div class="card-body">
              <h5 class="card-title"><?php echo $data['nama']; ?></h5>
              <p class="card-text text-truncate"><?php echo $data['detail']; ?></p>
              <p class="card-text fw-bold">Rp <?php echo number_format($data['harga'], 0, ',', '.'); ?></p>
              <a href="produk-detail.php?nama=<?php echo $data['nama']; ?>" class="btn-custom">
                <i class="fas fa-eye me-2"></i> Lihat Detail
              </a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
    <a class="btn btn-custom mt-3" href="produk.php">Lihat Semua Produk</a>
  </div>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    window.addEventListener('load', () => {
      document.querySelectorAll('.product-card').forEach((el, i) => {
        setTimeout(() => el.classList.add('appear'), i * 100);
      });
    });
  </script>
</body>
</html>
