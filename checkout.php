<?php
require "init.php";

if (!isset($_SESSION['keranjang']) || count($_SESSION['keranjang']) === 0) {
    echo "<script>alert('Keranjang Anda masih kosong!'); window.location='produk.php';</script>";
    exit;
}

$total = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total += $item['harga'] * $item['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Checkout | Wins Beads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .btn.custom-btn {
            background-color: rgba(128, 93, 116, 0.75);
            color: #fff;
            transition: background-color 0.3s;
        }

        .btn.custom-btn:hover {
            background-color: #a97474;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.7);
        }

        thead.table-light {
            background: rgba(158, 94, 137, 0.4);
            color: #fff;
        }

        table tr:hover {
            background: rgba(199, 139, 179, 0.2);
        }

        footer {
            background-color: rgba(158, 94, 137, 0.65);
            color: #fff;
            padding: 15px 0;
            text-align: center;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <?php require "navbar.php"; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-4"><i class="fas fa-credit-card me-2"></i>Checkout</h4>

                        <form action="checkout-proses.php" method="post">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama" id="nama" class="form-control" required />
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat Pengiriman</label>
                                <textarea name="alamat" id="alamat" rows="3" class="form-control" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="telepon" class="form-label">Nomor Telepon</label>
                                <input type="text" name="telepon" id="telepon" class="form-control" required />
                            </div>

                            <h5 class="mt-5 mb-3">Ringkasan Pesanan</h5>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover text-center align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Harga Satuan</th>
                                            <th>Jumlah</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['keranjang'] as $item): 
                                            $subtotal = $item['harga'] * $item['jumlah'];
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['nama']); ?></td>
                                                <td>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                                <td><?= $item['jumlah']; ?></td>
                                                <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="fw-bold table-light">
                                            <td colspan="3" class="text-end">Total</td>
                                            <td>Rp <?= number_format($total, 0, ',', '.'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" class="btn custom-btn mt-4 w-100">
                                <i class="fas fa-check me-2"></i>Selesaikan Pesanan
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php require "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>