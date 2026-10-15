<?php
require "config/init.php";
require "config/chat.php";

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php?next=chat");
    exit;
}
$uid = (int) $_SESSION['user']['id'];

// "Tanya admin" di halaman produk: ?produk=ID
$produk = null;
if (isset($_GET['produk']) && ctype_digit((string) $_GET['produk'])) {
    $r = db_rows($con, "SELECT id, nama, foto FROM produk WHERE id = ?", "i", [(int) $_GET['produk']]);
    $produk = $r[0] ?? null;
}

$pageTitle = "Chat dengan Admin";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "head.php"; ?>
</head>
<body>
  <?php require "navbar.php"; ?>

  <main>
    <div class="container" style="max-width: 760px;">
      <div class="page-head">
        <h1>Chat dengan Admin</h1>
        <p>Tanyakan produk, stok, atau pesananmu. Admin akan membalas di sini.</p>
      </div>

      <section class="glass-card chat-box" aria-label="Percakapan">
        <div id="chat-log" class="chat-log" role="log" aria-live="polite">
          <div id="chat-kosong" class="chat-kosong" hidden>
            <i class="fas fa-comments"></i>
            <p>Belum ada pesan. Tulis pertanyaanmu di bawah.</p>
          </div>
        </div>

        <form id="chat-form" class="chat-form" autocomplete="off">
          <?php if ($produk): ?>
            <div id="chat-konteks" class="chat-konteks">
              <span><i class="fas fa-gem"></i> Menanyakan: <strong><?= e($produk['nama']); ?></strong></span>
              <button type="button" id="chat-konteks-batal" class="chat-konteks-batal" aria-label="Hapus produk dari pesan">&times;</button>
            </div>
          <?php endif; ?>
          <div id="chat-info" class="chat-info" aria-live="polite"></div>
          <div class="chat-input-row">
            <textarea id="chat-input" rows="1" maxlength="<?= CHAT_MAKS_KARAKTER; ?>" class="form-control" placeholder="Tulis pesan…" title="Enter kirim, Shift+Enter baris baru" aria-label="Pesan"></textarea>
            <button type="submit" id="chat-kirim" class="btn-custom" aria-label="Kirim"><i class="fas fa-paper-plane"></i></button>
          </div>
        </form>
      </section>
    </div>
  </main>

  <?php require "footer.php"; ?>

  <script>
    window.CHAT = <?= json_encode([
        'api'      => 'chat-api.php',
        'peran'    => 'user',
        'csrf'     => csrf_token(),
        'produkId' => $produk ? (int) $produk['id'] : null,
    ]); ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/chat.js?v=<?= @filemtime(__DIR__ . '/assets/js/chat.js'); ?>"></script>
</body>
</html>
