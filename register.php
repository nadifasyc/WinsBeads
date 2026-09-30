<?php
require "koneksi.php";

$error = "";
$success = "";

if (isset($_POST['registerbtn'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if ($username === '' || $password === '' || $confirm === '') {
        $error = "Semua kolom wajib diisi.";
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
            $stmtInsert = $con->prepare("INSERT INTO user (username, password, email) VALUES (?, ?, ?)");
            $stmtInsert->bind_param("sss", $username, $hash, $email);
            if ($stmtInsert->execute()) {
                $success = "Registrasi berhasil. Silakan login.";
            } else {
                $error = "Terjadi kesalahan. Coba lagi.";
            }
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Registrasi | Wins Beads</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: url('image/background.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-box {
            background: rgba(151, 110, 137, 0.6);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 420px;
            color: #fff;
        }

        .register-box h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2rem;
            text-shadow: 0 0 6px rgba(0, 0, 0, 0.4);
        }

        label {
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            backdrop-filter: blur(5px);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.3);
            outline: none;
            box-shadow: 0 0 10px rgba(199, 139, 179, 0.5);
        }

        .custom-register-btn {
            background-color: rgba(199, 139, 179, 0.6);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            padding: 12px;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .custom-register-btn:hover {
            background-color: rgba(199, 139, 179, 0.8);
        }

        .error-msg, .success-msg {
            margin-top: 15px;
            font-weight: 500;
            text-align: center;
            padding: 10px 15px;
            border-radius: 8px;
        }

        .error-msg {
            background-color: rgba(179, 58, 58, 0.2);
            color: #fff;
            border: 1px solid rgba(179, 58, 58, 0.4);
        }

        .success-msg {
            background-color: rgba(58, 179, 97, 0.2);
            color: #fff;
            border: 1px solid rgba(58, 179, 97, 0.4);
        }

        .text-link {
            text-align: center;
            margin-top: 15px;
        }

        .text-link a {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-box">
    <h2>Registrasi</h2>
    <form method="post" autocomplete="off" novalidate>
        <div class="mb-3">
            <label for="username">Username</label>
            <input type="text"
                   id="username"
                   name="username"
                   class="form-control"
                   placeholder="Masukkan username"
                   required
                   value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES); ?>" />
        </div>
        <div class="mb-3">
            <label for="email">Email (opsional)</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   placeholder="Masukkan email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>" />
        </div>
        <div class="mb-3">
            <label for="password">Password</label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   placeholder="Masukkan password"
                   required />
        </div>
        <div class="mb-3">
            <label for="confirm">Konfirmasi Password</label>
            <input type="password"
                   id="confirm"
                   name="confirm"
                   class="form-control"
                   placeholder="Ulangi password"
                   required />
        </div>
        <button type="submit" name="registerbtn" class="custom-register-btn">Daftar</button>
    </form>

    <div class="text-link">
        <small>
            <a href="login.php">Sudah punya akun? Login di sini</a>
        </small>
    </div>

    <?php if ($error): ?>
        <div class="error-msg" role="alert" aria-live="assertive"><?= $error; ?></div>
    <?php elseif ($success): ?>
        <div class="success-msg" role="alert" aria-live="polite"><?= $success; ?></div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>