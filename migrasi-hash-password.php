<?php

if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Hanya bisa dijalankan dari komputer lokal.');
}
require "config/init.php";

header('Content-Type: text/plain; charset=utf-8');

function sudah_hash(string $p): bool
{

    return (bool) preg_match('/^\$2[aby]\$\d{2}\$.{53}$/', $p) || str_starts_with($p, '$argon2');
}
foreach (['user', 'admin'] as $tabel) {
    $hasil = mysqli_query($con, "SELECT id, username, password FROM `$tabel`");
    $jumlah = 0;
    while ($row = mysqli_fetch_assoc($hasil)) {
        if (!sudah_hash($row['password'])) {
            $hash = password_hash($row['password'], PASSWORD_DEFAULT);
            $stmt = $con->prepare("UPDATE `$tabel` SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $row['id']);
            $stmt->execute();
            echo "[$tabel] {$row['username']}: di-hash\n";
            $jumlah++;
        }
    }
    echo "[$tabel] selesai, $jumlah akun diperbarui.\n";
}
echo "\nSelesai. HAPUS file migrasi-hash-password.php sekarang.\n";
