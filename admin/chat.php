<?php
require "session.php";
require "../config/init.php";
require "../config/chat.php";

$u = (int) ($_GET['u'] ?? 0);
$pelanggan = null;
if ($u > 0) {
    $r = db_rows($con, "SELECT id, username, email FROM user WHERE id = ?", "i", [$u]);
    $pelanggan = $r[0] ?? null;
}
$daftar = chat_daftar_percakapan($con);

$pageTitle = "Chat";
$base = "../";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "../head.php"; ?>
</head>
<body class="admin-body">
  <?php require "navbar.php"; ?>

  <main class="admin-main">
    <div class="container" style="max-width: 1040px;">
      <div class="page-head px-0">
        <h1>Chat Pelanggan</h1>
        <p>Balas pertanyaan pelanggan. Daftar dan pesan diperbarui otomatis.</p>
      </div>

      <div class="chat-admin <?= $pelanggan ? 'ada-thread' : ''; ?>">
        <aside class="glass-card chat-daftar" aria-label="Daftar percakapan">
          <div id="daftar-percakapan">
            <?php if (!$daftar): ?>
              <div class="chat-kosong-list">Belum ada percakapan.</div>
            <?php endif; ?>
            <?php foreach ($daftar as $c): ?>
              <a class="konv<?= $c['id'] === $u ? ' aktif' : ''; ?>" href="chat.php?u=<?= $c['id']; ?>">
                <div class="konv-atas"><strong>@<?= e($c['username']); ?></strong><span class="konv-waktu"><?= e($c['waktu']); ?></span></div>
                <div class="konv-bawah">
                  <span class="konv-isi"><?= $c['saya'] ? 'Anda: ' : ''; ?><?= e($c['terakhir']); ?></span>
                  <?php if ($c['belum'] > 0): ?><span class="nav-badge"><?= $c['belum']; ?></span><?php endif; ?>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </aside>

        <section class="glass-card chat-box chat-thread" aria-label="Percakapan">
          <?php if ($pelanggan): ?>
            <div class="chat-head">
              <a href="chat.php" class="chat-kembali" aria-label="Semua percakapan"><i class="fas fa-arrow-left"></i></a>
              <div><strong>@<?= e($pelanggan['username']); ?></strong><div class="small text-muted"><?= e($pelanggan['email'] ?? ''); ?></div></div>
            </div>
            <div id="chat-log" class="chat-log" role="log" aria-live="polite">
              <div id="chat-kosong" class="chat-kosong" hidden><i class="fas fa-comments"></i><p>Belum ada pesan.</p></div>
            </div>
            <form id="chat-form" class="chat-form" autocomplete="off">
              <div id="chat-info" class="chat-info" aria-live="polite"></div>
              <div class="chat-input-row">
                <textarea id="chat-input" rows="1" maxlength="<?= CHAT_MAKS_KARAKTER; ?>" class="form-control" placeholder="Tulis balasan…" aria-label="Balasan"></textarea>
                <button type="submit" id="chat-kirim" class="btn-custom" aria-label="Kirim"><i class="fas fa-paper-plane"></i></button>
              </div>
            </form>
          <?php else: ?>
            <div class="chat-kosong chat-kosong-besar">
              <i class="fas fa-comments"></i>
              <p>Pilih percakapan di sebelah kiri.</p>
            </div>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </main>

  <script>
    window.CHAT = <?= json_encode([
        'api'      => 'chat-api.php',
        'peran'    => 'admin',
        'csrf'     => csrf_token(),
        'userId'   => $pelanggan ? (int) $pelanggan['id'] : null,
        'daftarEl' => 'daftar-percakapan',
    ]); ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/chat.js?v=<?= @filemtime(__DIR__ . '/../assets/js/chat.js'); ?>"></script>
</body>
</html>
