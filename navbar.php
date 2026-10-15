<?php
require_once "config/init.php";
require_once __DIR__ . "/config/chat.php";

if (!function_exists('hitung_total_item')) {
    function hitung_total_item($keranjang) {
        return array_sum(array_column($keranjang, 'jumlah'));
    }
}

$totalItem = isset($_SESSION['keranjang']) ? hitung_total_item($_SESSION['keranjang']) : 0;
$chatBaru  = isset($_SESSION['user']['id']) ? chat_belum_dibaca_user($con, (int) $_SESSION['user']['id']) : 0;
$halaman   = basename($_SERVER['SCRIPT_NAME']);
$aktif     = fn(array $files) => in_array($halaman, $files, true) ? ' active' : '';
?>
<header class="site-nav">
  <div class="container">
    <nav class="nav-inner glass-card" aria-label="Navigasi utama">
      <a class="nav-brand" href="index.php"><i class="fas fa-gem"></i> Wins Beads</a>

      <button class="nav-toggle" type="button" aria-label="Buka menu" aria-expanded="false"
              onclick="var l=document.getElementById('navLinks');var o=l.classList.toggle('open');this.setAttribute('aria-expanded',o)">
        <i class="fas fa-bars"></i>
      </button>

      <ul class="nav-links" id="navLinks">
        <li><a class="<?= trim($aktif(['index.php'])); ?>" href="index.php">Home</a></li>
        <li><a class="<?= trim($aktif(['produk.php', 'produk-detail.php'])); ?>" href="produk.php">Produk</a></li>
        <li>
          <a class="<?= trim($aktif(['keranjang.php', 'checkout.php'])); ?>" href="keranjang.php">
            <i class="fas fa-shopping-cart"></i> Keranjang
            <?php if ($totalItem > 0): ?><span class="nav-badge"><?= $totalItem; ?></span><?php endif; ?>
          </a>
        </li>
        <?php if (isset($_SESSION['user'])): ?>
          <li><a class="<?= trim($aktif(['pesanan-saya.php', 'pesanan-detail.php'])); ?>" href="pesanan-saya.php"><i class="fas fa-box"></i> Pesanan</a></li>
          <li>
            <a class="<?= trim($aktif(['chat.php'])); ?>" href="chat.php"><i class="fas fa-comments"></i> Chat
              <?php if ($chatBaru > 0): ?><span class="nav-badge"><?= $chatBaru; ?></span><?php endif; ?>
            </a>
          </li>
          <li><a class="<?= trim($aktif(['profil.php'])); ?>" href="profil.php"><i class="fas fa-user"></i> <?= e($_SESSION['user']['username']); ?></a></li>
          <li><a href="logout.php" aria-label="Logout"><i class="fas fa-sign-out-alt"></i></a></li>
        <?php else: ?>
          <li><a class="<?= trim($aktif(['login.php', 'register.php'])); ?>" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>
