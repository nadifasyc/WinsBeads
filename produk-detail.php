<?php
    require "koneksi.php";

    $nama = htmlspecialchars($_GET['nama']);
    $queryProduk = mysqli_query($con, "SELECT * FROM produk WHERE nama='$nama'");
    $produk = mysqli_fetch_array($queryProduk);

    $queryProdukTerkait = mysqli_query($con, "SELECT * FROM produk WHERE kategori_id='$produk[kategori_id]' AND id!='$produk[id]' LIMIT 4");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - Winsbeads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: url('image/background.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        .detail-image {
            width: 100%;
            max-height: 350px;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .detail-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(31, 38, 135, 0.25);
            color: #fff;
            max-height: 350px; /* batas tinggi */
            overflow-y: auto;  /* scroll kalau isi panjang */
        }

        .detail-card h1 {
            font-family: 'Fredoka', sans-serif !important;
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 15px;
            color:rgb(170, 95, 160);
        }

        .detail-card p {
            font-size: 0.95rem;
            margin-bottom: 10px;
        }

        .text-harga {
            font-size: 1.2rem;
            font-weight: 600;
            color:rgb(170, 95, 160);
        }

        .container-produk {
            background: linear-gradient(135deg, rgba(158, 94, 137, 0.4), rgba(255, 200, 240, 0.2));
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 30 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            font-family: 'Fredoka', sans-serif !important;
        }

        .container-produk:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 50px rgba(0, 0, 0, 0.3);
        }

        .produk-terkait-title {
            color: #fff;
            font-weight: 700;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }

        .produk-terkait-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 20px;
            padding: 5px;
        }

        .produk-terkait-image:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .detail-card::-webkit-scrollbar {
            width: 6px;
        }

        .detail-card::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.4);
            border-radius: 4px;
        }
         .btn.btn-custom { 
        background-color: rgba(128, 93, 116, 0.75); 
        color: #fff; 
        transition: background-color 0.3s; 
        }

        .btn.btn-custom:hover { 
            background-color: #a97474; 
        }
</style>
    </style>
</head>
<body>
    <?php require "navbar.php"?>

    <div class="container py-5">
        <div class="row align-items-start">
            <div class="col-lg-5 mb-4">
                <img src="image/<?php echo $produk['foto']; ?>" class="detail-image" alt="">
            </div>
            <div class="col-lg-6 offset-lg-1 detail-card">
                <h1><?php echo $produk['nama']; ?></h1>
                <p><?php echo $produk['detail']; ?></p>
                <p class="text-harga">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>
                <p>Status: <strong><?php echo $produk['ketersediaan_stok'];?></strong></p>
                <form method="post" action="keranjang-update.php">
                <input type="hidden" name="produk_id" value="<?php echo $produk['id']; ?>">
                <?php if (isset($_SESSION['user'])): ?>
                    <form action="keranjang.php" method="post">
                        <input type="hidden" name="aksi" value="tambah">
                        <input type="hidden" name="id" value="<?= $produk['id'] ?>">
                        <button type="submit" class="btn btn-custom">
                            <i class="fas fa-cart-plus me-1"></i>Tambah ke Keranjang
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn btn-custom">
                        <i class="fas fa-cart-plus me-1"></i>Login untuk membeli
                    </a>
                <?php endif; ?>
                </form>

            </div>
        </div>
    </div>

    <div class="container py-5 container-produk">
        <h2 class="text-center produk-terkait-title">Produk Terkait</h2>
        <div class="row">
            <?php while($data = mysqli_fetch_array($queryProdukTerkait)) { ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="produk-detail.php?nama=<?php echo $data['nama']; ?>">
                        <img src="image/<?php echo $data['foto']; ?>" class="produk-terkait-image" alt="">
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php require "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
