
require "config/init.php";
require "config/toko.php";

header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'pesan' => 'Silakan login.']);
    exit;
}
echo json_encode(hitung_ongkir($_GET['lat'] ?? null, $_GET['lng'] ?? null));
