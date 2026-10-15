<?php
require "config/init.php";
require "config/pesanan.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$userId = (int) $_SESSION['user']['id'];

$notif = ['akun' => null, 'password' => null];   // [jenis, pesan]

// Data terbaru dari database
$stmt = $con->prepare("SELECT username, email, password, created_at FROM user WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) {            // akun sudah tidak ada
    header("Location: logout.php");
    exit;
}

/* ---------- Ubah username & email ---------- */
if (isset($_POST['simpan_profil'])) {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $notif['akun'] = ['danger', 'Username 3-30 karakter: huruf, angka, atau garis bawah (_).'];
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $notif['akun'] = ['danger', 'Format email tidak valid.'];
    } else {
        // Username tidak boleh dipakai orang lain (akun sendiri dikecualikan)
        $cek = $con->prepare("SELECT id FROM user WHERE username = ? AND id <> ?");
        $cek->bind_param("si", $username, $userId);
        $cek->execute();
        $cek->store_result();
        if ($cek->num_rows > 0) {
            $notif['akun'] = ['danger', 'Username sudah dipakai akun lain.'];
        } else {
            $emailDb = $email === '' ? null : $email;
            $up = $con->prepare("UPDATE user SET username = ?, email = ? WHERE id = ?");
            $up->bind_param("ssi", $username, $emailDb, $userId);
            $up->execute();
            $_SESSION['user']['username'] = $username;
            $user['username'] = $username;
            $user['email'] = $emailDb;
            $notif['akun'] = ['success', 'Profil berhasil diperbarui.'];
        }
        $cek->close();
    }
}

/* ---------- Ganti password ---------- */
if (isset($_POST['ganti_password'])) {
    $lama   = $_POST['password_lama'] ?? '';
    $baru   = $_POST['password_baru'] ?? '';
    $ulangi = $_POST['password_ulangi'] ?? '';

    if (!password_verify($lama, $user['password'])) {
        $notif['password'] = ['danger', 'Password saat ini salah.'];
    } elseif (strlen($baru) < 8) {
        $notif['password'] = ['danger', 'Password baru minimal 8 karakter.'];
    } elseif ($baru !== $ulangi) {
        $notif['password'] = ['danger', 'Konfirmasi password baru tidak cocok.'];
    } elseif (password_verify($baru, $user['password'])) {
        $notif['password'] = ['danger', 'Password baru harus berbeda dari yang lama.'];
    } else {
        $hash = password_hash($baru, PASSWORD_DEFAULT);
        $up = $con->prepare("UPDATE user SET password = ? WHERE id = ?");
        $up->bind_param("si", $hash, $userId);
        $up->execute();
        session_regenerate_id(true);
        $notif['password'] = ['success', 'Password berhasil diganti.'];
    }
}

$pageTitle = "Profil Saya";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php require "head.php"; ?>
</head>
<body>
  <?php require "navbar.php"; ?>

  <main>
    <div class="container" style="max-width: 720px;">
      <div class="page-head">
        <h1>Profil Saya</h1>
        <p>Anggota sejak <?= e(tgl_id($user['created_at'], false)); ?></p>
      </div>

      <form method="post" class="glass-card panel mb-4" autocomplete="off">
        <?= csrf_field(); ?>
        <div class="panel-title"><i class="fas fa-user"></i> Data Akun</div>
        <?php if ($notif['akun']): ?><div class="alert alert-<?= e($notif['akun'][0]); ?>"><?= e($notif['akun'][1]); ?></div><?php endif; ?>

        <div class="mb-4">
          <label for="username" class="form-label">Username</label>
          <input type="text" id="username" name="username" class="form-control" required maxlength="30"
                 value="<?= e($_POST['username'] ?? $user['username']); ?>">
        </div>
        <div class="mb-4">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control" maxlength="100"
                 value="<?= e($_POST['email'] ?? $user['email']); ?>">
        </div>
        <button type="submit" name="simpan_profil" class="btn-custom">Simpan Perubahan</button>
      </form>

      <form method="post" class="glass-card panel" autocomplete="off">
        <?= csrf_field(); ?>
        <div class="panel-title"><i class="fas fa-lock"></i> Ganti Password</div>
        <?php if ($notif['password']): ?><div class="alert alert-<?= e($notif['password'][0]); ?>"><?= e($notif['password'][1]); ?></div><?php endif; ?>

        <div class="mb-4">
          <label for="password_lama" class="form-label">Password saat ini</label>
          <input type="password" id="password_lama" name="password_lama" class="form-control" required autocomplete="current-password">
        </div>
        <div class="mb-4">
          <label for="password_baru" class="form-label">Password baru</label>
          <input type="password" id="password_baru" name="password_baru" class="form-control" required minlength="8" autocomplete="new-password">
          <div class="form-text">Minimal 8 karakter.</div>
        </div>
        <div class="mb-4">
          <label for="password_ulangi" class="form-label">Ulangi password baru</label>
          <input type="password" id="password_ulangi" name="password_ulangi" class="form-control" required autocomplete="new-password">
        </div>
        <button type="submit" name="ganti_password" class="btn-custom">Ganti Password</button>
      </form>
    </div>
  </main>

  <?php require "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
