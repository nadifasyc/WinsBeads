<?php

require_once __DIR__ . '/pesanan.php';   // tgl_id(), db_rows()

const CHAT_MAKS_KARAKTER = 1000;
const CHAT_MAKS_PER_MENIT = 20;

/** hari ini "18:05", hari lain "13 Okt, 18:05" */
function chat_waktu(?string $waktu): string
{
    if (!$waktu || ($t = strtotime($waktu)) === false) {
        return '';
    }
    return date('Y-m-d', $t) === date('Y-m-d')
        ? date('H:i', $t)
        : preg_replace('/ \d{4}, /', ', ', tgl_id($waktu));
}

function chat_format(array $r): array
{
    return [
        'id'     => (int) $r['id'],
        'dari'   => $r['pengirim'],
        'isi'    => $r['isi'],
        'waktu'  => chat_waktu($r['dibuat_at']),
        'dibaca' => (int) $r['dibaca'] === 1,
        'produk' => !empty($r['produk_nama'])
            ? ['nama' => $r['produk_nama'], 'url' => 'produk-detail.php?nama=' . urlencode($r['produk_nama'])]
            : null,
    ];
}

function chat_kirim(mysqli $con, int $userId, string $pengirim, string $isi, ?int $produkId = null): array
{
    $isi = trim(str_replace("\r\n", "\n", $isi));
    if ($isi === '') {
        return ['ok' => false, 'galat' => 'Pesan tidak boleh kosong.'];
    }
    if (mb_strlen($isi) > CHAT_MAKS_KARAKTER) {
        return ['ok' => false, 'galat' => 'Pesan terlalu panjang (maksimal ' . CHAT_MAKS_KARAKTER . ' karakter).'];
    }
    if (!in_array($pengirim, ['user', 'admin'], true)) {
        return ['ok' => false, 'galat' => 'Pengirim tidak valid.'];
    }

    $menitLalu = date('Y-m-d H:i:s', time() - 60);
    $n = db_rows($con, "SELECT COUNT(*) AS n FROM pesan WHERE user_id = ? AND pengirim = ? AND dibuat_at >= ?", "iss", [$userId, $pengirim, $menitLalu]);
    if ((int) $n[0]['n'] >= CHAT_MAKS_PER_MENIT) {
        return ['ok' => false, 'galat' => 'Terlalu banyak pesan. Tunggu sebentar lalu coba lagi.'];
    }

    if ($produkId !== null && !db_rows($con, "SELECT id FROM produk WHERE id = ?", "i", [$produkId])) {
        $produkId = null;
    }

    $sekarang = date('Y-m-d H:i:s');
    $stmt = $con->prepare("INSERT INTO pesan (user_id, pengirim, isi, produk_id, dibaca, dibuat_at) VALUES (?, ?, ?, ?, 0, ?)");
    $stmt->bind_param("issis", $userId, $pengirim, $isi, $produkId, $sekarang);
    $stmt->execute();
    $id = (int) $con->insert_id;
    $stmt->close();

    $baris = db_rows($con, "SELECT p.*, pr.nama AS produk_nama FROM pesan p LEFT JOIN produk pr ON pr.id = p.produk_id WHERE p.id = ?", "i", [$id]);
    return ['ok' => true, 'pesan' => chat_format($baris[0])];
}

function chat_ambil(mysqli $con, int $userId, int $setelahId, string $pembaca): array
{
    $dasar = "SELECT p.*, pr.nama AS produk_nama FROM pesan p LEFT JOIN produk pr ON pr.id = p.produk_id WHERE p.user_id = ?";
    if ($setelahId === 0) {
        // Pembukaan pertama: 100 pesan TERBARU (riwayat lebih lama tidak dimuat)
        $baris = array_reverse(db_rows($con, $dasar . " ORDER BY p.id DESC LIMIT 100", "i", [$userId]));
    } else {
        $baris = db_rows($con, $dasar . " AND p.id > ? ORDER BY p.id LIMIT 200", "ii", [$userId, $setelahId]);
    }
    $lawan = $pembaca === 'admin' ? 'user' : 'admin';
    $ada = false;
    foreach ($baris as $r) {
        if ($r['pengirim'] === $lawan && (int) $r['dibaca'] === 0) {
            $ada = true;
        }
    }
    if ($ada) {
        $stmt = $con->prepare("UPDATE pesan SET dibaca = 1 WHERE user_id = ? AND pengirim = ? AND dibaca = 0");
        $stmt->bind_param("is", $userId, $lawan);
        $stmt->execute();
        $stmt->close();
    }
    return array_map('chat_format', $baris);
}

function chat_terbaca_sampai(mysqli $con, int $userId, string $pengirim): int
{
    $r = db_rows($con, "SELECT MAX(id) AS m FROM pesan WHERE user_id = ? AND pengirim = ? AND dibaca = 1", "is", [$userId, $pengirim]);
    return (int) ($r[0]['m'] ?? 0);
}

/** Jumlah pesan admin yang belum dibaca pelanggan */
function chat_belum_dibaca_user(mysqli $con, int $userId): int
{
    $r = db_rows($con, "SELECT COUNT(*) AS n FROM pesan WHERE user_id = ? AND pengirim = 'admin' AND dibaca = 0", "i", [$userId]);
    return (int) $r[0]['n'];
}

/** Jumlah pesan pelanggan yang belum dibaca admin (semua percakapan) */
function chat_belum_dibaca_admin(mysqli $con): int
{
    $r = db_rows($con, "SELECT COUNT(*) AS n FROM pesan WHERE pengirim = 'user' AND dibaca = 0");
    return (int) $r[0]['n'];
}

/** Daftar percakapan untuk admin, yang terbaru di atas */
function chat_daftar_percakapan(mysqli $con): array
{
    $rows = db_rows($con, "SELECT u.id, u.username, m.id AS id_terakhir, m.isi AS terakhir, m.pengirim AS pengirim_terakhir, m.dibuat_at,
                                  (SELECT COUNT(*) FROM pesan x WHERE x.user_id = u.id AND x.pengirim = 'user' AND x.dibaca = 0) AS belum
                           FROM user u JOIN pesan m ON m.id = (SELECT MAX(id) FROM pesan WHERE user_id = u.id)
                           ORDER BY m.id DESC");
    return array_map(fn($r) => [
        'id'       => (int) $r['id'],
        'username' => $r['username'],
        'terakhir' => mb_strimwidth(str_replace("\n", ' ', $r['terakhir']), 0, 60, '…'),
        'saya'     => $r['pengirim_terakhir'] === 'admin',
        'waktu'    => chat_waktu($r['dibuat_at']),
        'belum'    => (int) $r['belum'],
    ], $rows);
}
