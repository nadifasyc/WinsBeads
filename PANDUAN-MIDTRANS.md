
# Dokumentasi Integrasi Pembayaran Midtrans

## Overview

Wins Beads mengintegrasikan **Midtrans Snap API** untuk menangani pembayaran online. Integrasi ini memungkinkan pelanggan menyelesaikan pembayaran melalui metode yang tersedia di Midtrans, seperti QRIS, virtual account, dan e-wallet.

Integrasi ini dikembangkan dengan PHP Native dan mencakup pembuatan token pembayaran, pemrosesan notifikasi transaksi, verifikasi pembayaran, serta sinkronisasi status pesanan.

## Payment Flow

Alur pembayaran pada Wins Beads dirancang untuk memisahkan proses pembayaran di sisi pelanggan dari proses verifikasi transaksi di sisi server.

1. **Checkout:** Sistem menyimpan pesanan dan membuat permintaan token pembayaran ke Midtrans menggunakan Server Key.
2. **Payment:** Pelanggan menyelesaikan pembayaran melalui antarmuka Midtrans Snap.
3. **Notification:** Midtrans mengirimkan notifikasi transaksi ke endpoint webhook pada server.
4. **Verification:** Server memverifikasi signature notifikasi dan memeriksa nominal transaksi sebelum memperbarui status pembayaran.
5. **Order Update:** Status pembayaran dan pesanan diperbarui berdasarkan hasil verifikasi transaksi.

Webhook digunakan sebagai mekanisme utama untuk menerima pembaruan status pembayaran. Callback pada browser tidak dijadikan satu-satunya dasar untuk menentukan keberhasilan transaksi karena hasil yang diterima browser tidak cukup untuk memverifikasi pembayaran secara independen.

## Technical Implementation

### 1. Snap Token Generation

Server membuat token pembayaran melalui Snap API menggunakan Server Key sebagai kredensial autentikasi.

Informasi transaksi dikirim ke Midtrans untuk menghasilkan token yang digunakan oleh Snap.js pada halaman pembayaran.

Implementasi ini memisahkan kredensial server dari antarmuka pelanggan agar Server Key tidak terekspos di browser.

### 2. Payment Notification & Verification

Endpoint webhook menerima notifikasi transaksi dari Midtrans dan memverifikasi keaslian notifikasi sebelum memperbarui data pesanan.

Proses verifikasi mencakup:

- Validasi signature notifikasi menggunakan mekanisme verifikasi Midtrans.
- Pemeriksaan nominal transaksi terhadap total pesanan yang tersimpan di database.
- Pemetaan status transaksi Midtrans ke status pembayaran aplikasi.
- Pembaruan status pesanan berdasarkan hasil verifikasi yang valid.

Sistem juga mempertimbangkan notifikasi berulang agar pemrosesan transaksi tidak menyebabkan perubahan status yang tidak semestinya.

### 3. Payment Status Synchronization

Selain notifikasi webhook, Wins Beads menyediakan mekanisme sinkronisasi menggunakan Midtrans Status API.

Mekanisme ini digunakan sebagai cadangan ketika status transaksi di database belum diperbarui, misalnya karena webhook tidak dapat diterima saat aplikasi berjalan di localhost.

Server mengambil status transaksi langsung dari Midtrans, memverifikasi informasi transaksi, lalu menyinkronkannya dengan data pesanan.

Status pembayaran yang dikirim melalui parameter URL browser tidak digunakan sebagai sumber kebenaran transaksi.

## Security Considerations

Beberapa aspek keamanan yang diterapkan dalam integrasi pembayaran ini meliputi:

- **Credential Protection:** Server Key disimpan dalam konfigurasi lokal dan tidak disertakan dalam repository.
- **Signature Verification:** Notifikasi pembayaran diverifikasi sebelum diproses.
- **Amount Validation:** Nominal transaksi diperiksa terhadap total pesanan di database.
- **Server-Side Verification:** Status pembayaran ditentukan berdasarkan informasi transaksi yang diverifikasi oleh server, bukan hanya berdasarkan callback browser.
- **Transaction Consistency:** Pemrosesan notifikasi mempertimbangkan pengiriman ulang dan perubahan status transaksi agar data pesanan tetap konsisten.

## Local Testing

Pengujian integrasi dilakukan menggunakan lingkungan **Midtrans Sandbox** agar alur pembayaran dapat diuji tanpa menggunakan transaksi uang sungguhan.

Untuk pengujian webhook pada localhost, diperlukan endpoint yang dapat diakses dari internet. Salah satu opsi yang dapat digunakan adalah [ngrok](https://ngrok.com/).

Endpoint notifikasi perlu dikonfigurasikan pada dashboard Midtrans Sandbox sesuai dengan alamat lokal yang diekspos melalui tunnel.

Kredensial Sandbox dan konfigurasi pengujian disimpan secara lokal dan tidak disertakan dalam repository.

## Production Considerations

Sebelum digunakan pada lingkungan produksi, integrasi pembayaran perlu dikonfigurasikan menggunakan kredensial Production Midtrans, endpoint webhook ber-HTTPS, serta konfigurasi transaksi yang sesuai dengan lingkungan produksi.

Pengujian tambahan juga diperlukan untuk memastikan penanganan notifikasi berulang, perubahan status transaksi, kegagalan pembayaran, dan sinkronisasi status pesanan berjalan dengan benar.

---

Dokumentasi ini menjelaskan pendekatan teknis integrasi pembayaran Midtrans pada proyek portofolio Wins Beads.