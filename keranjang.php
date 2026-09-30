<?php
require_once "init.php";

if (!isset($_SESSION['user'])) {
    header("Location: user/login.php?pesan=login-dulu");
    exit;
}

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

function hitung_total_item($keranjang)
{
    return array_sum(array_column($keranjang, 'jumlah'));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Winsbeads | Keranjang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fdfdfd;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            background: rgba(255,255,255,0.95);
        }

        thead.table-light {
            background: rgba(158,94,137,0.4);
            color: #fff;
        }

        table tr:hover {
            background: rgba(199,139,179,0.15);
        }

        .btn.custom-btn {
            background-color: rgba(128, 93, 116, 0.75);
            color: #fff;
            transition: background-color 0.3s;
        }

        .btn.custom-btn:hover {
            background-color: #a97474;
        }
    </style>
</head>
<body>
<?php include "navbar.php"; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if (empty($_SESSION['keranjang'])): ?>
                <div class="alert alert-warning text-center shadow-sm">
                    <i class="fas fa-shopping-cart fa-lg me-2"></i>
                    Keranjang masih kosong.
                    <a href="produk.php" class="alert-link">Belanja sekarang</a>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-4"><i class="fas fa-shopping-basket me-2"></i>Keranjang (<?= hitung_total_item($_SESSION['keranjang']); ?> item)</h4>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th width="120">Harga</th>
                                        <th width="140">Jumlah</th>
                                        <th width="140">Subtotal</th>
                                        <th width="80">Aksi</th>
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
                                        <td><?= htmlspecialchars($item['nama']); ?></td>
                                        <td>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                        <td>
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <a href="keranjang-update.php?aksi=kurang&id=<?= $id; ?>" class="btn btn-sm btn-outline-secondary">-</a>
                                                <?= $item['jumlah']; ?>
                                                <a href="keranjang-update.php?aksi=tambah&id=<?= $id; ?>" class="btn btn-sm btn-outline-secondary">+</a>
                                            </div>
                                        </td>
                                        <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                        <td>
                                            <a href="keranjang-update.php?aksi=hapus&id=<?= $id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus produk ini dari keranjang?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="fw-bold table-light">
                                        <td colspan="3" class="text-end">Total</td>
                                        <td colspan="2">Rp <?= number_format($total, 0, ',', '.'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="produk.php" class="btn btn-secondary"><i class="fas fa-plus me-1"></i>Tambah Produk</a>
                            <a href="checkout.php" class="btn custom-btn"><i class="fas fa-arrow-right me-1"></i>Lanjut Checkout</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>