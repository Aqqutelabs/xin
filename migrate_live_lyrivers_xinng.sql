-- SUPERSEDED FOR LIVE CPANEL IMPORT: do not import this older file.
-- It contains DROP TABLE statements and does not include the merged source data.
-- Use the private Downloads/lyrivers_xinng_merged_one_import.sql instead;
-- see docs/live-database-merge-cpanel.md for its checksum and validation steps.
-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: lyrivers_xinng
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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

--
-- Current Database: `lyrivers_xinng`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `lyrivers_xinng` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `lyrivers_xinng`;

--
-- Table structure for table `admin_activity`
--

DROP TABLE IF EXISTS `admin_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_activity` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_user_id` bigint(20) unsigned NOT NULL,
  `event_type` varchar(64) NOT NULL,
  `target_type` varchar(32) DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_admin_activity_actor` (`actor_user_id`,`created_at`),
  KEY `idx_admin_activity_target` (`target_type`,`target_id`),
  CONSTRAINT `fk_admin_activity_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_activity`
--

LOCK TABLES `admin_activity` WRITE;
/*!40000 ALTER TABLE `admin_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_events`
--

DROP TABLE IF EXISTS `auth_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event_type` varchar(64) NOT NULL,
  `outcome` varchar(32) NOT NULL,
  `identifier_hash` char(64) DEFAULT NULL,
  `ip_hash` char(64) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_auth_events_identifier` (`event_type`,`identifier_hash`,`created_at`),
  KEY `idx_auth_events_ip` (`event_type`,`ip_hash`,`created_at`),
  KEY `idx_auth_events_user` (`user_id`,`created_at`),
  CONSTRAINT `fk_auth_events_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_events`
--

LOCK TABLES `auth_events` WRITE;
/*!40000 ALTER TABLE `auth_events` DISABLE KEYS */;
INSERT INTO `auth_events` VALUES (1,NULL,'auth_test','failed','6c46cc2953c7a8b239c1d5eb96bf2a2423df1882635d94b708461d8b050ec5ec',NULL,NULL,'{\"test\":true}','2026-10-18 11:14:02'),(2,NULL,'auth_test','failed','6c46cc2953c7a8b239c1d5eb96bf2a2423df1882635d94b708461d8b050ec5ec',NULL,NULL,'{\"test\":true}','2026-10-18 11:14:02'),(3,NULL,'auth_test','failed','6c46cc2953c7a8b239c1d5eb96bf2a2423df1882635d94b708461d8b050ec5ec',NULL,NULL,'{\"test\":true}','2026-10-18 11:14:02'),(4,NULL,'auth_test','failed','6c46cc2953c7a8b239c1d5eb96bf2a2423df1882635d94b708461d8b050ec5ec',NULL,NULL,'{\"test\":true}','2026-10-18 11:14:02'),(17,1,'signin','succeeded','1acad70aae334797f117b9b36160bfdb7508a2161880b48a4dc82f7237986d69','eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36',NULL,'2026-10-18 11:50:28'),(30,1,'signin','succeeded','1acad70aae334797f117b9b36160bfdb7508a2161880b48a4dc82f7237986d69','eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36',NULL,'2026-10-21 05:29:32');
/*!40000 ALTER TABLE `auth_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_transfer_receipts`
--

DROP TABLE IF EXISTS `bank_transfer_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_transfer_receipts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `credit_transaction_id` bigint(20) unsigned NOT NULL,
  `package_id` varchar(40) NOT NULL,
  `expected_amount` int(11) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'NGN',
  `receipt_path` varchar(255) NOT NULL,
  `original_filename` varchar(190) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `review_note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_bank_transfer_receipts_transaction` (`credit_transaction_id`),
  KEY `idx_bank_transfer_receipts_user` (`user_id`),
  KEY `idx_bank_transfer_receipts_status` (`status`),
  CONSTRAINT `fk_bank_transfer_receipts_transaction` FOREIGN KEY (`credit_transaction_id`) REFERENCES `credit_transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bank_transfer_receipts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_transfer_receipts`
--

LOCK TABLES `bank_transfer_receipts` WRITE;
/*!40000 ALTER TABLE `bank_transfer_receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_transfer_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_transactions`
--

DROP TABLE IF EXISTS `credit_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(30) NOT NULL,
  `amount` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `payment_gateway` varchar(80) DEFAULT NULL,
  `payment_amount` int(11) DEFAULT NULL,
  `payment_currency` varchar(10) DEFAULT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'completed',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_credit_transactions_user` (`user_id`),
  CONSTRAINT `fk_credit_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_transactions`
--

LOCK TABLES `credit_transactions` WRITE;
/*!40000 ALTER TABLE `credit_transactions` DISABLE KEYS */;
INSERT INTO `credit_transactions` VALUES (1,1,'signup_bonus',1000,'Signup bonus','signup',NULL,NULL,NULL,'completed','2026-09-22 10:39:38'),(2,1,'deduction',-1,'Create page','page:2',NULL,NULL,NULL,'completed','2026-10-16 23:44:08');
/*!40000 ALTER TABLE `credit_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails_log`
--

DROP TABLE IF EXISTS `emails_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `event_type` varchar(64) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'queued',
  `provider_message_id` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_emails_log_user_id` (`user_id`),
  KEY `idx_emails_log_event_type` (`event_type`),
  KEY `idx_emails_log_status` (`status`),
  KEY `idx_emails_log_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails_log`
--

LOCK TABLES `emails_log` WRITE;
/*!40000 ALTER TABLE `emails_log` DISABLE KEYS */;
INSERT INTO `emails_log` VALUES (1,1,'ichecodes@gmail.com','account_created','Welcome to Xinng 🚀','failed',NULL,'send_mail returned false',NULL,'2026-09-22 10:39:38'),(2,1,'ichecodes@gmail.com','login_alert','New login detected on your Xinng account','failed',NULL,'send_mail returned false',NULL,'2026-09-22 12:50:55'),(3,1,'ichecodes@gmail.com','login_alert','New login detected on your Xinng account','failed',NULL,'send_mail returned false',NULL,'2026-09-23 13:34:36'),(4,2,'auth-diagnostic-20261016@localhost.test','login_alert','New login detected on your Xinng account','failed',NULL,'send_mail returned false',NULL,'2026-10-16 22:49:29'),(5,1,'ichecodes@gmail.com','login_alert','New login detected on your Xinng account','failed',NULL,'send_mail returned false',NULL,'2026-10-16 23:27:50'),(6,1,'ichecodes@gmail.com','login_alert','New login detected on your Xinng account','failed',NULL,'send_mail returned false',NULL,'2026-10-17 19:07:45'),(7,1,'ichecodes@gmail.com','login_alert','New login detected on your Xinng account','failed',NULL,'send_mail returned false',NULL,'2026-10-18 10:45:32'),(8,1,'ichecodes@gmail.com','login_alert','New login detected on your Xinng account','failed',NULL,'send_mail returned false',NULL,'2026-10-18 11:50:28'),(9,1,'ichecodes@gmail.com','login_alert','New login detected on your Xinng account','failed',NULL,'send_mail returned false',NULL,'2026-10-21 05:29:32');
/*!40000 ALTER TABLE `emails_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_orders`
--

DROP TABLE IF EXISTS `event_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `ticket_type_id` bigint(20) unsigned NOT NULL,
  `order_ref` varchar(40) NOT NULL,
  `ticket_code` varchar(40) NOT NULL,
  `attendee_name` varchar(120) NOT NULL,
  `attendee_email` varchar(120) NOT NULL,
  `attendee_phone` varchar(40) DEFAULT NULL,
  `quantity` int(10) unsigned DEFAULT 1,
  `unit_price_minor` bigint(20) unsigned DEFAULT 0,
  `total_minor` bigint(20) unsigned DEFAULT 0,
  `currency` char(3) DEFAULT 'NGN',
  `payment_status` enum('free','pending','paid','failed','refunded','cancelled') DEFAULT 'pending',
  `pandascrow_reference` varchar(160) DEFAULT NULL,
  `pandascrow_payment_url` text DEFAULT NULL,
  `checked_in_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_event_order_ref` (`order_ref`),
  UNIQUE KEY `unique_event_ticket_code` (`ticket_code`),
  KEY `fk_event_orders_ticket` (`ticket_type_id`),
  KEY `idx_event_orders_page_id` (`page_id`),
  CONSTRAINT `fk_event_orders_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_orders_ticket` FOREIGN KEY (`ticket_type_id`) REFERENCES `event_ticket_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_orders`
--

LOCK TABLES `event_orders` WRITE;
/*!40000 ALTER TABLE `event_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_ticket_types`
--

DROP TABLE IF EXISTS `event_ticket_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_ticket_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(220) DEFAULT NULL,
  `price_minor` bigint(20) unsigned DEFAULT 0,
  `quantity_total` int(10) unsigned DEFAULT 0,
  `quantity_sold` int(10) unsigned DEFAULT 0,
  `min_per_order` int(10) unsigned DEFAULT 1,
  `max_per_order` int(10) unsigned DEFAULT 10,
  `position` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event_ticket_types_page_id` (`page_id`),
  KEY `idx_event_ticket_types_user_id` (`user_id`),
  CONSTRAINT `fk_event_ticket_types_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_ticket_types`
--

LOCK TABLES `event_ticket_types` WRITE;
/*!40000 ALTER TABLE `event_ticket_types` DISABLE KEYS */;
INSERT INTO `event_ticket_types` VALUES (1,2,1,'General Admission','Standard event admission',0,100,0,1,10,0,0,'2026-10-17 11:44:51','2026-10-17 12:16:08','2026-10-17 12:16:08'),(2,2,1,'General Admission','Standard event admission',0,100,0,1,10,0,1,'2026-10-17 11:44:59','2026-10-17 12:16:08',NULL);
/*!40000 ALTER TABLE `event_ticket_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `link_clicks`
--

DROP TABLE IF EXISTS `link_clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `link_clicks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_id` bigint(20) unsigned DEFAULT NULL,
  `block_id` bigint(20) unsigned DEFAULT NULL,
  `page_id` bigint(20) unsigned NOT NULL,
  `visitor_id` varchar(120) DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet','unknown') DEFAULT 'unknown',
  `clicked_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_link_clicks_link_id` (`link_id`),
  KEY `idx_link_clicks_page_id` (`page_id`),
  KEY `idx_link_clicks_clicked_at` (`clicked_at`),
  KEY `fk_link_clicks_block` (`block_id`),
  CONSTRAINT `fk_link_clicks_block` FOREIGN KEY (`block_id`) REFERENCES `page_blocks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_link_clicks_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_link_clicks_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `link_clicks`
--

LOCK TABLES `link_clicks` WRITE;
/*!40000 ALTER TABLE `link_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `link_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `title` varchar(150) NOT NULL,
  `url` text NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `link_type` enum('url','whatsapp','email','phone','payment','social','file','video','form','booking') DEFAULT 'url',
  `position` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `open_in_new_tab` tinyint(1) DEFAULT 1,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `click_count` bigint(20) unsigned DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_links_page_id` (`page_id`),
  KEY `idx_links_position` (`position`),
  KEY `idx_links_active` (`is_active`),
  KEY `idx_links_type` (`link_type`),
  CONSTRAINT `fk_links_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `links`
--

LOCK TABLES `links` WRITE;
/*!40000 ALTER TABLE `links` DISABLE KEYS */;
/*!40000 ALTER TABLE `links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(40) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_notifications_user` (`user_id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'account','Welcome to Xinng','Your account has been created and is ready to use.','dashboard.php',NULL,1,'2026-09-22 10:39:40','2026-10-19 09:44:46','2026-10-19 09:44:46'),(2,1,'security','Signed in successfully','Your account was accessed from this browser session.','dashboard.php',NULL,1,'2026-09-22 12:50:55','2026-10-19 09:44:46','2026-10-19 09:44:46'),(3,1,'security','Signed in successfully','Your account was accessed from this browser session.','dashboard.php',NULL,1,'2026-09-23 13:34:36','2026-10-19 09:44:46','2026-10-19 09:44:46'),(5,1,'security','Signed in successfully','Your account was accessed from this browser session.','dashboard.php',NULL,1,'2026-10-16 23:27:50','2026-10-19 09:44:46','2026-10-19 09:44:46'),(6,1,'security','Signed in successfully','Your account was accessed from this browser session.','dashboard.php',NULL,1,'2026-10-17 19:07:45','2026-10-19 09:44:46','2026-10-19 09:44:46'),(7,1,'security','Signed in successfully','Your account was accessed from this browser session.','dashboard.php',NULL,1,'2026-10-18 10:45:32','2026-10-19 09:44:46','2026-10-19 09:44:46'),(8,1,'security','Signed in successfully','Your account was accessed from this browser session.','dashboard.php',NULL,1,'2026-10-18 11:50:28','2026-10-19 09:44:46','2026-10-19 09:44:46'),(9,1,'security','Signed in successfully','Your account was accessed from this browser session.','dashboard.php',NULL,0,'2026-10-21 05:29:32',NULL,'2026-10-21 05:29:32');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization_activity`
--

DROP TABLE IF EXISTS `organization_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_activity` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned NOT NULL,
  `actor_user_id` bigint(20) unsigned NOT NULL,
  `event_type` varchar(64) NOT NULL,
  `target_user_id` bigint(20) unsigned DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_organization_activity_actor` (`actor_user_id`),
  KEY `fk_organization_activity_target` (`target_user_id`),
  KEY `idx_organization_activity_org` (`organization_id`,`created_at`),
  CONSTRAINT `fk_organization_activity_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_organization_activity_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_organization_activity_target` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_activity`
--

LOCK TABLES `organization_activity` WRITE;
/*!40000 ALTER TABLE `organization_activity` DISABLE KEYS */;
INSERT INTO `organization_activity` VALUES (1,5,1,'workspace.switched',NULL,NULL,'2026-10-19 07:04:47');
/*!40000 ALTER TABLE `organization_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization_members`
--

DROP TABLE IF EXISTS `organization_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `role` varchar(32) NOT NULL DEFAULT 'viewer',
  `status` varchar(24) NOT NULL DEFAULT 'active',
  `invited_by` bigint(20) unsigned DEFAULT NULL,
  `joined_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_organization_member` (`organization_id`,`user_id`),
  KEY `fk_organization_members_inviter` (`invited_by`),
  KEY `idx_organization_members_user` (`user_id`,`status`),
  KEY `idx_organization_members_org` (`organization_id`,`status`),
  CONSTRAINT `fk_organization_members_inviter` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_organization_members_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_organization_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_members`
--

LOCK TABLES `organization_members` WRITE;
/*!40000 ALTER TABLE `organization_members` DISABLE KEYS */;
INSERT INTO `organization_members` VALUES (3,2,5,'owner','active',5,'2026-10-18 17:27:19','2026-10-18 17:27:19','2026-10-18 17:27:19'),(8,5,1,'owner','active',1,'2026-10-19 07:04:35','2026-10-19 07:04:35','2026-10-19 07:04:35');
/*!40000 ALTER TABLE `organization_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organizations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `status` varchar(24) NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_organizations_creator` (`created_by`),
  KEY `idx_organizations_status` (`status`),
  CONSTRAINT `fk_organizations_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizations`
--

LOCK TABLES `organizations` WRITE;
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
INSERT INTO `organizations` VALUES (2,'cee4d142-cb10-11f1-9fb8-c7d7ddd078ea','Aqqute Admin Workspace','aqqute-admin-workspace',5,'active','2026-10-18 17:27:19','2026-10-18 17:27:19',NULL),(5,'ff589331-cb82-11f1-9fb8-c7d7ddd078ea','Aquila\'s workspace','aquila-s-workspace',1,'active','2026-10-19 07:04:35','2026-10-19 07:04:35',NULL);
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_blocks`
--

DROP TABLE IF EXISTS `page_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_blocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(40) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `destination_url` text DEFAULT NULL,
  `image_path` mediumtext DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `position` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_page_blocks_page` (`page_id`),
  KEY `fk_page_blocks_user` (`user_id`),
  CONSTRAINT `fk_page_blocks_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_page_blocks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_blocks`
--

LOCK TABLES `page_blocks` WRITE;
/*!40000 ALTER TABLE `page_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_corporate_buttons`
--

DROP TABLE IF EXISTS `page_corporate_buttons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_corporate_buttons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `label` varchar(30) NOT NULL,
  `destination_url` text NOT NULL,
  `button_color` varchar(20) DEFAULT '#1979BF',
  `text_color` varchar(20) DEFAULT '#FFFFFF',
  `position` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_corporate_buttons_page_id` (`page_id`),
  KEY `idx_page_corporate_buttons_user_id` (`user_id`),
  KEY `idx_page_corporate_buttons_position` (`position`),
  CONSTRAINT `fk_page_corporate_buttons_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_corporate_buttons`
--

LOCK TABLES `page_corporate_buttons` WRITE;
/*!40000 ALTER TABLE `page_corporate_buttons` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_corporate_buttons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_corporate_cards`
--

DROP TABLE IF EXISTS `page_corporate_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_corporate_cards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `card_type` enum('text','video','pdf') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `cta_label` varchar(30) DEFAULT NULL,
  `destination_url` text DEFAULT NULL,
  `fill_type` enum('color','gradient','photo') DEFAULT 'color',
  `fill_color` varchar(20) DEFAULT '#06111E',
  `gradient_start` varchar(20) DEFAULT '#06111E',
  `gradient_end` varchar(20) DEFAULT '#0A9994',
  `photo_path` mediumtext DEFAULT NULL,
  `outline_color` varchar(20) DEFAULT '#0A9994',
  `outline_weight` tinyint(3) unsigned DEFAULT 0,
  `position` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_corporate_cards_page_id` (`page_id`),
  KEY `idx_page_corporate_cards_user_id` (`user_id`),
  KEY `idx_page_corporate_cards_position` (`position`),
  CONSTRAINT `fk_page_corporate_cards_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_corporate_cards`
--

LOCK TABLES `page_corporate_cards` WRITE;
/*!40000 ALTER TABLE `page_corporate_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_corporate_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_corporate_event_registrations`
--

DROP TABLE IF EXISTS `page_corporate_event_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_corporate_event_registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `status` varchar(30) DEFAULT 'new',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_corporate_registration_page_id` (`page_id`),
  KEY `idx_page_corporate_registration_status` (`status`),
  CONSTRAINT `fk_page_corporate_event_registrations_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_corporate_event_registrations`
--

LOCK TABLES `page_corporate_event_registrations` WRITE;
/*!40000 ALTER TABLE `page_corporate_event_registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_corporate_event_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_corporate_events`
--

DROP TABLE IF EXISTS `page_corporate_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_corporate_events` (
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(80) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `location_text` varchar(120) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `countdown_enabled` tinyint(1) DEFAULT 1,
  `book_link` text DEFAULT NULL,
  `brochure_link` text DEFAULT NULL,
  `register_enabled` tinyint(1) DEFAULT 1,
  `card_color` varchar(20) DEFAULT '#062947',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`page_id`),
  KEY `idx_page_corporate_events_user_id` (`user_id`),
  KEY `idx_page_corporate_events_start_at` (`start_at`),
  CONSTRAINT `fk_page_corporate_events_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_corporate_events`
--

LOCK TABLES `page_corporate_events` WRITE;
/*!40000 ALTER TABLE `page_corporate_events` DISABLE KEYS */;
INSERT INTO `page_corporate_events` VALUES (2,1,'Your event','Add the essential details attendees need to register.',NULL,NULL,'','',1,'','',1,'#201A19','2026-10-16 23:44:08','2026-10-16 23:44:08');
/*!40000 ALTER TABLE `page_corporate_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_corporate_facts`
--

DROP TABLE IF EXISTS `page_corporate_facts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_corporate_facts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `fact_type` enum('specialty','location','link') NOT NULL,
  `label` varchar(80) NOT NULL,
  `url` text DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_corporate_facts_page_type` (`page_id`,`fact_type`),
  KEY `idx_page_corporate_facts_user_id` (`user_id`),
  KEY `idx_page_corporate_facts_position` (`position`),
  CONSTRAINT `fk_page_corporate_facts_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_corporate_facts`
--

LOCK TABLES `page_corporate_facts` WRITE;
/*!40000 ALTER TABLE `page_corporate_facts` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_corporate_facts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_corporate_profiles`
--

DROP TABLE IF EXISTS `page_corporate_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_corporate_profiles` (
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `header_photo_path` mediumtext DEFAULT NULL,
  `logo_path` mediumtext DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `page_description` varchar(200) DEFAULT NULL,
  `meeting_link` text DEFAULT NULL,
  `brochure_link` text DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `whatsapp` varchar(40) DEFAULT NULL,
  `team_title` varchar(30) DEFAULT 'Team',
  `team_description` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`page_id`),
  KEY `idx_page_corporate_profiles_user_id` (`user_id`),
  CONSTRAINT `fk_page_corporate_profiles_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_corporate_profiles`
--

LOCK TABLES `page_corporate_profiles` WRITE;
/*!40000 ALTER TABLE `page_corporate_profiles` DISABLE KEYS */;
INSERT INTO `page_corporate_profiles` VALUES (2,1,'','','','','','','','','','Team','','2026-10-16 23:44:08','2026-10-16 23:44:08');
/*!40000 ALTER TABLE `page_corporate_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_corporate_quote_requests`
--

DROP TABLE IF EXISTS `page_corporate_quote_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_corporate_quote_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `company` varchar(120) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `request_text` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'new',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_corporate_quote_page_id` (`page_id`),
  KEY `idx_page_corporate_quote_status` (`status`),
  CONSTRAINT `fk_page_corporate_quote_requests_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_corporate_quote_requests`
--

LOCK TABLES `page_corporate_quote_requests` WRITE;
/*!40000 ALTER TABLE `page_corporate_quote_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_corporate_quote_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_corporate_socials`
--

DROP TABLE IF EXISTS `page_corporate_socials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_corporate_socials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `platform` varchar(40) NOT NULL,
  `url` text NOT NULL,
  `position` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_corporate_socials_page_id` (`page_id`),
  KEY `idx_page_corporate_socials_user_id` (`user_id`),
  KEY `idx_page_corporate_socials_position` (`position`),
  CONSTRAINT `fk_page_corporate_socials_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_corporate_socials`
--

LOCK TABLES `page_corporate_socials` WRITE;
/*!40000 ALTER TABLE `page_corporate_socials` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_corporate_socials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_corporate_team_members`
--

DROP TABLE IF EXISTS `page_corporate_team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_corporate_team_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `photo_path` mediumtext DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `role_title` varchar(30) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `linkedin_url` text DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_corporate_team_page_id` (`page_id`),
  KEY `idx_page_corporate_team_user_id` (`user_id`),
  KEY `idx_page_corporate_team_position` (`position`),
  CONSTRAINT `fk_page_corporate_team_members_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_corporate_team_members`
--

LOCK TABLES `page_corporate_team_members` WRITE;
/*!40000 ALTER TABLE `page_corporate_team_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_corporate_team_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_events`
--

DROP TABLE IF EXISTS `page_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_events` (
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `event_format` enum('physical','virtual','hybrid') DEFAULT 'physical',
  `attendance_type` enum('open','ticketed') NOT NULL DEFAULT 'open',
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `venue` varchar(180) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `virtual_url` text DEFAULT NULL,
  `cover_image_path` mediumtext DEFAULT NULL,
  `organizer_name` varchar(120) DEFAULT NULL,
  `contact_email` varchar(120) DEFAULT NULL,
  `currency` char(3) DEFAULT 'NGN',
  `capacity` int(10) unsigned DEFAULT 0,
  `sales_start_at` datetime DEFAULT NULL,
  `sales_end_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`page_id`),
  KEY `idx_page_events_user_id` (`user_id`),
  KEY `idx_page_events_start_at` (`start_at`),
  CONSTRAINT `fk_page_events_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_events`
--

LOCK TABLES `page_events` WRITE;
/*!40000 ALTER TABLE `page_events` DISABLE KEYS */;
INSERT INTO `page_events` VALUES (2,1,'physical','open','2026-10-18 09:44:00','2026-10-18 11:42:00','Zoom','PH','Nigeria','','','john','quillz.ak@gmail.com','NGN',20,NULL,NULL,'2026-10-17 11:44:51','2026-10-17 12:16:08');
/*!40000 ALTER TABLE `page_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_socials`
--

DROP TABLE IF EXISTS `page_socials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_socials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `platform` varchar(40) NOT NULL,
  `label` varchar(120) DEFAULT NULL,
  `url` text NOT NULL,
  `icon` varchar(80) DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_page_socials_page_id` (`page_id`),
  KEY `idx_page_socials_user_id` (`user_id`),
  KEY `idx_page_socials_platform` (`platform`),
  KEY `idx_page_socials_position` (`position`),
  CONSTRAINT `fk_page_socials_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_socials`
--

LOCK TABLES `page_socials` WRITE;
/*!40000 ALTER TABLE `page_socials` DISABLE KEYS */;
INSERT INTO `page_socials` VALUES (1,2,1,'Instagram','Instagram','instagram.com/quillz','Instagram',0,1,'2026-10-17 12:16:08','2026-10-17 12:16:08',NULL);
/*!40000 ALTER TABLE `page_socials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_theme_settings`
--

DROP TABLE IF EXISTS `page_theme_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_theme_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `theme_id` bigint(20) unsigned DEFAULT NULL,
  `custom_background` text DEFAULT NULL,
  `custom_primary_color` varchar(20) DEFAULT NULL,
  `custom_secondary_color` varchar(20) DEFAULT NULL,
  `custom_accent_color` varchar(20) DEFAULT NULL,
  `custom_font_family` varchar(120) DEFAULT NULL,
  `custom_button_style` varchar(50) DEFAULT NULL,
  `custom_css` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_page_theme_settings` (`page_id`),
  CONSTRAINT `fk_page_theme_settings_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_theme_settings`
--

LOCK TABLES `page_theme_settings` WRITE;
/*!40000 ALTER TABLE `page_theme_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_theme_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_views`
--

DROP TABLE IF EXISTS `page_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_views` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `visitor_id` varchar(120) DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet','unknown') DEFAULT 'unknown',
  `browser` varchar(100) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `viewed_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_views_page_id` (`page_id`),
  KEY `idx_page_views_viewed_at` (`viewed_at`),
  CONSTRAINT `fk_page_views_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_views`
--

LOCK TABLES `page_views` WRITE;
/*!40000 ALTER TABLE `page_views` DISABLE KEYS */;
INSERT INTO `page_views` VALUES (1,2,'a25564c6f4c9b8c6b6b8877aea887d6e','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','http://localhost/xing/page_builder.php?id=2',NULL,NULL,'desktop','Chrome','Windows','2026-10-17 11:20:33'),(2,2,'a25564c6f4c9b8c6b6b8877aea887d6e','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','http://localhost/xing/page_builder.php?id=2',NULL,NULL,'desktop','Chrome','Windows','2026-10-17 12:25:47'),(3,2,'a25564c6f4c9b8c6b6b8877aea887d6e','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','http://localhost/xing/page_builder.php?id=2',NULL,NULL,'desktop','Chrome','Windows','2026-10-17 12:25:53');
/*!40000 ALTER TABLE `page_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `page_type` enum('creator','corporate','event') NOT NULL DEFAULT 'creator',
  `corporate_metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`corporate_metadata`)),
  `slug` varchar(80) DEFAULT NULL,
  `title` varchar(150) NOT NULL DEFAULT '',
  `bio` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `profile_image_url` text DEFAULT NULL,
  `profile_image_path` mediumtext DEFAULT NULL,
  `cover_image_url` text DEFAULT NULL,
  `theme` varchar(80) DEFAULT 'default',
  `layout` varchar(80) DEFAULT 'simple',
  `font` varchar(80) DEFAULT 'system',
  `title_color` varchar(20) DEFAULT '#26282C',
  `description_color` varchar(20) DEFAULT '#26282C',
  `header_mode` varchar(20) DEFAULT 'color',
  `header_color` varchar(20) DEFAULT '#26282C',
  `header_gradient_start` varchar(20) DEFAULT '#26282C',
  `header_gradient_end` varchar(20) DEFAULT '#0A9994',
  `header_image_path` mediumtext DEFAULT NULL,
  `header_fit` varchar(20) DEFAULT 'cover',
  `background_mode` varchar(20) DEFAULT 'color',
  `background_color` varchar(20) DEFAULT '#FFFAF6',
  `background_gradient_start` varchar(20) DEFAULT '#FFFAF6',
  `background_gradient_end` varchar(20) DEFAULT '#FFFFFF',
  `background_image_path` mediumtext DEFAULT NULL,
  `social_icon_style` varchar(20) DEFAULT 'original',
  `social_placement` varchar(20) DEFAULT 'top',
  `block_shape` varchar(40) DEFAULT 'rounded',
  `block_shadow` varchar(40) DEFAULT 'soft',
  `block_color` varchar(20) DEFAULT '#0A9994',
  `block_text_color` varchar(20) DEFAULT '#FFFAF6',
  `hide_xinng_logo` tinyint(1) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `status` varchar(20) DEFAULT 'published',
  `published_at` datetime DEFAULT NULL,
  `seo_title` varchar(180) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_pages_slug` (`slug`),
  KEY `idx_pages_user_id` (`user_id`),
  KEY `idx_pages_slug` (`slug`),
  KEY `idx_pages_published` (`is_published`),
  KEY `idx_pages_page_type` (`page_type`),
  CONSTRAINT `fk_pages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,1,'creator',NULL,'quillz','Aquila',NULL,NULL,NULL,NULL,NULL,'default','simple','system','#26282C','#26282C','color','#26282C','#26282C','#0A9994',NULL,'cover','color','#FFFAF6','#FFFAF6','#FFFFFF',NULL,'original','top','rounded','soft','#0A9994','#FFFAF6',0,1,0,'published',NULL,NULL,NULL,'2026-09-22 10:39:38','2026-09-22 10:39:38',NULL),(2,1,'event','{\"header_photo\":\"\",\"logo\":\"\",\"company_name\":\"\",\"description\":\"\",\"cards_title\":\"Core Capabilities\",\"cards_lede\":\"Integrated technical solutions designed for any environment and high-risk industrial operations.\",\"actions_title\":\"What would you like to do?\",\"actions_lede\":\"\",\"event_register_title\":\"Event Registration\",\"hero_primary_cta_label\":\"\",\"hero_primary_cta_url\":\"\",\"quote_title\":\"\",\"quote_description\":\"\",\"quote_button_label\":\"\",\"contact\":{\"meeting_link\":\"\",\"brochure_link\":\"\",\"phone\":\"\",\"email\":\"\",\"whatsapp\":\"\"},\"specialties\":[],\"locations\":[],\"links\":[],\"socials\":[],\"event\":{\"title\":\"Your event\",\"description\":\"Add the essential details attendees need to register.\",\"start_at\":\"\",\"end_at\":\"\",\"location\":\"\",\"city\":\"\",\"countdown\":true,\"book_link\":\"\",\"brochure_link\":\"\",\"register\":true,\"card_color\":\"#201A19\",\"button_label\":\"Join Webinar\"},\"cards\":[],\"buttons\":[],\"team\":{\"title\":\"Team\",\"description\":\"\",\"members\":[]}}','chill-and-relax','chill and relax','an evening of wine and stimulating conversations','an evening of wine and stimulating conversations','data:image/png;base64,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','data:image/png;base64,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',NULL,'warm','compact','system','#26282C','#26282C','color','#201A19','#26282C','#0A9994','','cover','color','#FFF9F7','#FFFAF6','#FFFFFF','','original','top','rounded','soft','#E83B32','#FFFFFF',0,1,0,'published','2026-10-17 12:16:08',NULL,NULL,'2026-10-16 23:44:08','2026-10-17 12:16:08',NULL);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_password_resets_user` (`user_id`),
  KEY `idx_password_resets_token_hash` (`token_hash`),
  KEY `idx_password_resets_expires_at` (`expires_at`),
  CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subscription_id` bigint(20) unsigned DEFAULT NULL,
  `plan_id` bigint(20) unsigned DEFAULT NULL,
  `billing_interval` enum('monthly','yearly') DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'NGN',
  `payment_provider` enum('paystack','flutterwave','stripe','manual') DEFAULT 'paystack',
  `provider_reference` varchar(190) DEFAULT NULL,
  `status` enum('pending','successful','failed','refunded') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_reference` (`provider_reference`),
  KEY `fk_wallet_payments_plan` (`plan_id`),
  KEY `idx_wallet_payments_user` (`user_id`),
  KEY `idx_wallet_payments_status` (`status`),
  KEY `idx_wallet_payments_subscription` (`subscription_id`),
  CONSTRAINT `fk_wallet_payments_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_wallet_payments_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_wallet_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `price_monthly` decimal(12,2) DEFAULT 0.00,
  `price_yearly` decimal(12,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'NGN',
  `max_pages` int(11) DEFAULT 1,
  `max_links_per_page` int(11) DEFAULT 10,
  `custom_domain_enabled` tinyint(1) DEFAULT 0,
  `analytics_enabled` tinyint(1) DEFAULT 0,
  `remove_branding` tinyint(1) DEFAULT 0,
  `qr_customization_enabled` tinyint(1) DEFAULT 0,
  `team_enabled` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_plans_slug` (`slug`),
  KEY `idx_plans_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (1,'Free','free',0.00,0.00,'NGN',1,5,0,0,0,0,0,1,'2026-09-22 10:37:35','2026-09-22 10:37:35'),(2,'Pro','pro',3000.00,30000.00,'NGN',3,50,0,1,1,1,0,1,'2026-09-22 10:37:35','2026-09-22 10:37:35'),(3,'Business','business',10000.00,100000.00,'NGN',10,200,1,1,1,1,1,1,'2026-09-22 10:37:35','2026-09-22 10:37:35');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qr_code_scans`
--

DROP TABLE IF EXISTS `qr_code_scans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qr_code_scans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `qr_code_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_hash` varchar(128) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referer` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `device_type` varchar(40) DEFAULT NULL,
  `scanned_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_qr_code_scans_qr_code_id` (`qr_code_id`),
  KEY `idx_qr_code_scans_scanned_at` (`scanned_at`),
  CONSTRAINT `fk_qr_code_scans_qr_code` FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qr_code_scans`
--

LOCK TABLES `qr_code_scans` WRITE;
/*!40000 ALTER TABLE `qr_code_scans` DISABLE KEYS */;
/*!40000 ALTER TABLE `qr_code_scans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qr_codes`
--

DROP TABLE IF EXISTS `qr_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qr_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `page_id` bigint(20) unsigned DEFAULT NULL,
  `short_link_id` bigint(20) unsigned DEFAULT NULL,
  `profile_page_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('profile_page','website','page','custom') DEFAULT 'website',
  `title` varchar(150) NOT NULL DEFAULT 'QR Code',
  `name` varchar(120) DEFAULT 'QR Code',
  `destination_url` text DEFAULT NULL,
  `back_half` varchar(64) DEFAULT NULL,
  `qr_image_url` text DEFAULT NULL,
  `qr_image_path` text DEFAULT NULL,
  `foreground_color` varchar(20) DEFAULT '#000000',
  `code_color` varchar(20) DEFAULT '#000000',
  `background_color` varchar(20) DEFAULT '#FFFFFF',
  `corner_color` varchar(20) DEFAULT NULL,
  `pattern_style` varchar(80) DEFAULT 'default',
  `corner_style` varchar(80) DEFAULT 'square',
  `frame_style` varchar(80) DEFAULT NULL,
  `frame_text` varchar(120) DEFAULT NULL,
  `logo_path` text DEFAULT NULL,
  `remove_xinng_logo` tinyint(1) DEFAULT 0,
  `scan_count` bigint(20) unsigned DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `status` enum('active','archived','disabled') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_qr_codes_back_half` (`back_half`),
  KEY `idx_qr_codes_user_id` (`user_id`),
  KEY `idx_qr_codes_page_id` (`page_id`),
  KEY `idx_qr_codes_short_link_id` (`short_link_id`),
  KEY `idx_qr_codes_profile_page_id` (`profile_page_id`),
  KEY `idx_qr_codes_status` (`status`),
  KEY `idx_qr_codes_type` (`type`),
  CONSTRAINT `fk_qr_codes_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qr_codes_profile_page` FOREIGN KEY (`profile_page_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qr_codes_short_link` FOREIGN KEY (`short_link_id`) REFERENCES `short_links` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qr_codes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qr_codes`
--

LOCK TABLES `qr_codes` WRITE;
/*!40000 ALTER TABLE `qr_codes` DISABLE KEYS */;
INSERT INTO `qr_codes` VALUES (1,1,1,NULL,1,'profile_page','Aquila page QR','Aquila page QR','https://localhost/xing/quillz',NULL,'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=16&color=000000&bgcolor=FFFFFF&data=http%3A%2F%2Flocalhost%2Fxing%2Fquillz',NULL,'#000000','#000000','#FFFFFF',NULL,'default','square',NULL,NULL,NULL,0,0,1,'active','2026-10-16 23:28:44','2026-10-21 06:09:46',NULL),(2,1,2,NULL,2,'profile_page','chill and relax','chill and relax','https://localhost/xing/chill-and-relax',NULL,'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=16&color=000000&bgcolor=FFFFFF&data=http%3A%2F%2Flocalhost%2Fxing%2Fchill-and-relax',NULL,'#000000','#000000','#FFFFFF',NULL,'default','square',NULL,NULL,NULL,0,0,1,'active','2026-10-16 23:44:08','2026-10-17 19:07:45',NULL);
/*!40000 ALTER TABLE `qr_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qr_scans`
--

DROP TABLE IF EXISTS `qr_scans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qr_scans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `qr_code_id` bigint(20) unsigned NOT NULL,
  `page_id` bigint(20) unsigned NOT NULL,
  `visitor_id` varchar(120) DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `scanned_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_qr_scans_qr_code_id` (`qr_code_id`),
  KEY `idx_qr_scans_page_id` (`page_id`),
  KEY `idx_qr_scans_visitor_id` (`visitor_id`),
  KEY `idx_qr_scans_scanned_at` (`scanned_at`),
  KEY `idx_qr_scans_country` (`country`),
  CONSTRAINT `fk_qr_scans_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_qr_scans_qr_code` FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qr_scans`
--

LOCK TABLES `qr_scans` WRITE;
/*!40000 ALTER TABLE `qr_scans` DISABLE KEYS */;
/*!40000 ALTER TABLE `qr_scans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `short_link_clicks`
--

DROP TABLE IF EXISTS `short_link_clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `short_link_clicks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `short_link_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_hash` varchar(128) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referer` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `device_type` varchar(40) DEFAULT NULL,
  `clicked_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_short_link_clicks_short_link_id` (`short_link_id`),
  KEY `idx_short_link_clicks_clicked_at` (`clicked_at`),
  CONSTRAINT `fk_short_link_clicks_short_link` FOREIGN KEY (`short_link_id`) REFERENCES `short_links` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `short_link_clicks`
--

LOCK TABLES `short_link_clicks` WRITE;
/*!40000 ALTER TABLE `short_link_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `short_link_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `short_links`
--

DROP TABLE IF EXISTS `short_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `short_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `destination_url` text NOT NULL,
  `back_half` varchar(64) NOT NULL,
  `status` enum('active','archived','disabled') DEFAULT 'active',
  `click_count` bigint(20) unsigned DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_short_links_back_half` (`back_half`),
  KEY `idx_short_links_user_id` (`user_id`),
  KEY `idx_short_links_status` (`status`),
  KEY `idx_short_links_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_short_links_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `short_links`
--

LOCK TABLES `short_links` WRITE;
/*!40000 ALTER TABLE `short_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `short_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_links`
--

DROP TABLE IF EXISTS `social_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `social_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL,
  `platform` enum('instagram','linkedin','x','facebook','youtube','tiktok','snapchat','telegram','github','website') NOT NULL,
  `url` text NOT NULL,
  `position` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_social_links_page_id` (`page_id`),
  KEY `idx_social_links_platform` (`platform`),
  CONSTRAINT `fk_social_links_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_links`
--

LOCK TABLES `social_links` WRITE;
/*!40000 ALTER TABLE `social_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `plan_id` bigint(20) unsigned NOT NULL,
  `status` enum('active','trialing','past_due','cancelled','expired') NOT NULL DEFAULT 'active',
  `payment_provider` enum('paystack','flutterwave','stripe','manual') NOT NULL DEFAULT 'paystack',
  `billing_interval` enum('monthly','yearly') DEFAULT NULL,
  `provider_customer_id` varchar(190) DEFAULT NULL,
  `provider_subscription_id` varchar(190) DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `renews_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_wallet_subscriptions_user` (`user_id`,`status`),
  KEY `idx_wallet_subscriptions_plan` (`plan_id`),
  KEY `idx_wallet_subscriptions_renews` (`renews_at`),
  CONSTRAINT `fk_wallet_subscriptions_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  CONSTRAINT `fk_wallet_subscriptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,1,1,'active','manual',NULL,NULL,NULL,'2026-10-21 08:55:00',NULL,NULL,NULL,'2026-10-21 08:55:00','2026-10-21 08:55:00');
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `credit_balance` int(11) NOT NULL DEFAULT 1000,
  `credits_purchased_total` int(11) NOT NULL DEFAULT 0,
  `credits_used_total` int(11) NOT NULL DEFAULT 0,
  `avatar_url` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `status` enum('active','suspended','deleted') DEFAULT 'active',
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ab7caef9-b669-11f1-b976-84c5a66dd2dc','Aquila','ichecodes@gmail.com','$2y$10$8cERN6xGeujvZu/27TEGN.R/vtkxVNd5X1aYqShizOGdVAP9ZKMhK',999,0,1,NULL,NULL,NULL,'2026-10-21 05:29:32','active','user','2026-09-22 10:39:38','2026-10-21 05:29:32',NULL),(5,'cee433ac-cb10-11f1-9fb8-c7d7ddd078ea','Aqqute Admin','aqqute.dev@gmail.com','$2y$10$YuHsFyLfjojEhKTJyqym9uF5ZHV3FRBsfQhyLH4ga7uhqWDmMho4e',1000,0,0,NULL,NULL,NULL,NULL,'active','admin','2026-10-18 17:27:19','2026-10-18 17:27:19',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `x_accounts`
--

DROP TABLE IF EXISTS `x_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `x_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `x_user_id` varchar(32) NOT NULL,
  `username` varchar(15) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `profile_image_url` text DEFAULT NULL,
  `access_token_encrypted` text NOT NULL,
  `refresh_token_encrypted` text DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `scopes` varchar(500) NOT NULL,
  `connected_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_x_account` (`user_id`,`x_user_id`),
  KEY `idx_x_accounts_user_id` (`user_id`),
  CONSTRAINT `fk_x_accounts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `x_accounts`
--

LOCK TABLES `x_accounts` WRITE;
/*!40000 ALTER TABLE `x_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `x_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'lyrivers_xinng'
--

--
-- Dumping routines for database 'lyrivers_xinng'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-22 13:31:39
