<?php
/** Pengaturan lokasi toko dan tarif ongkir berbasis jarak.*/
const TOKO = [
    'nama'          => 'Wins Beads Medan',
    'lat'           => 3.5952,    // pusat Kota Medan
    'lng'           => 98.6722,
    'tarif_dasar'   => 8000,      // Rp, biaya awal
    'tarif_per_km'  => 2500,      // Rp per km
    'faktor_jalan'  => 1.3,       // jarak garis lurus x faktor ini ~ jarak lewat jalan
    'maks_km'       => 50,        // di atas jarak ini pesanan tidak bisa dikirim
    'pembulatan'    => 500,       // ongkir dibulatkan ke atas ke kelipatan ini
];

function jarak_garis_lurus_km(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $r = 6371.0088;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
    return 2 * $r * asin(min(1.0, sqrt($a)));
}

/** Hitung ongkir dari titik tujuan.*/
function hitung_ongkir($lat, $lng): array
{
    if (!is_numeric($lat) || !is_numeric($lng)) {
        return ['ok' => false, 'jarak_km' => 0.0, 'ongkir' => 0, 'pesan' => 'Pilih lokasi pengiriman di peta.'];
    }
    $lat = (float) $lat;
    $lng = (float) $lng;
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        return ['ok' => false, 'jarak_km' => 0.0, 'ongkir' => 0, 'pesan' => 'Koordinat tidak valid.'];
    }

    $jarak = jarak_garis_lurus_km(TOKO['lat'], TOKO['lng'], $lat, $lng) * TOKO['faktor_jalan'];
    $jarak = round($jarak, 1);

    if ($jarak > TOKO['maks_km']) {
        return ['ok' => false, 'jarak_km' => $jarak, 'ongkir' => 0,
                'pesan' => 'Lokasi di luar jangkauan pengiriman (maksimal ' . TOKO['maks_km'] . ' km dari toko).'];
    }

    $mentah = TOKO['tarif_dasar'] + TOKO['tarif_per_km'] * $jarak;
    $ongkir = (int) (ceil($mentah / TOKO['pembulatan']) * TOKO['pembulatan']);
    return ['ok' => true, 'jarak_km' => $jarak, 'ongkir' => $ongkir, 'pesan' => ''];
}
