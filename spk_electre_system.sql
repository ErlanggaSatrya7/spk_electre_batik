-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 22, 2025 at 11:35 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spk_electre_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `alternatif`
--

CREATE TABLE `alternatif` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `asal_daerah` varchar(100) DEFAULT NULL,
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `alternatif`
--

INSERT INTO `alternatif` (`id`, `nama`, `kategori`, `asal_daerah`, `deskripsi`, `created_at`) VALUES
(45, 'Batik Reza', '', '', '', '2025-06-22 07:42:57'),
(46, 'Batik Khotimah', '', '', '', '2025-06-22 07:43:05'),
(47, 'Batik Rossalinda', '', '', '', '2025-06-22 07:43:27'),
(48, 'Batik Adeline', '', '', '', '2025-06-22 07:43:38'),
(49, 'Batik Marina', '', '', '', '2025-06-22 07:43:50'),
(50, 'Batik Satria', '', '', '', '2025-06-22 07:43:59'),
(51, 'Batik Hanawati', '', '', '', '2025-06-22 07:44:09'),
(52, 'Batik Sriamah', '', '', '', '2025-06-22 07:44:15'),
(53, 'Batik Kahiyang', '', '', '', '2025-06-22 07:44:21'),
(54, 'Batik Tresna', '', '', '', '2025-06-22 07:44:31');

-- --------------------------------------------------------

--
-- Table structure for table `gambar_alternatif`
--

CREATE TABLE `gambar_alternatif` (
  `id` int NOT NULL,
  `alternatif_id` int NOT NULL,
  `filename` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasil_ranking`
--

CREATE TABLE `hasil_ranking` (
  `id` int NOT NULL,
  `id_alternatif` int DEFAULT NULL,
  `nilai_akhir` float DEFAULT NULL,
  `ranking` int DEFAULT NULL,
  `tanggal_proses` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kriteria`
--

CREATE TABLE `kriteria` (
  `id` int NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `bobot` float NOT NULL,
  `tipe` enum('benefit','cost') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kriteria`
--

INSERT INTO `kriteria` (`id`, `kode`, `nama`, `bobot`, `tipe`, `created_at`) VALUES
(19, 'C5', 'Surat Izin', 0.07, 'benefit', '2025-06-22 02:17:33'),
(20, 'C4', 'Nilai Investasi', 0.09, 'cost', '2025-06-22 02:19:16'),
(21, 'C3', 'Nilai Produksi', 0.19, 'benefit', '2025-06-22 02:19:25'),
(22, 'C2', 'Kapasitas Produksi', 0.26, 'benefit', '2025-06-22 02:19:38'),
(23, 'C1', 'Jumlah Pekerja', 0.39, 'benefit', '2025-06-22 02:28:44');

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `aktivitas` text,
  `waktu` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_pengaturan`
--

CREATE TABLE `log_pengaturan` (
  `id` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `isi_lama` text,
  `waktu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `log_pengaturan`
--

INSERT INTO `log_pengaturan` (`id`, `id_user`, `isi_lama`, `waktu`) VALUES
(1, NULL, '{\"id\":1,\"nama_aplikasi\":\"SPK Batik - ELECTRE\",\"deskripsi\":\"Digunakan untuk membantu memilih batik terbaik berdasarkan beberapa kriteria.\",\"versi\":\"1.0\",\"logo\":null,\"maintenance\":0}', '2025-06-21 15:04:31'),
(2, NULL, '{\"id\":1,\"nama_aplikasi\":\"SPK Batik - ELECTRE\",\"deskripsi\":\"Digunakan untuk membantu memilih batik terbaik berdasarkan beberapa kriteria.\",\"versi\":\"1.0\",\"logo\":null,\"maintenance\":0}', '2025-06-21 15:04:39'),
(3, NULL, '{\"id\":1,\"nama_aplikasi\":\"SPK Batik - ELECTR\",\"deskripsi\":\"Digunakan untuk membantu memilih batik terbaik berdasarkan beberapa kriteria.\",\"versi\":\"1.0\",\"logo\":null,\"maintenance\":0}', '2025-06-21 15:04:42'),
(4, NULL, '{\"id\":1,\"nama_aplikasi\":\"SPK Batik - ELECTR\",\"deskripsi\":\"Digunakan untuk membantu memilih batik terbaik berdasarkan beberapa kriteria.\",\"versi\":\"1.0\",\"logo\":null,\"maintenance\":0}', '2025-06-21 15:04:43'),
(5, NULL, '{\"id\":1,\"nama_aplikasi\":\"SPK Batik - ELECTR\",\"deskripsi\":\"Digunakan untuk membantu memilih batik terbaik berdasarkan beberapa kriteria.\",\"versi\":\"1.0\",\"logo\":null,\"maintenance\":0}', '2025-06-21 15:04:43'),
(6, NULL, '{\"id\":1,\"nama_aplikasi\":\"SPK Batik - ELECTR\",\"deskripsi\":\"Digunakan untuk membantu memilih batik terbaik berdasarkan beberapa kriteria.\",\"versi\":\"1.0\",\"logo\":null,\"maintenance\":0}', '2025-06-21 15:04:43'),
(7, NULL, '{\"id\":1,\"nama_aplikasi\":\"SPK Batik - ELECTR\",\"deskripsi\":\"Digunakan untuk membantu memilih batik terbaik berdasarkan beberapa kriteria.\",\"versi\":\"1.0\",\"logo\":null,\"maintenance\":0}', '2025-06-21 15:04:48'),
(8, NULL, '{\"id\":1,\"nama_aplikasi\":\"SPK Batik - ELECTR\",\"deskripsi\":\"Digunakan untuk membantu memilih batik terbaik berdasarkan beberapa kriteria.\",\"versi\":\"1.0\",\"logo\":\"logo_1750493088.jpg\",\"maintenance\":0}', '2025-06-21 15:04:54'),
(9, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":null,\"maintenance\":0}', '2025-06-21 15:05:32'),
(10, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":null,\"maintenance\":0,\"slogan\":null,\"theme_color\":null,\"background_image\":null,\"footer_text\":null}', '2025-06-22 15:22:53'),
(11, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":\"logo_1750580573.jpg\",\"maintenance\":0,\"slogan\":null,\"theme_color\":null,\"background_image\":null,\"footer_text\":null}', '2025-06-22 15:23:05'),
(12, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":\"logo_1750580585.jpg\",\"maintenance\":0,\"slogan\":null,\"theme_color\":null,\"background_image\":null,\"footer_text\":null}', '2025-06-22 15:24:00'),
(13, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":\"logo_1750580640.png\",\"maintenance\":0,\"slogan\":null,\"theme_color\":null,\"background_image\":null,\"footer_text\":null}', '2025-06-22 15:24:39'),
(14, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":\"logo_1750580679.jpg\",\"maintenance\":0,\"slogan\":null,\"theme_color\":null,\"background_image\":null,\"footer_text\":null}', '2025-06-22 15:24:58'),
(15, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTR\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":\"logo_1750580679.jpg\",\"maintenance\":0,\"slogan\":null,\"theme_color\":null,\"background_image\":null,\"footer_text\":null}', '2025-06-22 15:25:31'),
(16, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":\"logo_1750580679.jpg\",\"maintenance\":0,\"slogan\":null,\"theme_color\":null,\"background_image\":null,\"footer_text\":null}', '2025-06-22 15:26:32'),
(17, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":\"logo_1750580679.jpg\",\"maintenance\":1,\"slogan\":null,\"theme_color\":null,\"background_image\":null,\"footer_text\":null}', '2025-06-22 15:30:00'),
(18, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":null,\"maintenance\":0,\"background_image\":null,\"footer_text\":null,\"login_judul\":\"Kontrol Penuh. Keamanan Maksimal.\",\"login_deskripsi\":\"Panel admin ini dirancang untuk performa dan keamanan dalam mengelola sistem SPK berbasis metode ELECTRE.\"}', '2025-06-22 16:07:12'),
(19, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":null,\"maintenance\":0,\"background_image\":null,\"footer_text\":null,\"login_judul\":\"Kontrol Penuh. Keamanan Maksimal.\",\"login_deskripsi\":\"Panel admin ini dirancang untuk performa dan keamanan dalam mengelola sistem SPK berbasis metode ELECTRE.\"}', '2025-06-22 18:30:17'),
(20, NULL, '{\"id\":1,\"nama_aplikasi\":\"Sistem SPK ELECTRE\",\"deskripsi\":\"Aplikasi SPK berbasis metode ELECTRE\",\"versi\":\"1.0.0\",\"logo\":null,\"maintenance\":0,\"background_image\":null,\"footer_text\":null,\"login_judul\":\"Kontrol Penuh. Keamanan Maksimal.\",\"login_deskripsi\":\"Panel admin ini dirancang untuk performa dan keamanan dalam mengelola sistem SPK berbasis metode ELECTRE.\"}', '2025-06-22 18:30:28');

-- --------------------------------------------------------

--
-- Table structure for table `nilai`
--

CREATE TABLE `nilai` (
  `id` int NOT NULL,
  `id_alternatif` int DEFAULT NULL,
  `id_kriteria` int DEFAULT NULL,
  `nilai` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nilai`
--

INSERT INTO `nilai` (`id`, `id_alternatif`, `id_kriteria`, `nilai`) VALUES
(82, 45, 19, 0),
(83, 45, 20, 4500),
(84, 45, 21, 5250000),
(85, 45, 22, 150),
(86, 45, 23, 1),
(87, 46, 19, 0),
(88, 46, 20, 4500),
(89, 46, 21, 5250000),
(90, 46, 22, 150),
(91, 46, 23, 1),
(92, 47, 19, 0),
(93, 47, 20, 4500),
(94, 47, 21, 5250000),
(95, 47, 22, 150),
(96, 47, 23, 1),
(97, 48, 19, 0),
(98, 48, 20, 4500),
(99, 48, 21, 5250000),
(100, 48, 22, 150),
(101, 48, 23, 3),
(102, 49, 19, 1),
(103, 49, 20, 4250),
(104, 49, 21, 3500000),
(105, 49, 22, 100),
(106, 49, 23, 2),
(107, 50, 19, 0),
(108, 50, 20, 4500),
(109, 50, 21, 5250000),
(110, 50, 22, 150),
(111, 50, 23, 2),
(112, 51, 19, 0),
(113, 51, 20, 4500),
(114, 51, 21, 5250000),
(115, 51, 22, 150),
(116, 51, 23, 2),
(117, 52, 19, 0),
(118, 52, 20, 4500),
(119, 52, 21, 3500000),
(120, 52, 22, 100),
(121, 52, 23, 2),
(122, 53, 19, 0),
(123, 53, 20, 4500),
(124, 53, 21, 5250000),
(125, 53, 22, 150),
(126, 53, 23, 1),
(127, 54, 19, 0),
(128, 54, 20, 4250),
(129, 54, 21, 5250000),
(130, 54, 22, 150),
(131, 54, 23, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int NOT NULL,
  `nama_aplikasi` varchar(100) DEFAULT NULL,
  `deskripsi` text,
  `versi` varchar(10) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `maintenance` tinyint(1) DEFAULT '0',
  `background_image` varchar(255) DEFAULT NULL,
  `footer_text` text,
  `login_judul` varchar(255) DEFAULT 'Kontrol Penuh. Keamanan Maksimal.',
  `login_deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_aplikasi`, `deskripsi`, `versi`, `logo`, `maintenance`, `background_image`, `footer_text`, `login_judul`, `login_deskripsi`) VALUES
(1, 'Sistem SPK ELECTRE', 'Aplikasi SPK berbasis metode ELECTRE', '1.0.0', NULL, 0, NULL, NULL, 'Kontrol Penuh. Keamanan Maksimal.', 'Panel admin ini dirancang untuk performa dan keamanan dalam mengelola sistem SPK berbasis metode ELECTRE.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `PASSWORD` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `PASSWORD`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alternatif`
--
ALTER TABLE `alternatif`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gambar_alternatif`
--
ALTER TABLE `gambar_alternatif`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alternatif_id` (`alternatif_id`);

--
-- Indexes for table `hasil_ranking`
--
ALTER TABLE `hasil_ranking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hasil_ranking_ibfk_1` (`id_alternatif`);

--
-- Indexes for table `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `log_pengaturan`
--
ALTER TABLE `log_pengaturan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `nilai`
--
ALTER TABLE `nilai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unik_alternatif_kriteria` (`id_alternatif`,`id_kriteria`),
  ADD KEY `id_kriteria` (`id_kriteria`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alternatif`
--
ALTER TABLE `alternatif`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `gambar_alternatif`
--
ALTER TABLE `gambar_alternatif`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `hasil_ranking`
--
ALTER TABLE `hasil_ranking`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_pengaturan`
--
ALTER TABLE `log_pengaturan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `nilai`
--
ALTER TABLE `nilai`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gambar_alternatif`
--
ALTER TABLE `gambar_alternatif`
  ADD CONSTRAINT `gambar_alternatif_ibfk_1` FOREIGN KEY (`alternatif_id`) REFERENCES `alternatif` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hasil_ranking`
--
ALTER TABLE `hasil_ranking`
  ADD CONSTRAINT `hasil_ranking_ibfk_1` FOREIGN KEY (`id_alternatif`) REFERENCES `alternatif` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);

--
-- Constraints for table `log_pengaturan`
--
ALTER TABLE `log_pengaturan`
  ADD CONSTRAINT `log_pengaturan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);

--
-- Constraints for table `nilai`
--
ALTER TABLE `nilai`
  ADD CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`id_alternatif`) REFERENCES `alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_ibfk_2` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
