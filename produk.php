<?php
require "init.php";

// semua kategori
$queryKategori = mysqli_query($con, "SELECT * FROM kategori");

// produk berdasarkan keyword
if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
    $queryProduk = mysqli_query($con, "SELECT * FROM produk WHERE nama LIKE '%$keyword%'");
}

// produk berdasarkan kategori
else if (isset($_GET['kategori'])) {
    $kategoriNama = $_GET['kategori'];
    $queryKategoriId = mysqli_query($con, "SELECT id FROM kategori WHERE nama='$kategoriNama'");
    $kategoriId = mysqli_fetch_array($queryKategoriId);
    $queryProduk = mysqli_query($con, "SELECT * FROM produk WHERE kategori_id='" . $kategoriId['id'] . "'");
}

// semua produk (default)
else {
    $queryProduk = mysqli_query($con, "SELECT * FROM produk");
}

$countdata = mysqli_num_rows($queryProduk);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk | Winsbeads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>

<style>
.list-group a {
        text-decoration: none;
}
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #fbeff2, #f2e5e5);
    color: #4a2c2c;
}

.list-group-item.kategori-item {
  background-color: rgba(158, 94, 137, 0.65) !important;
  backdrop-filter: blur(6px);
  margin-bottom: 6px;
  border: none;
  border-radius: 10px;
  padding: 8px 12px;
  box-shadow: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: background-color 0.2s ease, transform 0.2s ease;
}

.list-group-item:hover, .list-group-item.active {
  background-color: #9c5b86;
  color: white;
}

.card-title {
  font-family: 'Poppins', 'Segoe UI', Tahoma, sans-serif;
  font-weight: 700;
  font-size: 1.25rem;
  color: #4a2c2c;
  margin-bottom: 8px;
}

.card-text.text-truncate {
  font-family: 'Segoe UI', Tahoma, sans-serif;
  font-size: 0.95rem;
  color: #6c5b7b;
  margin-bottom: 12px;
}

.card-text.text-harga {
  font-family: 'Segoe UI', Tahoma, sans-serif;
  font-weight: 700;
  font-size: 1.1rem;
  color: #a73e6b;
  margin-bottom: 12px;
}


.card {
    border: none;
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    font-family: 'Fredoka', sans-serif !important;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.card-img-top {
    border-radius: 20px 20px 0 0;
    height: 250px;
    object-fit: cover;
}

.card-body h4 {
    font-weight: 600;
    font-size: 1.2rem;
}

.card-text.text-harga {
    font-weight: bold;
    color: #c0392b;
}

.kategori-item {
    background-color: rgba(158, 94, 137, 0.37) !important;
    backdrop-filter: blur(8px);
    margin-bottom: 10px;
    border: none;
    border-radius: 15px;
    transition: background-color 0.3s ease, transform 0.3s ease;
          box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
}

.kategori-item:hover {
    background-color: rgba(158, 94, 137, 0.37) !important;
    transform: translateX(5px);
}

.kategori-link {
    display: block;
    padding: 12px 20px;
    color:rgb(255, 255, 255);
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}

.kategori-link:hover {
    color:rgb(255, 255, 255);
}

.kategori-item.active .kategori-link {
    color: #b85c5c;
    font-weight: 700;
}
.product-card {
  border-radius: 20px;
  overflow: hidden;
  transition: box-shadow 0.3s ease;
  background: #fff;
}

.product-card:hover {
  box-shadow: 0 12px 25px rgba(156, 91, 134, 0.35);
}

.product-image img {
  width: 100%;
  height: 250px;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.product-image img:hover {
  transform: scale(1.05);
}

.product-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.25rem;
  color: #613659;
  margin-bottom: 0.5rem;
  text-align: center;
}

.product-desc {
  font-family: 'Segoe UI', Tahoma, sans-serif;
  font-size: 0.95rem;
  color: #6c5b7b;
  margin-bottom: 1rem;
  text-align: center;
}

.product-price {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 1.1rem;
  color: #9c5b86;
  text-align: center;
}

.btn-accent {
  background-color: #9c5b86;
  color: white;
  border-radius: 30px;
  padding: 8px 22px;
  text-align: center;
  transition: background-color 0.3s ease;
  border: none;
  font-weight: 600;
  text-decoration: none;
}

.btn-accent:hover {
  background-color: #7d4264;
  color: #fff;
}
</style>
</head>

<body>
    <?php require "navbar.php"; ?>

    <div class="container py-5">
        <div class="row">

            <div class="col-lg-3 mb-5">
                <ul class="list-group list-group-flush">
                    <?php while ($kategori = mysqli_fetch_array($queryKategori)) { ?>
                        <li class="list-group-item kategori-item">
                            <a href="produk.php?kategori=<?php echo $kategori['nama']; ?>" class="kategori-link">
                                <?php echo $kategori['nama']; ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="col-lg-9">
                <div class="row g-4">
                    <?php while ($produk = mysqli_fetch_array($queryProduk)) { ?>
                        <div class="col-md-4">
                            <div class="card product-card h-100 shadow-sm">
                                <div class="product-image">
                                    <img src="image/<?php echo $produk['foto']; ?>" alt="<?php echo $produk['nama']; ?>" />
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="product-title"><?php echo $produk['nama']; ?></h5>
                                    <p class="product-desc text-truncate"><?php echo $produk['detail']; ?></p>
                                    <p class="product-price mt-auto">
                                        Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>
                                    </p>
                                    <a href="produk-detail.php?nama=<?php echo $produk['nama']; ?>" class="btn-accent mt-3">Detail Produk</a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>