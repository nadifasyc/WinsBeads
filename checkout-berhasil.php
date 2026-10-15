<?php
require "config/init.php";
require "config/midtrans.php";

$order_id = $_GET['order_id'] ?? ($_GET['order'] ?? '');
if (!is_string($order_id)) {
    $order_id = '';
}
$statusBayar = null;
if ($order_id !== '') {
    $stmt = mysqli_prepare($con, "SELECT status_bayar FROM pesanan WHERE order_id = ? AND user_id = ?");
    $uid = (int) ($_SESSION['user']['id'] ?? 0);
    mysqli_stmt_bind_param($stmt, "si", $order_id, $uid);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $statusBayar = $row['status_bayar'] ?? null;

    // Jangan bergantung pada webhook (di localhost sering tidak sampai): tanya Midtrans langsung.
    // Parameter status di URL TIDAK dipercaya; hanya jawaban resmi Status API yang dipakai.
    if ($statusBayar === 'pending' && midtrans_sinkron_pesanan($con, $order_id)) {
        $stmt = mysqli_prepare($con, "SELECT status_bayar FROM pesanan WHERE order_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "si", $order_id, $uid);
        mysqli_stmt_execute($stmt);
        $statusBayar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['status_bayar'] ?? $statusBayar;
    }
}

$pageTitle = "Terima Kasih";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "head.php"; ?>
</head>
<body>
  <?php require "navbar.php"; ?>

  <main>
    <div class="container result-wrap">
      <div class="glass-card result-box">
        <?php if ($statusBayar === 'pending'): ?>
          <h1><i class="fas fa-hourglass-half me-2"></i>Menunggu Pembayaran</h1>
          <p>Pesananmu sudah tercatat. Selesaikan pembayaran sesuai instruksi, status akan diperbarui otomatis.</p>
          <a href="bayar.php?order=<?= urlencode($order_id); ?>" class="btn-custom"><i class="fas fa-credit-card"></i> Lihat Instruksi Bayar</a>
        <?php elseif ($statusBayar === 'failed' || $statusBayar === 'expired'): ?>
          <h1><i class="fas fa-circle-exclamation me-2"></i>Pembayaran Tidak Berhasil</h1>
          <p>Pembayaran gagal atau kedaluwarsa. Kamu bisa membuat pesanan baru.</p>
          <a href="produk.php" class="btn-custom">Belanja Lagi</a>
        <?php else: ?>
          <h1><i class="fas fa-heart me-2"></i>Terima Kasih!</h1>
          <p>Pembayaranmu telah kami terima.</p>
          <p>Kami akan segera menghubungi untuk konfirmasi dan pengiriman.</p>
          <a href="pesanan-saya.php" class="btn-custom"><i class="fas fa-box"></i> Lihat Pesananku</a>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
