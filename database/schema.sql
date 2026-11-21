-- Baseline schema exported from local MariaDB on 2026-11-20; no application rows.
-- Intentionally preserves current schema, including the missing otp_verification table.

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cai_dat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dang_ky_de_tai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sinh_vien_id` int(11) NOT NULL,
  `de_tai_id` int(11) NOT NULL,
  `trang_thai` enum('cho_duyet','da_duyet','tu_choi') DEFAULT 'cho_duyet',
  `ly_do_tu_choi` text DEFAULT NULL,
  `ngay_dang_ky` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_duyet` timestamp NULL DEFAULT NULL,
  `nguoi_duyet_id` int(11) DEFAULT NULL,
  `giang_vien_cham_id` int(11) DEFAULT NULL,
  `ngay_phan_cong_cham` timestamp NULL DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_dang_ky` (`sinh_vien_id`,`de_tai_id`),
  KEY `de_tai_id` (`de_tai_id`),
  KEY `nguoi_duyet_id` (`nguoi_duyet_id`),
  KEY `idx_trang_thai` (`trang_thai`),
  KEY `fk_giang_vien_cham` (`giang_vien_cham_id`),
  CONSTRAINT `dang_ky_de_tai_ibfk_1` FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dang_ky_de_tai_ibfk_2` FOREIGN KEY (`de_tai_id`) REFERENCES `de_tai` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dang_ky_de_tai_ibfk_3` FOREIGN KEY (`nguoi_duyet_id`) REFERENCES `nguoi_dung` (`id`),
  CONSTRAINT `fk_giang_vien_cham` FOREIGN KEY (`giang_vien_cham_id`) REFERENCES `giang_vien` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `de_tai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_de_tai` varchar(50) DEFAULT NULL,
  `ten_de_tai` varchar(500) NOT NULL,
  `tieu_de` varchar(500) NOT NULL,
  `mo_ta` text NOT NULL,
  `giang_vien_id` int(11) NOT NULL,
  `he_dao_tao` enum('co_so_nganh','chuyen_nganh') NOT NULL,
  `so_luong_sv` int(11) NOT NULL DEFAULT 1,
  `so_luong_da_dang_ky` int(11) DEFAULT 0,
  `trang_thai` enum('nhap','cho_duyet','da_duyet','tu_choi') DEFAULT 'nhap',
  `ly_do_tu_choi` text DEFAULT NULL,
  `chuyen_nganh` varchar(255) DEFAULT NULL,
  `ngon_ngu_cong_cu` text DEFAULT NULL,
  `cong_nghe` text DEFAULT NULL,
  `yeu_cau_sinh_vien` text DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `nam_hoc` varchar(20) DEFAULT NULL,
  `hoc_ky` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_de_tai` (`ma_de_tai`),
  KEY `giang_vien_id` (`giang_vien_id`),
  KEY `idx_trang_thai` (`trang_thai`),
  KEY `idx_he_dao_tao` (`he_dao_tao`),
  CONSTRAINT `de_tai_ibfk_1` FOREIGN KEY (`giang_vien_id`) REFERENCES `giang_vien` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `giang_vien` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) NOT NULL,
  `ma_giang_vien` varchar(50) NOT NULL,
  `hoc_ham_hoc_vi` varchar(50) DEFAULT NULL,
  `khoa` varchar(255) DEFAULT NULL,
  `chuyen_mon` varchar(255) DEFAULT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `la_lanh_dao` tinyint(4) DEFAULT 0,
  `gioi_han_sv_csn` int(11) DEFAULT 10 COMMENT 'Giới hạn số sinh viên đăng ký đề tài cơ sở ngành',
  `gioi_han_sv_cn` int(11) DEFAULT 10 COMMENT 'Giới hạn số sinh viên đăng ký đề tài chuyên ngành',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nguoi_dung_id` (`nguoi_dung_id`),
  UNIQUE KEY `ma_giang_vien` (`ma_giang_vien`),
  CONSTRAINT `giang_vien_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lanh_dao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) NOT NULL,
  `ma_lanh_dao` varchar(50) NOT NULL,
  `chuc_vu` varchar(255) DEFAULT NULL,
  `khoa` varchar(255) DEFAULT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nguoi_dung_id` (`nguoi_dung_id`),
  UNIQUE KEY `ma_lanh_dao` (`ma_lanh_dao`),
  CONSTRAINT `lanh_dao_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lich_su_duyet_de_tai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `de_tai_id` int(11) NOT NULL,
  `nguoi_duyet_id` int(11) NOT NULL,
  `trang_thai_cu` varchar(50) DEFAULT NULL,
  `trang_thai_moi` varchar(50) DEFAULT NULL,
  `ly_do` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `de_tai_id` (`de_tai_id`),
  KEY `nguoi_duyet_id` (`nguoi_duyet_id`),
  CONSTRAINT `lich_su_duyet_de_tai_ibfk_1` FOREIGN KEY (`de_tai_id`) REFERENCES `de_tai` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lich_su_duyet_de_tai_ibfk_2` FOREIGN KEY (`nguoi_duyet_id`) REFERENCES `nguoi_dung` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(255) NOT NULL,
  `vai_tro_id` int(11) NOT NULL,
  `trang_thai` enum('active','inactive') DEFAULT 'active',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `vai_tro_id` (`vai_tro_id`),
  KEY `idx_reset_token` (`reset_token`),
  CONSTRAINT `nguoi_dung_ibfk_1` FOREIGN KEY (`vai_tro_id`) REFERENCES `vai_tro` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sinh_vien` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) NOT NULL,
  `ma_sinh_vien` varchar(50) NOT NULL,
  `lop` varchar(100) DEFAULT NULL,
  `khoa_hoc` varchar(50) DEFAULT NULL,
  `chuyen_nganh` varchar(255) DEFAULT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nguoi_dung_id` (`nguoi_dung_id`),
  UNIQUE KEY `ma_sinh_vien` (`ma_sinh_vien`),
  CONSTRAINT `sinh_vien_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thong_bao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_nhan_id` int(11) DEFAULT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `noi_dung` text NOT NULL,
  `loai` enum('info','success','warning','danger') DEFAULT 'info',
  `da_doc` tinyint(1) DEFAULT 0,
  `link` varchar(500) DEFAULT NULL,
  `loai_thong_bao` varchar(50) DEFAULT 'thong_bao',
  `ngay_bat_dau` date DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `trang_thai` enum('mo','khoa') NOT NULL DEFAULT 'mo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_da_doc` (`da_doc`),
  KEY `idx_nguoi_nhan` (`nguoi_nhan_id`),
  CONSTRAINT `thong_bao_ibfk_1` FOREIGN KEY (`nguoi_nhan_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thong_bao_do_an_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_title` varchar(500) DEFAULT 'Thông báo đồ án',
  `subtitle` text DEFAULT NULL,
  `date_badge` varchar(100) DEFAULT NULL,
  `trang_thai` enum('mo','khoa') NOT NULL DEFAULT 'mo',
  `ngay_bat_dau` date DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thong_bao_do_an_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tieu_de` varchar(500) NOT NULL,
  `noi_dung` text DEFAULT NULL,
  `trang_thai` enum('mo','khoa') NOT NULL DEFAULT 'mo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thong_bao_do_an_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loai` varchar(50) NOT NULL DEFAULT 'dang_ky',
  `thu_tu` int(11) NOT NULL DEFAULT 1,
  `tieu_de` varchar(500) NOT NULL,
  `noi_dung` text DEFAULT NULL,
  `ngay` int(11) DEFAULT NULL,
  `thang` int(11) DEFAULT NULL,
  `nam` int(11) DEFAULT NULL,
  `yeu_cau` text DEFAULT NULL,
  `trang_thai` enum('mo','khoa') NOT NULL DEFAULT 'mo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vai_tro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_vai_tro` varchar(50) NOT NULL,
  `ten_vai_tro` varchar(100) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_vai_tro` (`ma_vai_tro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

CREATE TABLE IF NOT EXISTS de_tai_quan_tam (
    id INT NOT NULL AUTO_INCREMENT,
    sinh_vien_id INT NOT NULL,
    de_tai_id INT NULL,
    tieu_de_da_luu VARCHAR(500) NOT NULL,
    he_dao_tao_da_luu ENUM('co_so_nganh','chuyen_nganh') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_quan_tam_sinh_vien_de_tai (sinh_vien_id, de_tai_id),
    KEY idx_quan_tam_de_tai (de_tai_id),
    CONSTRAINT fk_quan_tam_sinh_vien FOREIGN KEY (sinh_vien_id) REFERENCES sinh_vien(id) ON DELETE CASCADE,
    CONSTRAINT fk_quan_tam_de_tai FOREIGN KEY (de_tai_id) REFERENCES de_tai(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
