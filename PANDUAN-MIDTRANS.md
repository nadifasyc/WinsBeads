# Panduan Integrasi Midtrans (Snap) untuk Winsbeads

## 1. Cara kerjanya (bayangkan dulu alurnya)

```
Pelanggan klik "Selesaikan Pesanan"
   │
   ▼
checkout-proses.php  (SERVER)
   1. simpan pesanan, status_bayar = pending
   2. minta "Snap token" ke Midtrans (pakai SERVER KEY, rahasia)
   3. redirect ke bayar.php
   │
   ▼
bayar.php  (BROWSER)
   snap.js membuka popup pembayaran memakai Snap token (pakai CLIENT KEY, boleh publik)
   │
   ▼  pelanggan bayar (QRIS, VA bank, e-wallet, kartu, dst.)
   │
Midtrans ──POST──▶ midtrans-notif.php  (SERVER, webhook)
   memverifikasi signature, lalu ubah status_bayar → paid / failed / expired
```

Kenapa ada webhook? Karena browser pelanggan bisa saja tertutup atau dimanipulasi.
**Status bayar yang dipercaya hanya yang datang dari webhook**, bukan dari callback `onSuccess` di JavaScript.

Dua kunci, dua fungsi:
| Kunci | Dipakai di | Boleh tampil di browser? |
|---|---|---|
| Server Key | PHP (membuat token, cek signature) | TIDAK, rahasia |
| Client Key | `snap.js` | Ya |

## 2. Langkah persiapan (sekali saja)

1. Daftar di https://dashboard.sandbox.midtrans.com (akun **Sandbox**, gratis, uang tidak sungguhan).
2. Buka **Settings → Access Keys**, salin Server Key dan Client Key (awalan `SB-Mid-...`).
3. Salin `config/midtrans.local.example.php` menjadi `config/midtrans.local.php`, lalu isi kedua kunci.
   File ini sudah ada di `.gitignore`. **Jangan pernah meng-commit kunci ke GitHub.**
   Kalau pernah ter-commit, anggap bocor dan generate ulang kuncinya.
4. Buka phpMyAdmin → database `winsbeads` → tab SQL → jalankan isi `migrasi-midtrans.sql`.
5. Pastikan PHP punya ekstensi cURL (XAMPP: buka `php.ini`, hapus `;` di depan `extension=curl`, restart Apache).

## 3. Mengetes alur bayar di localhost

1. Login sebagai user, tambah produk ke keranjang, checkout.
2. Popup Midtrans muncul. Pilih metode, pakai data uji dari
   https://docs.midtrans.com/docs/testing-payment-on-sandbox (kartu uji `4811 1111 1111 1114`, CVV `123`, OTP `112233`).
3. Setelah bayar kamu diarahkan ke `checkout-berhasil.php`.

Di tahap ini status di database mungkin masih `pending`, karena webhook belum bisa masuk ke localhost. Lanjut langkah 4.

## 4. Membuat webhook bisa diterima di localhost

Midtrans harus bisa menghubungi servermu lewat internet, jadi localhost tidak terjangkau. Pakai tunnel:

1. Pasang ngrok (https://ngrok.com), lalu jalankan: `ngrok http 80`
2. Salin URL `https://xxxx.ngrok-free.app`.
3. Di dashboard Midtrans: **Settings → Configuration → Payment Notification URL**, isi
   `https://xxxx.ngrok-free.app/winsbeads/midtrans-notif.php` (sesuaikan nama folder), klik **Update**.
4. Ulangi tes bayar. Cek halaman admin → Pesanan, kolom **Pembayaran** harus berubah jadi **Lunas**.
5. Kalau gagal, lihat log di dashboard ngrok (http://127.0.0.1:4040) untuk melihat request dari Midtrans dan jawabanmu.

URL ngrok gratis berubah tiap dijalankan, jadi perbarui di dashboard setiap kali.

## 5. Penjelasan file

| File | Peran |
|---|---|
| `config/midtrans.php` | Fungsi bantu: buat token (cURL), verifikasi signature, petakan status |
| `config/midtrans.local.php` | Kunci rahasia (tidak ikut Git) |
| `checkout-proses.php` | Simpan pesanan → minta token → redirect |
| `bayar.php` | Halaman popup Snap, bisa dibuka ulang kalau popup tertutup |
| `midtrans-notif.php` | Webhook: verifikasi lalu update status |
| `checkout-berhasil.php` | Menampilkan hasil sesuai status di database |
| `migrasi-midtrans.sql` | Kolom baru di tabel `pesanan` |

Pelajari tiga bagian ini untuk memahami intinya:
- `midtrans_create_snap_token()`: request ke Midtrans dengan Basic Auth `base64(ServerKey + ":")`.
- `midtrans_signature_valid()`: `SHA512(order_id + status_code + gross_amount + ServerKey)` dibandingkan dengan `signature_key` dari Midtrans.
- `midtrans_map_status()`: arti `settlement`, `capture`, `pending`, `expire`, `deny`, `cancel`.

## 6. Aturan penting

- `gross_amount` harus **integer** dan sama dengan jumlah `price × quantity` semua item.
- `order_id` harus unik, maksimal 50 karakter, dan tidak boleh dipakai ulang.
- Webhook harus membalas HTTP **200**, kalau tidak Midtrans akan mengirim ulang.
- Webhook harus **idempoten**: pesanan yang sudah `paid` tidak diturunkan lagi.
- Selalu cek nominal di webhook sama dengan nominal pesananmu.

## 7. Pindah ke Production (nanti)

Daftarkan bisnis di dashboard production dan tunggu verifikasi Midtrans. Lalu ganti kunci menjadi kunci production dan `'is_production' => true`. Pasang situs di hosting ber-HTTPS, dan isi Payment Notification URL di dashboard **production** (terpisah dari sandbox).


## Tambahan: halaman tujuan setelah bayar & cadangan webhook

**1. Kenapa setelah bayar muncul `example.com`?**
Itu *Finish Redirect URL* bawaan dashboard Sandbox. Sekarang `checkout-proses.php` mengirim `callbacks.finish`
ke `checkout-berhasil.php` untuk tiap transaksi, jadi pembeli kembali ke tokomu. Midtrans menambahkan `?order_id=...`
di akhir alamat itu.

**2. Kenapa status bisa tetap "menunggu" padahal sudah dibayar?**
Status hanya berubah saat webhook sampai. Di localhost webhook butuh ngrok yang hidup, dan alamat ngrok versi gratis
berubah setiap dinyalakan ulang (harus diperbarui di dashboard). Sebagai cadangan, saat pelanggan membuka
`checkout-berhasil.php`, `pesanan-detail.php`, atau `pesanan-saya.php`, server memanggil **Status API**
(`GET /v2/{order_id}/status`, fungsi `midtrans_sinkron_pesanan()`), memeriksa signature dan nominalnya, lalu memperbarui status.
Parameter `transaction_status` di URL **tidak dipercaya**; hanya jawaban resmi dari Midtrans yang dipakai.
Di server produksi, webhook tetap menjadi sumber utama.
