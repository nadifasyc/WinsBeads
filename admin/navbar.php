<?php
require_once __DIR__ . '/../config/chat.php';
$chatBaru = chat_belum_dibaca_admin($con);
$halaman = basename($_SERVER['SCRIPT_NAME']);
$aktif   = fn(string $f) => $halaman === $f ? 'active' : '';
?>
<header class="site-nav">
  <div class="container">
    <nav class="nav-inner glass-card" aria-label="Navigasi admin">
      <a class="nav-brand" href="index.php"><i class="fas fa-gem"></i> Wins Beads <small class="text-muted fw-normal">Admin</small></a>

      <button class="nav-toggle" type="button" aria-label="Buka menu" aria-expanded="false"
              onclick="var l=document.getElementById('navLinks');var o=l.classList.toggle('open');this.setAttribute('aria-expanded',o)">
        <i class="fas fa-bars"></i>
      </button>

      <ul class="nav-links" id="navLinks">
        <li><a class="<?= $aktif('index.php'); ?>" href="index.php">Home</a></li>
        <li><a class="<?= $aktif('kategori.php'); ?>" href="kategori.php">Kategori</a></li>
        <li><a class="<?= $aktif('produk.php'); ?>" href="produk.php">Produk</a></li>
        <li><a class="<?= $aktif('pesanan.php'); ?>" href="pesanan.php">Pesanan</a></li>
        <li>
          <a class="<?= $aktif('chat.php'); ?>" href="chat.php">Chat
            <?php if ($chatBaru > 0): ?><span class="nav-badge"><?= $chatBaru; ?></span><?php endif; ?>
          </a>
        </li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </nav>
  </div>
</header>
