-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: sidisiplin
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `absensi`
--

DROP TABLE IF EXISTS `absensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `absensi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nip` bigint unsigned NOT NULL,
  `periode` date NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_pulang` time NOT NULL,
  `status` enum('Tanpa Keterangan','Terlambat','Pulang Duluan') NOT NULL,
  `alasan` text,
  `durasi_menit` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `absensi_periode_index` (`periode`),
  KEY `absensi_nip_periode_index` (`nip`,`periode`),
  CONSTRAINT `absensi_nip_foreign` FOREIGN KEY (`nip`) REFERENCES `pegawai` (`nip`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi`
--

LOCK TABLES `absensi` WRITE;
/*!40000 ALTER TABLE `absensi` DISABLE KEYS */;
INSERT INTO `absensi` VALUES (6,198503152010121001,'2026-11-02','08:35:00','16:00:00','Terlambat','Macet di Jalan A. Yani KM 6',35,'2026-11-04 07:38:04','2026-11-04 07:38:04'),(7,198503152010121001,'2026-11-03','08:00:00','15:15:00','Pulang Duluan','Ada keperluan keluarga mendesak',45,'2026-11-04 07:38:04','2026-11-04 07:38:04'),(8,199008222015032002,'2026-11-03','08:00:00','16:00:00','Tanpa Keterangan','Tidak hadir tanpa pemberitahuan',450,'2026-11-04 07:38:04','2026-11-04 07:38:04'),(9,198811052012121003,'2026-11-04','08:50:00','15:30:00','Terlambat','Ban bocor dan izin pulang lebih awal',80,'2026-11-04 07:38:04','2026-11-04 07:38:04'),(10,199401152021031001,'2026-11-05','08:00:00','14:00:00','Pulang Duluan','pengen pulang',120,'2026-11-05 06:15:29','2026-11-05 06:15:43'),(11,198503152010121001,'2026-11-02','08:35:00','16:00:00','Terlambat','Macet di Jalan A. Yani KM 6',35,'2026-11-09 06:22:07','2026-11-09 06:22:07'),(12,198503152010121001,'2026-11-03','08:00:00','15:15:00','Pulang Duluan','Ada keperluan keluarga mendesak',45,'2026-11-09 06:22:07','2026-11-09 06:22:07'),(13,199008222015032002,'2026-11-03','08:00:00','16:00:00','Tanpa Keterangan','Tidak hadir tanpa pemberitahuan',450,'2026-11-09 06:22:07','2026-11-09 06:22:07'),(14,198811052012121003,'2026-11-04','08:50:00','15:30:00','Terlambat','Ban bocor dan izin pulang lebih awal',80,'2026-11-09 06:22:07','2026-11-09 06:22:07');
/*!40000 ALTER TABLE `absensi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `divisi_bagian`
--

DROP TABLE IF EXISTS `divisi_bagian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `divisi_bagian` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `kode` varchar(50) DEFAULT NULL,
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `divisi_bagian_nama_unique` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `divisi_bagian`
--

LOCK TABLES `divisi_bagian` WRITE;
/*!40000 ALTER TABLE `divisi_bagian` DISABLE KEYS */;
INSERT INTO `divisi_bagian` VALUES (1,'Pelayanan Hukum','YANKUM','Divisi Pelayanan Hukum','2026-11-09 06:22:07','2026-11-09 06:22:07'),(2,'P3H','P3H','Divisi Peraturan Perundang-undangan dan Pembudayaan Hukum','2026-11-09 06:22:07','2026-11-09 06:22:07'),(3,'Tata Usaha dan Umum','TU_UMUM','Bagian Tata Usaha dan Umum','2026-11-09 06:22:07','2026-11-09 06:22:07');
/*!40000 ALTER TABLE `divisi_bagian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_11_04_000001_create_pegawai_table',1),(5,'2026_11_04_000002_create_absensi_table',1),(6,'2026_11_04_000003_create_pengaturan_table',1),(7,'2026_11_04_142844_create_personal_access_tokens_table',1),(8,'2026_11_04_000000_create_divisi_bagian_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pegawai`
--

DROP TABLE IF EXISTS `pegawai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pegawai` (
  `nip` bigint unsigned NOT NULL,
  `nama` varchar(255) NOT NULL,
  `divisi` enum('Pelayanan Hukum','P3H','Tata Usaha dan Umum','Kekayaan Intelektual','Administrasi Hukum Umum','Humas','SDM','Perencanaan','Analis Hukum') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`nip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pegawai`
--

LOCK TABLES `pegawai` WRITE;
/*!40000 ALTER TABLE `pegawai` DISABLE KEYS */;
INSERT INTO `pegawai` VALUES (123456789012345689,'kk','P3H','2026-11-05 00:50:02','2026-11-05 00:50:02'),(198503152010121001,'Ahmad Fauzi, S.H.','Pelayanan Hukum','2026-11-04 07:38:04','2026-11-04 07:38:04'),(198602102009031007,'Hendro Prasetyo, S.H.','P3H','2026-11-04 07:38:04','2026-11-04 07:38:04'),(198706302011011005,'Muhammad Rizky, S.E.','Perencanaan','2026-11-04 07:38:04','2026-11-04 07:38:04'),(198811052012121003,'Budi Santoso, S.Kom.','Tata Usaha dan Umum','2026-11-04 07:38:04','2026-11-04 07:38:04'),(198907122013011009,'Ferry Gunawan, S.H.','Analis Hukum','2026-11-04 07:38:04','2026-11-04 07:38:04'),(199008222015032002,'Siti Rahmawati, S.Sos.','Humas','2026-11-04 07:38:04','2026-11-04 07:38:04'),(199105252014022008,'Kartika Sari, S.H.','Administrasi Hukum Umum','2026-11-04 07:38:04','2026-11-04 07:38:04'),(199204182019012004,'Dewi Anggraini, S.H., M.H.','Kekayaan Intelektual','2026-11-04 07:38:04','2026-11-04 07:38:04'),(199309142020121006,'Nurul Hidayah, S.Tr.A.P.','SDM','2026-11-04 07:38:04','2026-11-04 07:38:04'),(199401152021031001,'Bambang Wijaya, S.H.','Pelayanan Hukum','2026-11-05 05:19:42','2026-11-05 05:19:42'),(199505202022022002,'Rina Kusuma, S.E.','Perencanaan','2026-11-05 05:19:42','2026-11-05 05:19:42'),(199612102023011003,'Dedi Saputra, S.Kom.','Tata Usaha dan Umum','2026-11-05 05:19:42','2026-11-05 05:19:42');
/*!40000 ALTER TABLE `pegawai` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan`
--

DROP TABLE IF EXISTS `pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text,
  `deskripsi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengaturan_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan`
--

LOCK TABLES `pengaturan` WRITE;
/*!40000 ALTER TABLE `pengaturan` DISABLE KEYS */;
INSERT INTO `pengaturan` VALUES (1,'jam_masuk_standar','07:30','Jam Masuk Standar','2026-11-04 07:38:04','2026-11-09 06:22:07'),(2,'jam_pulang_standar','16:00','Jam Pulang Standar','2026-11-04 07:38:04','2026-11-04 07:38:04'),(3,'senin_masuk','07:30','Jam Masuk Senin','2026-11-09 06:22:07','2026-11-09 06:22:07'),(4,'senin_pulang','16:00','Jam Pulang Senin','2026-11-09 06:22:07','2026-11-09 06:22:07'),(5,'senin_flex','0','Flex Senin','2026-11-09 06:22:07','2026-11-09 06:22:07'),(6,'selasa_kamis_masuk','07:30','Jam Masuk Selasa-Kamis','2026-11-09 06:22:07','2026-11-09 06:22:07'),(7,'selasa_kamis_pulang','16:00','Jam Pulang Selasa-Kamis','2026-11-09 06:22:07','2026-11-09 06:22:07'),(8,'selasa_kamis_flex','1','Flex Selasa-Kamis','2026-11-09 06:22:07','2026-11-09 06:22:07'),(9,'jumat_masuk','07:30','Jam Masuk Jumat','2026-11-09 06:22:07','2026-11-09 06:22:07'),(10,'jumat_pulang','16:30','Jam Pulang Jumat','2026-11-09 06:22:07','2026-11-09 06:22:07'),(11,'jumat_flex','0','Flex Jumat','2026-11-09 06:22:07','2026-11-09 06:22:07');
/*!40000 ALTER TABLE `pengaturan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (13,'App\\Models\\User',14,'admin_auth_token','53f923b44ece0d70cb0af8190cb6f200afdab7ad6e1e52e2c10c614073c1ccf9','[\"*\"]','2026-11-09 06:14:20',NULL,'2026-11-05 06:14:42','2026-11-09 06:14:20');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('2Q0yTMOvvNJ5QNJOcI5edWDlfAPWR3gyAOVI51QV',NULL,'127.0.0.1','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNTNUTE9iQWtnSWJrNmZ5SEI1MG9PVTM1Z3B6ZWhJVlU0dGVPaENlRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793847123),('7MzcVLGvW2WDg7DDpdq4E3GpiAobjw1b32zhLSTt',NULL,'127.0.0.1','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidmNPTExueDNWZDVqc1Z6R1VpSnZibUM1Q25DTHhzbXdESXFMVWJjdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793849262),('b4DTAzwJ0BjCCRM9IMPequ8T1ERYlg4zkCN3WnBn',NULL,'127.0.0.1','curl/8.21.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoid250Z0hHNzJROWhZTWowbFB2YzdzRE94eHFmeE1POXlnc0hsVHZhQyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793848188),('daEbhUbtgQOhc8s5UvghqRkI2OgYFPu0bEaNIQfi',NULL,'127.0.0.1','curl/8.21.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiQ2xITHhRa2N5VHZjb05iYzJubmVSYjM2ZWFGeTB6RUEwc0lzQ1RLeCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793843287),('fCkfsrwXDyaPSFdIEl33ezpWh27pPxCnyogdklCb',NULL,'127.0.0.1','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicHNkSkpxekd5TGpnVVZwZWo5ZGtmUlljR1JKMXZJTFhteEdiREhwNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793841996),('HpGWDloHHabaTUqC8Q6KZz0oFlkdXiNalANov8vM',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWGlvbWFHSjhmek5hYjA3VE80YWx3N24yU3pBUkwxU2JBM2d0YkVBVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793859479),('HXi5At4l5LmAp7dMxJJyaWE3cM7R5BBI9RRcaeu4',NULL,'127.0.0.1','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYVdHamRhc2dQcDFsZlU1Nm5UZFJmUnVBMVJtcEpYZGVnN24wV2hNTSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793841819),('JbC3tGfmC64nL8PQeJENKOpW2RCmUV6RVsIRyaiO',NULL,'127.0.0.1','curl/8.21.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiN05nM2IyaFR1MDVwRUlhVDBiMlJ4WndDZGhHcG5LRE9GUWNjSXMzZiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793843461),('jU1mNEZv8kUV7DR2hTz5PamAeaR0rrEs2r7pYfnS',NULL,'127.0.0.1','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZGRwd0FiRHhvWUdxNFFaOTkzbEhFN002Q1Y4TlV3RXhyM3hWNTBMeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793845493),('LbKU2n9zcWlC5iXyyPWhT20oHZvsdr9Z2PcUdJ1z',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNzNhZ1o3WWZFZllJZGttemt5SkV1bHZ3eUhleGQ3Z3UyeDlsZDNsSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793777642),('PoSzO268juFt0eONTSnmbRCuv5VJaqPumNYCMNzF',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY0lvZjVvZThVTTVka3VkajVsQmR2MmVpVDI2T294cTVyYW55M21HbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1794204802),('RZNVFp3auFmIKima9uoj8WofT25U9LnBzf8yWOvp',NULL,'127.0.0.1','curl/8.21.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiR21qeU1uRkgwZk0zbGx5eWN1TjM1TkJrbElJMnhjOThGMGR0NFNYcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793842043),('tx7Tbo66GrJFN3k51aiJqSg0jd5De7mTXsBTm0Jf',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9711','YTozOntzOjY6Il90b2tlbiI7czo0MDoiazk3bHRYd3cxZElYaUtPVWtrQ1praHk1bXhQdlZJc2ZqMWhnVDMyRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793777923),('u7l5QFZJC6MZcMbcP9wHYxqkNPbuAP3eCfsgyTOO',NULL,'127.0.0.1','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUVl3R2t5NEFLSG5EZm1hZmJZTnQyOHRyOXhxMGJMUTBZbXhHT0FGMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793854756),('Vf1eZt7XEOI8y692HnM9OisrZsNXTNI8UDDD4jiz',NULL,'127.0.0.1','curl/8.21.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidWFzdjd0MEpINXFNYmlaT1pvZEo0UTNnQ21BSFBGUUJIb25lNXA1biI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1793843246),('XKcqVBUEDkwDcOupXeinqIsL4J46O5Vv6Z4Vna5h',NULL,'127.0.0.1','curl/8.21.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVkxwTjRKOXhVRlJSTXVjZGgzRjdjY3c4bzdDSW42NXFwY3VuZjBZWCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793840617),('XtRd4MXxZcS9CEeKOBWQz3sxa5KvgWYWbMu2p2KH',NULL,'127.0.0.1','curl/8.21.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiTmRaWEFYMGZrcGJLSjJCZG9ueEFBTDFFU2tCQnlqMHJ2WU4wUXBndSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1793844493);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT 'Administrator',
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (14,'Administrator SIDISIPLIN','admin','$2y$12$3IpPaKtbVgz4ZlZdSniGAuCzo8WPCn4gUowvMQSa/bNavm5H9wPMu',NULL,'2026-11-04 07:37:34','2026-11-04 07:37:50');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'sidisiplin'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 14:22:21
