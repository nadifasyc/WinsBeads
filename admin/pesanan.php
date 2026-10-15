<?php
require "session.php";
require "../config/init.php";
require "../config/pesanan.php";

$alert = '';

/* ---------- 1. Ubah status & info pengiriman ---------- */
if (isset($_POST['kelola_pesanan'])) {
    try {
        $id     = (int) ($_POST['pesanan_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $kurir  = trim($_POST['kurir'] ?? '');
        $resi   = trim($_POST['no_resi'] ?? '');
        $est    = trim($_POST['estimasi_tiba'] ?? '');

        if (!isset(STATUS_PESANAN[$status])) {
            throw new Exception('Status tidak valid.');
        }
        $ada = db_rows($con, "SELECT status_bayar FROM pesanan WHERE id = ?", "i", [$id]);
        if (!$ada) {
            throw new Exception('Pesanan tidak ditemukan.');
        }
        // Aturan bisnis: pesanan yang belum dibayar tidak boleh diproses/dikirim/diselesaikan
        if (in_array($status, ['diproses', 'dikirim', 'selesai'], true) && $ada[0]['status_bayar'] !== 'paid') {
            throw new Exception('Pesanan ini belum dibayar, jadi belum bisa diproses atau dikirim.');
        }
        if ($kurir !== '' && !in_array($kurir, KURIR, true)) {
            throw new Exception('Kurir tidak valid.');
        }
        $estDb = null;
        if ($est !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $est);
            if (!$d || $d->format('Y-m-d') !== $est) {
                throw new Exception('Format estimasi tiba tidak valid.');
            }
            $estDb = $est;
        }
        if ($status === 'dikirim' && ($kurir === '' || $estDb === null)) {
            throw new Exception('Untuk status Dikirim, isi kurir dan estimasi tiba.');
        }
        $resiDb  = $resi === '' ? null : mb_substr($resi, 0, 50);
        $kurirDb = $kurir === '' ? null : $kurir;

        // Catat waktu pertama kali pesanan mencapai setiap tahap (COALESCE = jangan timpa yang sudah ada)
        $now      = date('Y-m-d H:i:s');
        $tProses  = in_array($status, ['diproses', 'dikirim', 'selesai'], true) ? $now : null;
        $tKirim   = in_array($status, ['dikirim', 'selesai'], true) ? $now : null;
        $tSelesai = $status === 'selesai' ? $now : null;

        $stmt = $con->prepare("UPDATE pesanan SET status = ?, kurir = ?, no_resi = ?, estimasi_tiba = ?,
                               diproses_at = COALESCE(diproses_at, ?), dikirim_at = COALESCE(dikirim_at, ?), selesai_at = COALESCE(selesai_at, ?)
                               WHERE id = ?");
        $stmt->bind_param("sssssssi", $status, $kurirDb, $resiDb, $estDb, $tProses, $tKirim, $tSelesai, $id);
        $stmt->execute();
        $alert = '<div class="alert alert-success">Pesanan #' . $id . ' diperbarui.</div>';
    } catch (Exception $ex) {
        $alert = '<div class="alert alert-danger">' . e($ex->getMessage()) . '</div>';
    }
}

/* ---------- 2. Filter, sorting, pencarian, halaman ---------- */
$fStatus = $_GET['status'] ?? 'semua';
$fBayar  = $_GET['bayar'] ?? '';
$urut    = $_GET['urut'] ?? 'terbaru';
$q       = trim($_GET['q'] ?? '');
$hal     = max(1, (int) ($_GET['hal'] ?? 1));
$perHal  = 10;

$where = [];
$tipe  = '';
$param = [];
if (isset(STATUS_PESANAN[$fStatus])) {
    $where[] = 'p.status = ?';  $tipe .= 's';  $param[] = $fStatus;
} else {
    $fStatus = 'semua';
}
if (isset(STATUS_BAYAR[$fBayar])) {
    $where[] = 'p.status_bayar = ?';  $tipe .= 's';  $param[] = $fBayar;
} else {
    $fBayar = '';
}
if ($q !== '') {
    $like = '%' . $q . '%';
    $where[] = '(p.nama LIKE ? OR p.order_id LIKE ? OR u.username LIKE ?)';
    $tipe .= 'sss';
    array_push($param, $like, $like, $like);
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$urutan = [
    'terbaru' => 'p.tanggal DESC, p.id DESC',
    'terlama' => 'p.tanggal ASC, p.id ASC',

    'status'  => "CASE p.status WHEN 'baru' THEN 1 WHEN 'diproses' THEN 2 WHEN 'dikirim' THEN 3 WHEN 'selesai' THEN 4 ELSE 5 END, p.tanggal DESC",
];
if (!isset($urutan[$urut])) {
    $urut = 'terbaru';
}

$dasar = "FROM pesanan p LEFT JOIN user u ON u.id = p.user_id $whereSql";
$jumlah = (int) db_rows($con, "SELECT COUNT(*) AS n $dasar", $tipe, $param)[0]['n'];
$totalHal = max(1, (int) ceil($jumlah / $perHal));
$hal = min($hal, $totalHal);
$offset = ($hal - 1) * $perHal;

$daftarPesanan = db_rows(
    $con,
    "SELECT p.*, u.username $dasar ORDER BY {$urutan[$urut]} LIMIT ? OFFSET ?",
    $tipe . 'ii',
    array_merge($param, [$perHal, $offset])
);
$items = ambil_item_pesanan($con, array_column($daftarPesanan, 'id'));

// Jumlah per status untuk chip filter
$hitung = ['semua' => 0];
foreach (db_rows($con, "SELECT status, COUNT(*) AS n FROM pesanan GROUP BY status") as $r) {
    $hitung[$r['status']] = (int) $r['n'];
    $hitung['semua'] += (int) $r['n'];
}

function url_pesanan(array $ganti = []): string
{
    $q = array_merge($_GET, $ganti);
    $q = array_filter($q, fn($v) => $v !== '' && $v !== null && $v !== 'semua' && $v !== 'terbaru');
    return 'pesanan.php' . ($q ? '?' . http_build_query($q) : '');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php $pageTitle = "Pesanan Admin"; $base = "../"; require "../head.php"; ?>
</head>
<body class="admin-body">
<?php require "navbar.php"; ?>

<div class="container admin-main">
    <div class="page-head px-0">
        <h1>Pesanan</h1>
        <p><?= $jumlah; ?> pesanan<?= ($where ? ' sesuai filter' : ''); ?></p>
    </div>

    <?= $alert; ?>

    <!-- Filter status -->
    <nav class="filter-bar" aria-label="Filter status pesanan">
        <?php
        $chip = ['semua' => 'Semua'] + array_map(fn($v) => $v[0], STATUS_PESANAN);
        foreach ($chip as $kunci => $label): ?>
            <a href="<?= e(url_pesanan(['status' => $kunci, 'hal' => 1])); ?>"
               class="filter-chip<?= $fStatus === $kunci ? ' active' : ''; ?>">
                <?= e($label); ?> <span class="chip-count"><?= $hitung[$kunci] ?? 0; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Pencarian, filter bayar, urutan -->
    <form method="get" class="glass-card toolbar">
        <input type="hidden" name="status" value="<?= e($fStatus); ?>">
        <input type="search" name="q" value="<?= e($q); ?>" class="form-control" placeholder="Cari nama, username, atau nomor pesanan">
        <select name="bayar" class="form-select" aria-label="Status pembayaran">
            <option value="">Semua pembayaran</option>
            <?php foreach (STATUS_BAYAR as $k => $v): ?>
                <option value="<?= e($k); ?>" <?= $fBayar === $k ? 'selected' : ''; ?>><?= e($v[0]); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="urut" class="form-select" aria-label="Urutkan">
            <option value="terbaru" <?= $urut === 'terbaru' ? 'selected' : ''; ?>>Tanggal: terbaru</option>
            <option value="terlama" <?= $urut === 'terlama' ? 'selected' : ''; ?>>Tanggal: terlama</option>
            <option value="status"  <?= $urut === 'status'  ? 'selected' : ''; ?>>Urut status (baru lebih dulu)</option>
        </select>
        <button type="submit" class="btn-custom">Terapkan</button>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Pesanan</th>
                            <th>Pemesan</th>
                            <th>Alamat</th>
                            <th>Item</th>
                            <th class="text-end">Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$daftarPesanan): ?>
                        <tr><td colspan="8" class="text-center py-5">Tidak ada pesanan yang cocok.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($daftarPesanan as $p):
                        $id = (int) $p['id'];
                        $itemP = $items[$id] ?? [];
                    ?>
                        <tr>
                            <td>
                                <strong><?= e($p['order_id'] ?: '#' . $id); ?></strong><br>
                                <small class="text-muted"><?= e(tgl_id($p['tanggal'])); ?></small>
                            </td>
                            <td>
                                <?= e($p['nama']); ?>
                                <?php if ($p['username']): ?>
                                    <br><small class="user-tag"><i class="fas fa-user"></i> @<?= e($p['username']); ?></small>
                                <?php else: ?>
                                    <br><small class="text-muted">tamu / akun dihapus</small>
                                <?php endif; ?>
                                <br><small class="text-muted"><?= e($p['telepon']); ?></small>
                            </td>
                            <td class="cell-alamat"><?= e($p['alamat']); ?>
                                <?php if (isset($p['lat']) && $p['lat'] !== null && $p['lng'] !== null): ?>
                                    <div class="small"><a target="_blank" rel="noopener" href="https://www.openstreetmap.org/?mlat=<?= e($p['lat']); ?>&amp;mlon=<?= e($p['lng']); ?>#map=16/<?= e($p['lat']); ?>/<?= e($p['lng']); ?>"><i class="fas fa-location-dot"></i> Peta</a> · <?= e(number_format((float) $p['jarak_km'], 1, ',', '.')); ?> km · ongkir Rp <?= number_format((int) $p['ongkir'], 0, ',', '.'); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php foreach ($itemP as $it): ?>
                                    <div class="item-line">
                                        <?php if (!empty($it['foto'])): ?>
                                            <img src="../assets/image/<?= e($it['foto']); ?>" class="foto-produk" alt="">
                                        <?php endif; ?>
                                        <span><?= e($it['nama'] ?? '(produk dihapus)'); ?> <span class="text-muted">&times;<?= (int) $it['jumlah']; ?></span></span>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td class="text-end text-nowrap"><strong>Rp <?= number_format(total_pesanan($p, $itemP), 0, ',', '.'); ?></strong></td>
                            <td>
                                <?= pill(STATUS_BAYAR, $p['status_bayar']); ?>
                                <?php if ($p['payment_type']): ?><br><small class="text-muted"><?= e(label_metode($p['payment_type'])); ?></small><?php endif; ?>
                            </td>
                            <td>
                                <?= pill(STATUS_PESANAN, $p['status']); ?>
                                <?php if ($p['status'] === 'dikirim' && $p['estimasi_tiba']): ?>
                                    <br><small class="text-muted">tiba <?= e(tgl_id($p['estimasi_tiba'], false)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm custom-btn" data-bs-toggle="modal" data-bs-target="#kelola<?= $id; ?>">Kelola</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalHal > 1): ?>
                <nav class="pager" aria-label="Halaman">
                    <?php if ($hal > 1): ?><a href="<?= e(url_pesanan(['hal' => $hal - 1])); ?>">&laquo; Sebelumnya</a><?php endif; ?>
                    <?php for ($i = 1; $i <= $totalHal; $i++): ?>
                        <a href="<?= e(url_pesanan(['hal' => $i])); ?>" class="<?= $i === $hal ? 'active' : ''; ?>"><?= $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($hal < $totalHal): ?><a href="<?= e(url_pesanan(['hal' => $hal + 1])); ?>">Berikutnya &raquo;</a><?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Kelola (satu per pesanan) -->
    <?php foreach ($daftarPesanan as $p): $id = (int) $p['id']; ?>
        <div class="modal fade" id="kelola<?= $id; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="<?= e(url_pesanan()); ?>">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="pesanan_id" value="<?= $id; ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Kelola <?= e($p['order_id'] ?: '#' . $id); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <?php if ($p['status_bayar'] !== 'paid'): ?>
                                <div class="alert alert-warning">Pesanan belum dibayar, jadi status Diproses/Dikirim/Selesai belum bisa dipilih.</div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Status pesanan</label>
                                <select name="status" class="form-select">
                                    <?php foreach (STATUS_PESANAN as $k => $v): ?>
                                        <option value="<?= e($k); ?>" <?= $p['status'] === $k ? 'selected' : ''; ?>><?= e($v[0]); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kurir</label>
                                <select name="kurir" class="form-select">
                                    <option value="">-</option>
                                    <?php foreach (KURIR as $k): ?>
                                        <option <?= $p['kurir'] === $k ? 'selected' : ''; ?>><?= e($k); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor resi</label>
                                <input type="text" name="no_resi" maxlength="50" class="form-control" value="<?= e($p['no_resi']); ?>">
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Estimasi tiba</label>
                                <input type="date" name="estimasi_tiba" class="form-control"
                                       value="<?= e($p['estimasi_tiba'] ?: date('Y-m-d', strtotime('+3 days'))); ?>">
                                <div class="form-text">Wajib diisi saat status Dikirim. Pelanggan melihat tanggal ini.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="kelola_pesanan" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <p class="footer-copy text-center border-0">&copy; 2025 Wins Beads &middot; Admin</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
