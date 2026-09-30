<?php
require_once "init.php";
if (!function_exists('hitung_total_item')) {
    function hitung_total_item($keranjang) {
        return array_sum(array_column($keranjang, 'jumlah'));
    }
}

$totalItem = isset($_SESSION['keranjang']) ? hitung_total_item($_SESSION['keranjang']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Navbar | Winsbeads</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <style>
    body {
      background: url('image/background.jpg') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
    }

    .navbar {
      background: rgba(158, 94, 137, 0.37);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
      border-radius: 12px;
      margin: 1rem 2rem;
      transition: background 0.3s ease;
    }

    .navbar:hover {
      background: rgba(199, 139, 179, 0.44);
    }

    .navbar-brand {
      font-weight: 700;
      color: #fff !important;
      font-size: 1.6rem;
      text-shadow: 0 0 8px rgba(0, 0, 0, 0.4);
    }

    .nav-link {
      color: #f0f0f0 !important;
      position: relative;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .nav-link::after {
      content: "";
      position: absolute;
      width: 0%;
      height: 2px;
      bottom: 0;
      left: 0;
      background-color: #fff;
      transition: width 0.3s ease;
      border-radius: 2px;
    }

    .nav-link:hover {
      color: #fff !important;
    }

    .nav-link:hover::after {
      width: 100%;
    }

    .navbar-toggler {
      border: none;
      filter: drop-shadow(0 0 1px rgba(255, 255, 255, 0.7));
    }

    .navbar-toggler:focus {
      box-shadow: none;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <i class="fas fa-gem me-2"></i> Wins Beads
    </a>
    <button
      class="navbar-toggler"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#navbarContent"
      aria-controls="navbarContent"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item me-3">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <li class="nav-item me-3">
          <a class="nav-link" href="produk.php">Produk</a>
        </li>
        <li class="nav-item me-3">
          <a class="nav-link position-relative" href="keranjang.php">
            <i class="fas fa-shopping-cart"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  <?= $totalItem; ?>
              </span>
          </a>
        </li>
      </ul>

<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
  <?php if (isset($_SESSION['user'])): ?>
    <li class="nav-item">
      <a class="nav-link" href="logout.php">
        <i class="fas fa-sign-out-alt me-1"></i> Logout
      </a>
    </li>
  <?php else: ?>
    <li class="nav-item">
      <a class="nav-link" href="login.php">
        <i class="fas fa-sign-in-alt me-1"></i> Login
      </a>
    </li>
  <?php endif; ?>
</ul>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
