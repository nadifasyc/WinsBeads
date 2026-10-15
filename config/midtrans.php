<?php

function midtrans_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $file = __DIR__ . '/midtrans.local.php';
        if (!is_file($file)) {
            http_response_code(500);
            exit('Kunci Midtrans belum diatur. Salin config/midtrans.local.example.php menjadi config/midtrans.local.php lalu isi kuncinya.');
        }
        $cfg = require $file;
    }
    return $cfg;
}

function midtrans_snap_js_url(): string
{
    return midtrans_config()['is_production']
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
}

function midtrans_api_url(): string
{
    return midtrans_config()['is_production']
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
}

function midtrans_create_snap_token(array $params): array
{
    $cfg = midtrans_config();

    $ch = curl_init(midtrans_api_url());
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($params),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($cfg['server_key'] . ':'),
        ],
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) {
        throw new Exception('Tidak bisa menghubungi Midtrans: ' . $err);
    }
    $json = json_decode($body, true);
    if ($code >= 300 || empty($json['token'])) {
        $pesan = $json['error_messages'][0] ?? ('HTTP ' . $code);
        throw new Exception('Midtrans menolak permintaan: ' . $pesan);
    }
    return $json;
}

function midtrans_core_url(): string
{
    return midtrans_config()['is_production'] ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
}

function midtrans_get_status(string $orderId): ?array
{
    $cfg = midtrans_config();
    $ch = curl_init(midtrans_core_url() . '/v2/' . rawurlencode($orderId) . '/status');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 6,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($cfg['server_key'] . ':'),
        ],
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    $json = $body === false ? null : json_decode($body, true);
    if (!is_array($json) || ($json['status_code'] ?? '') === '404' || empty($json['transaction_status'])) {
        return null;
    }
    return $json;
}

function midtrans_terapkan_status(mysqli $con, array $n): string
{
    $stmt = $con->prepare("SELECT id, total, status_bayar FROM pesanan WHERE order_id = ?");
    $orderId = (string) ($n['order_id'] ?? '');
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    $pesanan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$pesanan) {
        return 'tidak_ada';
    }
    if ((int) round((float) ($n['gross_amount'] ?? 0)) !== (int) $pesanan['total']) {
        return 'nominal_beda';
    }
    if ($pesanan['status_bayar'] !== 'paid') {
        $statusBaru = midtrans_map_status($n['transaction_status'] ?? '', $n['fraud_status'] ?? null);
        $tipe = $n['payment_type'] ?? null;
        $sekarang = date('Y-m-d H:i:s');
        $upd = $con->prepare("UPDATE pesanan SET status_bayar = ?, payment_type = ?, dibayar_at = CASE WHEN ? = 'paid' THEN COALESCE(dibayar_at, ?) ELSE dibayar_at END WHERE id = ?");
        $idp = (int) $pesanan['id'];
        $upd->bind_param("ssssi", $statusBaru, $tipe, $statusBaru, $sekarang, $idp);
        $upd->execute();
        $upd->close();
    }
    return 'ok';
}

function midtrans_sinkron_pesanan(mysqli $con, string $orderId): bool
{
    $n = midtrans_get_status($orderId);
    if ($n === null || ($n['order_id'] ?? '') !== $orderId) {
        return false;
    }
    if (isset($n['signature_key']) && !midtrans_signature_valid($n, midtrans_config()['server_key'])) {
        return false;
    }
    return midtrans_terapkan_status($con, $n) === 'ok';
}

function midtrans_signature_valid(array $n, string $serverKey): bool
{
    foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $k) {
        if (!isset($n[$k]) || !is_string($n[$k])) {
            return false;
        }
    }
    $expected = hash('sha512', $n['order_id'] . $n['status_code'] . $n['gross_amount'] . $serverKey);
    return hash_equals($expected, $n['signature_key']);
}

function midtrans_map_status(string $transactionStatus, ?string $fraudStatus = null): string
{
    switch ($transactionStatus) {
        case 'capture':                       // kartu kredit
            return ($fraudStatus === 'accept') ? 'paid' : 'pending';
        case 'settlement':                    // transfer/e-wallet/QRIS berhasil
            return 'paid';
        case 'pending':
            return 'pending';
        case 'expire':
            return 'expired';
        case 'deny':
        case 'cancel':
        case 'failure':
            return 'failed';
        default:
            return 'pending';
    }
}
