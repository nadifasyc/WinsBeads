<?php
require "../config/init.php";

$error = "";

if (isset($_POST['loginbtn'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = $con->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($data && password_verify($password, $data['password'])) {
            session_regenerate_id(true);
            $_SESSION['login'] = true;
            $_SESSION['admin_id'] = $data['id'];
            $_SESSION['admin_username'] = $data['username'];
            header('Location: index.php');
            exit;
        }
        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php $pageTitle = "Login Admin"; $base = "../"; require "../head.php"; ?>
</head>
<body class="login-page-body">

<div class="login-box" role="main" aria-label="Form Login Admin">
    <h2>Login Admin</h2>
    <form action="" method="post" autocomplete="off" novalidate>
<?= csrf_field(); ?>
        <div class="mb-3">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                class="form-control"
                placeholder="Masukkan username admin"
                required
                autofocus
                value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES); ?>"
            />
        </div>
        <div class="mb-3">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Masukkan password admin"
                required
            />
        </div>
        <button type="submit" name="loginbtn" class="custom-login-btn">Login Admin</button>
    </form>

    <?php if ($error): ?>
        <div class="error-msg" role="alert" aria-live="assertive">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>