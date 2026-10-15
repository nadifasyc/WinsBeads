<?php
require "session.php";
require "../config/init.php";

$alert = '';
$folderGambar = __DIR__ . "/../assets/image";

function ambil_input_produk(): array
{
    $nama   = trim($_POST['nama'] ?? '');
    $detail = trim($_POST['detail'] ?? '');
    $kategori = (int) ($_POST['kategori'] ?? 0);
    $harga  = filter_var($_POST['harga'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $stok   = $_POST['ketersediaan_stok'] ?? '';

    if ($nama === '' || $kategori < 1 || $harga === false || !in_array($stok, ['tersedia', 'habis'], true)) {
        throw new Exception('Data produk belum lengkap atau tidak valid.');
    }
    return [$nama, $kategori, $harga, $detail, $stok];
}

try {
    // Tambah produk
    if (isset($_POST['simpan_produk'])) {
        [$nama, $kategori, $harga, $detail, $stok] = ambil_input_produk();
        $foto = (($_FILES['foto']['error'] ?? 4) !== UPLOAD_ERR_NO_FILE) ? simpan_gambar($_FILES['foto'], $folderGambar) : null;

        $stmt = $con->prepare("INSERT INTO produk (nama, kategori_id, harga, foto, detail, ketersediaan_stok) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siisss", $nama, $kategori, $harga, $foto, $detail, $stok);
        $stmt->execute();
        $alert = '<div class="alert alert-success">Produk berhasil ditambahkan.</div>';
    }

    // Ubah produk
    if (isset($_POST['update_produk'])) {
        $id = (int) ($_POST['id'] ?? 0);
        [$nama, $kategori, $harga, $detail, $stok] = ambil_input_produk();

        if (($_FILES['foto']['error'] ?? 4) !== UPLOAD_ERR_NO_FILE) {
            $foto = simpan_gambar($_FILES['foto'], $folderGambar);
            $stmt = $con->prepare("UPDATE produk SET nama=?, kategori_id=?, harga=?, foto=?, detail=?, ketersediaan_stok=? WHERE id=?");
            $stmt->bind_param("siisssi", $nama, $kategori, $harga, $foto, $detail, $stok, $id);
        } else {
            $stmt = $con->prepare("UPDATE produk SET nama=?, kategori_id=?, harga=?, detail=?, ketersediaan_stok=? WHERE id=?");
            $stmt->bind_param("siissi", $nama, $kategori, $harga, $detail, $stok, $id);
        }
        $stmt->execute();
        $alert = '<div class="alert alert-success">Produk berhasil diperbarui.</div>';
    }

    // Hapus produk (POST + CSRF, bukan link GET)
    if (isset($_POST['hapus_produk'])) {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $con->prepare("DELETE FROM produk WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $alert = '<div class="alert alert-success">Produk berhasil dihapus.</div>';
    }
} catch (Exception $ex) {
    $alert = '<div class="alert alert-danger">' . e($ex->getMessage()) . '</div>';
}

$queryProduk = mysqli_query($con, "SELECT p.*, k.nama AS nama_kategori FROM produk p LEFT JOIN kategori k ON k.id = p.kategori_id ORDER BY p.id DESC");
$jumlahProduk = mysqli_num_rows($queryProduk);
$queryKategori = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = "Produk Admin"; $base = "../"; require "../head.php"; ?>
</head>
<body class="admin-body">
<?php require "navbar.php" ?>

<div class="container admin-main">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <?= $alert; ?>
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3"><i class="fas fa-plus-circle me-2"></i>Tambah Produk</h4>
                    <form action="" method="post" enctype="multipart/form-data">
<?= csrf_field(); ?>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Produk</label>
                            <input type="text" id="nama" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select name="kategori" id="kategori" class="form-select" required>
                                <option value="">Pilih Satu</option>
                                <?php while($data=mysqli_fetch_array($queryKategori)){ ?>
                                    <option value="<?php echo $data['id']; ?>"><?= e($data['nama']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga</label>
                            <input type="number" class="form-control" name="harga" required>
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto</label>
                            <input type="file" name="foto" id="foto" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="detail" class="form-label">Detail</label>
                            <textarea name="detail" id="detail" cols="30" rows="5" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="ketersediaan_stok" class="form-label">Ketersediaan Stok</label>
                            <select name="ketersediaan_stok" id="ketersediaan_stok" class="form-select">
                                <option value="tersedia">Tersedia</option>
                                <option value="habis">Habis</option>
                            </select>
                        </div>
                        <button class="btn custom-btn mt-3" type="submit" name="simpan_produk">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3"><i class="fas fa-list me-2"></i>List Produk</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:5%;">No.</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th style="width:20%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($jumlahProduk == 0) {
                                    echo '<tr><td colspan="6">Tidak ada data produk</td></tr>';
                                } else {
                                    $number = 1;
                                    while ($data = mysqli_fetch_array($queryProduk)) {
                                ?>
                                        <tr>
                                            <td><?= $number++; ?></td>
                                            <td><?= e($data['nama']); ?></td>
                                            <td><?= e($data['nama_kategori'] ?? '-'); ?></td>
                                            <td>Rp <?= number_format($data['harga'], 0, ',', '.'); ?></td>
                                            <td><?= e($data['ketersediaan_stok']); ?></td>
                                            <td>
                                                <button class="btn btn-table" data-bs-toggle="modal" data-bs-target="#editModal<?= $data['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Yakin ingin hapus produk ini?')">
<?= csrf_field(); ?>
                                                    <input type="hidden" name="id" value="<?= (int) $data['id']; ?>">
                                                    <button type="submit" name="hapus_produk" class="btn btn-table" aria-label="Hapus"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Produk -->
                                        <div class="modal fade" id="editModal<?= $data['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="post" enctype="multipart/form-data">
<?= csrf_field(); ?>
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Produk</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $data['id']; ?>">
                                                            <div class="mb-3">
                                                                <label>Nama Produk</label>
                                                                <input type="text" name="nama" class="form-control" value="<?= e($data['nama']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Kategori</label>
                                                                <select name="kategori" class="form-control" required>
                                                                    <?php
                                                                    $queryKategoriModal = mysqli_query($con, "SELECT * FROM kategori");
                                                                    while ($kategori = mysqli_fetch_array($queryKategoriModal)) { ?>
                                                                        <option value="<?= $kategori['id']; ?>" <?= $kategori['id'] == $data['kategori_id'] ? 'selected' : ''; ?>>
                                                                            <?= e($kategori['nama']); ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Harga</label>
                                                                <input type="number" name="harga" class="form-control" value="<?= (int) $data['harga']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Foto (opsional)</label>
                                                                <input type="file" name="foto" class="form-control">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Detail</label>
                                                                <textarea name="detail" class="form-control"><?= e($data['detail']); ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Stok</label>
                                                                <select name="ketersediaan_stok" class="form-control">
                                                                    <option value="tersedia" <?= $data['ketersediaan_stok'] == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                                                                    <option value="habis" <?= $data['ketersediaan_stok'] == 'habis' ? 'selected' : ''; ?>>Habis</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="update_produk" class="btn btn-primary">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

                <p class="footer-copy text-center border-0">&copy; 2025 Wins Beads &middot; Admin</p>

        </div>
    </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
