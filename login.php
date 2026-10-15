<?php
require "config/init.php";

$error = "";

if (isset($_POST['loginbtn'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = $con->prepare("SELECT id, username, password FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Pesan error sengaja sama agar penyerang tidak bisa menebak username yang terdaftar.
        if ($data && password_verify($password, $data['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $data['id'], 'username' => $data['username']];

            if (password_needs_rehash($data['password'], PASSWORD_DEFAULT)) {
                $baru = password_hash($password, PASSWORD_DEFAULT);
                $up = $con->prepare("UPDATE user SET password = ? WHERE id = ?");
                $up->bind_param("si", $baru, $data['id']);
                $up->execute();
            }

            $tujuanOk = ['checkout' => 'checkout.php', 'chat' => 'chat.php'];   // daftar putih: tidak ada open redirect
            $tujuan = $tujuanOk[$_GET['next'] ?? ''] ?? 'index.php';
            header("Location: $tujuan");
            exit;
        }
        $error = "Username atau password salah.";
    }
}

$pageTitle = "Login";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "head.php"; ?>
</head>
<body class="login-page-body">

<main class="login-box">
  <h2>Login Pengguna</h2>
  <form action="" method="post">
<?= csrf_field(); ?>
    <div class="mb-3">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES); ?>">
    </div>
    <div class="mb-3">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
    </div>
    <button type="submit" name="loginbtn" class="custom-login-btn">Login</button>

    <div class="auth-alt">
      <a href="register.php" class="btn-ghost">Belum punya akun? Daftar</a>
      <a href="index.php" class="btn-ghost">Lanjut sebagai Guest</a>
    </div>
  </form>

  <?php if ($error): ?>
    <div class="error-msg" role="alert" aria-live="assertive"><?= htmlspecialchars($error); ?></div>
  <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
