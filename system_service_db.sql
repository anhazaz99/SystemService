-- --------------------------------------------------------
-- Máy chủ:                      127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Phiên bản:           12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for system_services
CREATE DATABASE IF NOT EXISTS `system_services` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `system_services`;

-- Dumping structure for table system_services.calendar
CREATE TABLE IF NOT EXISTS `calendar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tieu_de` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` text COLLATE utf8mb4_unicode_ci,
  `thoi_gian_bat_dau` datetime NOT NULL,
  `thoi_gian_ket_thuc` datetime NOT NULL,
  `loai_su_kien` enum('task','su_kien') COLLATE utf8mb4_unicode_ci NOT NULL,
  `task_id` bigint unsigned DEFAULT NULL,
  `nguoi_tham_gia_id` bigint unsigned NOT NULL,
  `loai_nguoi_tham_gia` enum('giang_vien','sinh_vien') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nguoi_tao_id` bigint unsigned NOT NULL,
  `loai_nguoi_tao` enum('giang_vien','sinh_vien') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `calendar_loai_nguoi_tham_gia_nguoi_tham_gia_id_index` (`loai_nguoi_tham_gia`,`nguoi_tham_gia_id`),
  KEY `calendar_thoi_gian_bat_dau_index` (`thoi_gian_bat_dau`),
  KEY `calendar_task_id_index` (`task_id`),
  CONSTRAINT `calendar_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table system_services.don_vi
CREATE TABLE IF NOT EXISTS `don_vi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ten` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `loai` enum('truong','khoa','to') COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `don_vi_parent_id_foreign` (`parent_id`),
  CONSTRAINT `don_vi_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `don_vi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table system_services.giang_vien
CREATE TABLE IF NOT EXISTS `giang_vien` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ho_ten` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gioi_tinh` enum('Nam','Nữ','Khác') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dia_chi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sdt` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ma_giao_vien` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `don_vi_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `giang_vien_email_unique` (`email`),
  UNIQUE KEY `giang_vien_ma_giao_vien_unique` (`ma_giao_vien`),
  KEY `giang_vien_don_vi_id_foreign` (`don_vi_id`),
  CONSTRAINT `giang_vien_don_vi_id_foreign` FOREIGN KEY (`don_vi_id`) REFERENCES `don_vi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table system_services.giang_vien_account
CREATE TABLE IF NOT EXISTS `giang_vien_account` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `giang_vien_id` bigint unsigned NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `giang_vien_account_username_unique` (`username`),
  KEY `giang_vien_account_giang_vien_id_index` (`giang_vien_id`),
  CONSTRAINT `giang_vien_account_giang_vien_id_foreign` FOREIGN KEY (`giang_vien_id`) REFERENCES `giang_vien` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table system_services.lop
CREATE TABLE IF NOT EXISTS `lop` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ten_lop` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ma_lop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `khoa_id` bigint unsigned NOT NULL,
  `giang_vien_id` bigint unsigned DEFAULT NULL,
  `nam_hoc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lop_ma_lop_unique` (`ma_lop`),
  KEY `lop_khoa_id_foreign` (`khoa_id`),
  KEY `lop_giang_vien_id_foreign` (`giang_vien_id`),
  CONSTRAINT `lop_giang_vien_id_foreign` FOREIGN KEY (`giang_vien_id`) REFERENCES `giang_vien` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lop_khoa_id_foreign` FOREIGN KEY (`khoa_id`) REFERENCES `don_vi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table system_services.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table system_services.sinh_vien
CREATE TABLE IF NOT EXISTS `sinh_vien` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ho_ten` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` enum('Nam','Nữ','Khác') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dia_chi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sdt` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ma_sinh_vien` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lop_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sinh_vien_email_unique` (`email`),
  UNIQUE KEY `sinh_vien_ma_sinh_vien_unique` (`ma_sinh_vien`),
  KEY `sinh_vien_lop_id_foreign` (`lop_id`),
  CONSTRAINT `sinh_vien_lop_id_foreign` FOREIGN KEY (`lop_id`) REFERENCES `lop` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table system_services.sinh_vien_account
CREATE TABLE IF NOT EXISTS `sinh_vien_account` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sinh_vien_id` bigint unsigned NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sinh_vien_account_username_unique` (`username`),
  KEY `sinh_vien_account_sinh_vien_id_index` (`sinh_vien_id`),
  CONSTRAINT `sinh_vien_account_sinh_vien_id_foreign` FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table system_services.task
CREATE TABLE IF NOT EXISTS `task` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tieu_de` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` text COLLATE utf8mb4_unicode_ci,
  `ngay_tao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nguoi_nhan_id` bigint unsigned NOT NULL,
  `loai_nguoi_nhan` enum('giang_vien','sinh_vien') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nguoi_tao_id` bigint unsigned NOT NULL,
  `loai_nguoi_tao` enum('giang_vien','sinh_vien') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task_loai_nguoi_nhan_nguoi_nhan_id_index` (`loai_nguoi_nhan`,`nguoi_nhan_id`),
  KEY `task_loai_nguoi_tao_nguoi_tao_id_index` (`loai_nguoi_tao`,`nguoi_tao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table system_services.task_file
CREATE TABLE IF NOT EXISTS `task_file` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task_file_task_id_index` (`task_id`),
  CONSTRAINT `task_file_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
