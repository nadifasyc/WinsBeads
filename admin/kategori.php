<?php
require "session.php";
require "../config/init.php";

$alert = '';

if (isset($_POST['simpan_kategori'])) {
    $kategori = trim($_POST['kategori'] ?? '');
    $cek = $con->prepare("SELECT id FROM kategori WHERE nama = ?");
    $cek->bind_param("s", $kategori);
    $cek->execute();
    $cek->store_result();
    if ($kategori === '') {
        $alert = '<div class="alert alert-warning mt-3">Nama kategori wajib diisi.</div>';
    } elseif ($cek->num_rows > 0) {
        $alert = '<div class="alert alert-warning mt-3">Kategori sudah ada.</div>';
    } else {
        $stmt = $con->prepare("INSERT INTO kategori (nama) VALUES (?)");
        $stmt->bind_param("s", $kategori);
        $alert = $stmt->execute()
            ? '<div class="alert alert-success mt-3">Kategori berhasil ditambahkan.</div>'
            : '<div class="alert alert-danger mt-3">Gagal menambahkan kategori.</div>';
    }
}

if (isset($_POST['edit_kategori'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $stmt = $con->prepare("UPDATE kategori SET nama = ? WHERE id = ?");
    $stmt->bind_param("si", $nama, $id);
    $alert = ($nama !== '' && $stmt->execute())
        ? '<div class="alert alert-success mt-3">Kategori berhasil diupdate.</div>'
        : '<div class="alert alert-danger mt-3">Gagal mengupdate kategori.</div>';
}

if (isset($_POST['hapus_kategori'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $con->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);
    $alert = $stmt->execute()
        ? '<div class="alert alert-success mt-3">Kategori berhasil dihapus.</div>'
        : '<div class="alert alert-danger mt-3">Gagal menghapus kategori.</div>';
}

$queryKategori = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama");
$jumlahKategori = mysqli_num_rows($queryKategori);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = "Kategori Admin"; $base = "../"; require "../head.php"; ?>
</head>

<body class="admin-body">
    <?php require "navbar.php" ?>

    <div class="container admin-main">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3"><i class="fas fa-plus-circle me-2"></i>Tambah Kategori</h4>
                        <form action="" method="post">
<?= csrf_field(); ?>
                            <div class="mb-3">
                                <label for="kategori" class="form-label">Nama Kategori</label>
                                <input type="text" id="kategori" name="kategori" class="form-control" required>
                            </div>
                            <button class="btn custom-btn" type="submit" name="simpan_kategori">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                        </form>
                        <?= $alert; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3"><i class="fas fa-list me-2"></i>List Kategori</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:5%;">No.</th>
                                        <th>Nama</th>
                                        <th style="width:20%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($jumlahKategori == 0) { ?>
                                        <tr><td colspan="3">Tidak ada data kategori</td></tr>
                                    <?php } else {
                                        $number = 1;
                                        while ($data = mysqli_fetch_array($queryKategori)) { ?>
                                            <tr>
                                                <td><?= $number++; ?></td>
                                                <td><?= e($data['nama']); ?></td>
                                                <td>

                                                    <button class="btn btn-table" data-bs-toggle="modal" data-bs-target="#editModal<?= $data['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <form action="" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
<?= csrf_field(); ?>
                                                        <input type="hidden" name="id" value="<?= $data['id']; ?>">
                                                        <button type="submit" name="hapus_kategori" class="btn btn-table">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                            <!-- MODAL EDIT KATEGORI -->
                                            <div class="modal fade" id="editModal<?= $data['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $data['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="" method="post">
<?= csrf_field(); ?>
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editModalLabel<?= $data['id']; ?>">Edit Kategori</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id" value="<?= $data['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nama Kategori</label>
                                                                    <input type="text" class="form-control" name="nama" value="<?= e($data['nama']); ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" name="edit_kategori" class="btn btn-primary">Simpan Perubahan</button>
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
