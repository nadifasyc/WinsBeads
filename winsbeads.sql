-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Okt 2026 pada 06.18
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `winsbeads`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2a$12$l9csEpo6pbuDUH6VtUhxeOjC73utV6.NXrmpfeJtzubf24l4Anueu');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama`) VALUES
(1, 'Fairy Bracelet'),
(2, 'Rope Bracelet'),
(3, 'Phone Strap'),
(4, 'Bag Charm');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesan`
--

CREATE TABLE `pesan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pengirim` enum('user','admin') NOT NULL,
  `isi` text NOT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `dibuat_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesan`
--

INSERT INTO `pesan` (`id`, `user_id`, `pengirim`, `isi`, `produk_id`, `dibaca`, `dibuat_at`) VALUES
(3, 1, 'user', 'Halo kak, apakah produk ini masih ada?', 11, 1, '2026-10-13 18:31:52'),
(4, 1, 'admin', 'Masih ready kak! Yuk langsung di-checkout dan diselesaikan pembayarannya sebelum kehabisan yaa! <3', NULL, 1, '2026-10-13 18:35:17'),
(5, 2, 'user', 'Kak ini bisa untuk semua tipe hp?', 12, 1, '2026-10-13 19:01:24'),
(6, 2, 'admin', 'Halo kak! Bisa banget untuk semua tipe HP yaa, asalkan pakai casing/case. Yuk langsung di-checkout dan diselesaikan pembayarannya sebelum kehabisan yaa! <3', NULL, 0, '2026-10-13 19:03:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `total` int(11) NOT NULL DEFAULT 0,
  `tanggal` datetime DEFAULT NULL,
  `status` enum('baru','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'baru',
  `status_bayar` enum('pending','paid','failed','expired') NOT NULL DEFAULT 'pending',
  `payment_type` varchar(50) DEFAULT NULL,
  `snap_token` varchar(100) DEFAULT NULL,
  `kurir` varchar(30) DEFAULT NULL,
  `no_resi` varchar(50) DEFAULT NULL,
  `estimasi_tiba` date DEFAULT NULL,
  `dibayar_at` datetime DEFAULT NULL,
  `diproses_at` datetime DEFAULT NULL,
  `dikirim_at` datetime DEFAULT NULL,
  `selesai_at` datetime DEFAULT NULL,
  `ongkir` int(11) NOT NULL DEFAULT 0,
  `jarak_km` decimal(6,1) DEFAULT NULL,
  `lat` decimal(9,6) DEFAULT NULL,
  `lng` decimal(9,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id`, `user_id`, `order_id`, `nama`, `alamat`, `telepon`, `total`, `tanggal`, `status`, `status_bayar`, `payment_type`, `snap_token`, `kurir`, `no_resi`, `estimasi_tiba`, `dibayar_at`, `diproses_at`, `dikirim_at`, `selesai_at`, `ongkir`, `jarak_km`, `lat`, `lng`) VALUES
(1, 1, 'WB-261013184514-B15F', 'Nadifa Syachdini', 'Jalan Sei Petani, Merdeka, Medan Baru, Kota Medan, Sumatera Utara, 20154, Indonesia', '081345623213', 66500, '2026-10-13 18:45:14', 'diproses', 'paid', 'qris', '46049957-82cc-4380-ae1f-8f6d29797a60', 'GoSend', NULL, '2026-10-17', '2026-10-13 18:51:01', '2026-10-13 18:58:45', NULL, NULL, 16500, 3.3, 3.576020, 98.659894);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan_detail`
--

CREATE TABLE `pesanan_detail` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) DEFAULT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan_detail`
--

INSERT INTO `pesanan_detail` (`id`, `pesanan_id`, `produk_id`, `jumlah`, `harga`) VALUES
(1, 1, 11, 1, 25000),
(2, 1, 16, 1, 25000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `harga` double DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `ketersediaan_stok` enum('habis','tersedia') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `kategori_id`, `nama`, `harga`, `foto`, `detail`, `ketersediaan_stok`) VALUES
(6, 1, 'Pink Fairy', 17000, 'wins_beads_3633521115156483820.jpg', 'Pink Fairy Bracelet adalah gelang cantik dengan perpaduan manik-manik pink pastel dan tali rajut handmade yang halus.', 'tersedia'),
(7, 1, 'Red Fairy', 17000, 'wins_beads_3633521115165076966.jpg', 'Red Fairy Bracelet adalah gelang cantik dengan perpaduan manik-manik pink pastel dan tali rajut handmade yang halus.', 'tersedia'),
(8, 1, 'Brown Fairy', 17000, 'wins_beads_3633521115164868043.jpg', 'Brown Fairy Bracelet adalah gelang cantik dengan perpaduan manik-manik pink pastel dan tali rajut handmade yang halus.', 'tersedia'),
(9, 2, 'Blue My Melody', 22000, 'wins_beads_3574810575572214388.jpg', 'Gelang handmade berwarna biru lembut, dipadukan dengan charm lucu karakter My Melody yang menggemaskan.', 'tersedia'),
(10, 2, 'Pink My Melody', 22000, 'wins_beads_3574810575589028407.jpg', 'Gelang handmade berwarna pink lembut, dipadukan dengan charm lucu karakter My Melody yang menggemaskan.', 'tersedia'),
(11, 2, 'Moana Inspired', 25000, 'wins_beads_3571082649345316327.jpg', 'Gelang cantik bertema warna Moana, memadukan manik-manik biru laut, dan aksen coklat pasir yang terinspirasi dari semangat petualang di lautan.', 'tersedia'),
(12, 3, 'Pinky Green Strap', 27000, 'wins_beads_3571074895226494630.jpg', '', 'tersedia'),
(13, 3, 'Blue Phone Strap', 25000, 'wins_beads_3571075453907881549.jpg', '', 'tersedia'),
(14, 3, 'Pinkeu Ribbon Strap', 28000, 'wins_beads_3571074431671879044.jpg', '', 'tersedia'),
(16, 4, 'Pink Candy', 25000, 'wins_beads_3571071212900017011.jpg', '', 'tersedia');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'nadifasyc', '$2y$10$MP2heCTjWcgX/QztoOpuZux/yGrhFsWyIg/XvXLjrKKcVUZ/vHF3O', 'nadifasyc@gmail.com', '2026-10-13 11:30:31'),
(2, 'carameeals', '$2y$10$YAutfLKTq2YzFihbnoOhNeJ37TM1vVy92Varxes4x/v1BpfLJ.iFG', 'carameeals@gmail.com', '2026-10-13 12:00:10'),
(3, 'user', '$2y$10$a48MtaNK8xRMxfZjLSxy6eQWXxITfCClWWyUCigZTnGMs43bY1hbq', 'user@gmail.com', '2026-10-14 04:16:16');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_admin_username` (`username`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pesan_user` (`user_id`,`id`),
  ADD KEY `fk_pesan_produk` (`produk_id`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_order_id` (`order_id`),
  ADD KEY `idx_pesanan_user` (`user_id`),
  ADD KEY `idx_pesanan_status` (`status`),
  ADD KEY `idx_pesanan_tanggal` (`tanggal`);

--
-- Indeks untuk tabel `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nama` (`nama`),
  ADD KEY `kategori_produk` (`kategori_id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `uniq_user_username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `pesan`
--
ALTER TABLE `pesan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pesan`
--
ALTER TABLE `pesan`
  ADD CONSTRAINT `fk_pesan_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pesan_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  ADD CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesanan_detail_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`),
  ADD CONSTRAINT `pesanan_detail_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `kategori_produk` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
