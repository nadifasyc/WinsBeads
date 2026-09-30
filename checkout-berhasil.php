<?php
require "init.php";

unset($_SESSION['keranjang']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Thank You | Wins Beads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            background: url('image/background.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }

        .trims-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .trims-box {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            color: #4a2e3f;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
        }

        .trims-box h1 {
            font-family: 'Fredoka', sans-serif;
            color: #9e5e89;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .trims-box p {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            color: #3c2a35;
        }

        .btn.custom-btn {
            background-color: rgba(128, 93, 116, 0.85);
            color: #fff;
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            margin-top: 25px;
            transition: background-color 0.3s ease;
            border-radius: 8px;
        }

        .btn.custom-btn:hover {
            background-color: #a97474;
        }
    </style>
</head>
<body>

    <?php require "navbar.php"; ?>

    <div class="container trims-container">
        <div class="trims-box">
            <h1><i class="fas fa-heart me-2"></i>Terima Kasih!</h1>
            <p>Pesananmu telah berhasil diproses.</p>
            <p>Kami akan segera menghubungi untuk konfirmasi dan pengiriman.</p>
            <a href="index.php" class="btn custom-btn"><i class="fas fa-home me-1"></i>Kembali ke Beranda</a>
        </div>
    </div>

    <?php require "footer.php"; ?>
</body>