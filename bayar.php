<?php
require "config/init.php";
require "config/midtrans.php";

$order_id = $_GET['order'] ?? '';
$stmt = mysqli_prepare($con, "SELECT order_id, user_id, total, status_bayar, snap_token FROM pesanan WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, "s", $order_id);
mysqli_stmt_execute($stmt);
$pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$pesanan || !isset($_SESSION['user']) || (int) $pesanan['user_id'] !== (int) $_SESSION['user']['id']) {
    header("Location: index.php");
    exit;
}
if ($pesanan['status_bayar'] === 'paid') {
    header("Location: checkout-berhasil.php?order=" . urlencode($order_id));
    exit;
}

$pageTitle = "Pembayaran";
$bisaBayar = $pesanan['status_bayar'] === 'pending' && !empty($pesanan['snap_token']);
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
        <h1><i class="fas fa-wallet me-2"></i>Pembayaran</h1>
        <p>Nomor pesanan: <strong><?= htmlspecialchars($pesanan['order_id']); ?></strong></p>
        <p>Total: <strong>Rp <?= number_format($pesanan['total'], 0, ',', '.'); ?></strong></p>

        <?php if ($bisaBayar): ?>
          <button id="pay-button" type="button" class="btn-custom"><i class="fas fa-credit-card"></i> Bayar Sekarang</button>
          <p class="mt-4 mb-0"><small>Popup tertutup? Klik tombol di atas untuk membukanya lagi.</small></p>
        <?php else: ?>
          <p>Pembayaran untuk pesanan ini <strong><?= $pesanan['status_bayar'] === 'expired' ? 'sudah kedaluwarsa' : 'gagal'; ?></strong>. Silakan buat pesanan baru.</p>
          <a href="produk.php" class="btn-custom">Belanja Lagi</a>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <?php if ($bisaBayar): ?>
  <!-- Client Key BOLEH tampil di browser (berbeda dengan Server Key) -->
  <script src="<?= midtrans_snap_js_url(); ?>" data-client-key="<?= htmlspecialchars(midtrans_config()['client_key']); ?>"></script>
  <script>
    const snapToken = <?= json_encode($pesanan['snap_token']); ?>;
    const orderId   = <?= json_encode($pesanan['order_id']); ?>;
    const hasil     = 'checkout-berhasil.php?order=' + encodeURIComponent(orderId);

    function bayar() {
      window.snap.pay(snapToken, {
        onSuccess: function () { window.location = hasil; },
        onPending: function () { window.location = hasil; },
        onError:   function () { alert('Pembayaran gagal. Silakan coba lagi.'); },
        onClose:   function () { /* pelanggan menutup popup; tombol tetap bisa diklik lagi */ }
      });
    }
    document.getElementById('pay-button').addEventListener('click', bayar);
    window.addEventListener('load', bayar);   // buka popup otomatis
  </script>
  <?php endif; ?>
</body>
</html>
