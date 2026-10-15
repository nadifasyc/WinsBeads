<?php
require "config/init.php";

$error = "";
$success = "";

if (isset($_POST['registerbtn'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $email    = trim($_POST['email'] ?? '');

    if ($username === '' || $password === '' || $confirm === '') {
        $error = "Username dan password wajib diisi.";
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $error = "Username 3-30 karakter: huruf, angka, atau garis bawah (_).";
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter.";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        $stmt = $con->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username sudah digunakan.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $emailDb = $email === '' ? null : $email;
            $ins = $con->prepare("INSERT INTO user (username, password, email) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $username, $hash, $emailDb);
            if ($ins->execute()) {
                $success = "Registrasi berhasil. Silakan login.";
            } else {
                $error = "Terjadi kesalahan. Coba lagi.";
            }
        }
        $stmt->close();
    }
}

$pageTitle = "Registrasi";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "head.php"; ?>
</head>
<body class="login-page-body">

<main class="register-box">
  <h2>Registrasi</h2>
  <form method="post" autocomplete="off" novalidate>
<?= csrf_field(); ?>
    <div class="mb-3">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES); ?>">
    </div>
    <div class="mb-3">
      <label for="email">Email (opsional)</label>
      <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
    </div>
    <div class="mb-3">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
    </div>
    <div class="mb-3">
      <label for="confirm">Konfirmasi Password</label>
      <input type="password" id="confirm" name="confirm" class="form-control" placeholder="Ulangi password" required>
    </div>
    <button type="submit" name="registerbtn" class="custom-register-btn">Daftar</button>
  </form>

  <div class="text-link"><small><a href="login.php">Sudah punya akun? Login di sini</a></small></div>

  <?php if ($error): ?>
    <div class="error-msg" role="alert" aria-live="assertive"><?= htmlspecialchars($error); ?></div>
  <?php elseif ($success): ?>
    <div class="success-msg" role="alert" aria-live="polite"><?= htmlspecialchars($success); ?></div>
  <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
