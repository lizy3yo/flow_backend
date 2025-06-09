-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2025 at 10:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `flow_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` varchar(20) NOT NULL DEFAULT 'admin',
  `session_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`, `role`, `session_token`) VALUES
(79, 'Gordon College', 'gordoncollege@gmail.com', '$2y$10$4dBajwDfteM/bFV9b9PPXOn/YB4yNaDdIZbWVY9ix9OlqF9r0slYi', '2025-05-08 07:50:52', '2025-06-07 17:30:48', 'admin', '65e372b2247115e84f956051becc2196d8b97502d61fa94611692190caba394d'),
(82, 'Tech Solutions Inc.', 'tech.solutions@example.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:26:36', '2025-05-28 07:37:55', 'admin', 'ae4dd2818073baff16007abd31f0faa42bf13a04e6bd70e5b7fff26be6ee4bef'),
(83, 'Green Gardens Co.', 'green.gardens@example.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:26:36', '2025-05-22 08:11:00', 'admin', '6a13b226d555b511307b89f77fab0cf2d7fa091b33fa5baac5b85cd04be16371'),
(84, 'Health First Clinic', 'health.first@example.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:26:36', '2025-05-22 08:11:40', 'admin', 'aa8a4650b355b7a56a713aaeac3d607b5e891d8d9fe4390318049b0c012d4dc2'),
(85, 'Dynamic Innovations', 'info@dynamicinnovations.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:26:50', '2025-05-22 07:26:50', 'admin', NULL),
(86, 'Urban Fitness Hub', 'contact@urbanfitness.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:26:50', '2025-05-22 07:26:50', 'admin', NULL),
(87, 'Culinary Arts Institute', 'admissions@culinaryarts.edu', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:26:50', '2025-05-22 07:26:50', 'admin', NULL),
(91, 'Dynamic Innovations', 'info.di@example.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:27:57', '2025-05-22 07:27:57', 'admin', NULL),
(92, 'Urban Fitness Hub', 'contact.ufh@example.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:27:57', '2025-05-22 07:27:57', 'admin', NULL),
(93, 'Culinary Arts Institute', 'admissions.cai@example.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:27:57', '2025-05-22 07:27:57', 'admin', NULL),
(94, 'Global Logistics Corp', 'logistics@globalcorp.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:27:57', '2025-05-22 07:27:57', 'admin', NULL),
(95, 'Aqua Marine Center', 'aquamarine@example.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:27:57', '2025-05-22 07:27:57', 'admin', NULL),
(101, 'Innovate Nexus', 'contact@innovatenexus.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:29:30', '2025-05-22 07:29:30', 'admin', NULL),
(102, 'Apex Auto Services', 'service@apexauto.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:29:30', '2025-05-22 07:29:30', 'admin', NULL),
(103, 'Bridges Education', 'enroll@bridgesedu.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:29:30', '2025-05-22 07:29:30', 'admin', NULL),
(104, 'Zenith Health Solutions', 'info@zenithhealth.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:29:30', '2025-05-22 07:37:35', 'admin', 'd78d6e42dbd1d5eda4a4444dad20d9ebbf81369aabc72cb09d460dfa157850d8'),
(105, 'Harmony Music School', 'admissions@harmonymusic.com', '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6', '2025-05-22 07:29:30', '2025-05-22 07:29:30', 'admin', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `action` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `admin_id`, `type`, `message`, `action`, `entity_id`, `created_at`, `read_at`) VALUES
(1, 79, 'service', 'Archived service: kharl', 'archive', 70, '2025-05-27 19:36:28', '2025-05-27 19:45:58'),
(2, 79, 'service', 'Archived service: kharl', 'archive', 70, '2025-05-27 19:47:22', '2025-05-28 05:59:40'),
(3, 79, 'service', 'Created new service: adc', 'create', 72, '2025-05-27 19:48:04', '2025-05-28 05:59:40'),
(4, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-05-27 19:48:56', '2025-05-28 05:59:40'),
(5, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-05-27 19:48:56', '2025-05-28 05:59:40'),
(6, 79, 'status', 'Changed establishment status to: active', 'update_status', NULL, '2025-05-27 19:48:56', '2025-05-28 05:59:40'),
(7, 79, 'queue', 'Accepted queue B-003 for CEAS', 'new_queue', 0, '2025-05-27 19:49:47', '2025-05-28 05:59:40'),
(8, 79, 'service', 'Archived service: adc', 'archive', 72, '2025-05-28 05:27:45', '2025-05-28 05:59:40'),
(9, 79, 'service', 'Archived service: kharl', 'archive', 71, '2025-05-28 05:31:45', '2025-05-28 05:59:40'),
(10, 79, 'service', 'Created new service: kharl', 'create', 73, '2025-05-28 05:34:58', '2025-05-28 18:17:15'),
(11, 79, 'queue', 'Accepted queue B-001 for CCS', 'new_queue', 0, '2025-05-28 07:58:59', NULL),
(12, 79, 'queue', 'Accepted queue A-001 for CCS', 'new_queue', 0, '2025-05-28 07:59:03', NULL),
(13, 79, 'queue', 'Accepted queue B-002 for CCS', 'new_queue', 0, '2025-05-28 09:03:40', NULL),
(14, 79, 'queue', 'Accepted queue B-003 for CCS', 'new_queue', 0, '2025-05-28 09:11:30', NULL),
(15, 79, 'queue', 'Accepted queue P-001 for CCS', 'new_queue', 0, '2025-05-28 09:18:20', NULL),
(16, 79, 'queue', 'Accepted queue RCCS-001 for CCS', 'new_queue', 0, '2025-05-28 09:41:51', NULL),
(17, 79, 'queue', 'Accepted queue RCEAS-001 for CEAS', 'new_queue', 0, '2025-05-28 09:41:59', NULL),
(18, 79, 'queue', 'Accepted queue PCCS-001 for CCS', 'new_queue', 0, '2025-05-28 10:03:17', '2025-05-28 17:42:12'),
(19, 79, 'queue', 'Accepted queue SCCS-001 for CCS', 'new_queue', 0, '2025-05-28 10:03:39', '2025-05-28 17:42:12'),
(20, 79, 'queue', 'Accepted queue SCCS-002 for CCS', 'new_queue', 0, '2025-05-28 10:37:22', '2025-05-28 17:42:10'),
(21, 79, 'service', 'Archived service: kharl', 'archive', 73, '2025-05-28 17:03:52', '2025-05-28 17:42:10'),
(22, 79, 'service', 'Created new service: kharl', 'create', 74, '2025-05-28 18:14:15', NULL),
(23, 79, 'service', 'Archived service: kharl', 'archive', 74, '2025-05-28 18:23:48', NULL),
(24, 79, 'service', 'Created new service: kharl', 'create', 75, '2025-05-28 19:49:32', NULL),
(25, 79, 'service', 'Archived service: kharl', 'archive', 75, '2025-05-28 19:49:49', NULL),
(26, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-05-28 20:19:20', NULL),
(27, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-05-28 20:19:20', NULL),
(28, 79, 'status', 'Changed establishment status to: inactive', 'update_status', NULL, '2025-05-28 20:19:20', NULL),
(29, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-05-28 20:31:29', NULL),
(30, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-05-28 20:31:29', NULL),
(31, 79, 'status', 'Changed establishment status to: inactive', 'update_status', NULL, '2025-05-28 20:31:29', NULL),
(32, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-05-28 20:32:15', NULL),
(33, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-05-28 20:32:15', NULL),
(34, 79, 'status', 'Changed establishment status to: inactive', 'update_status', NULL, '2025-05-28 20:32:15', NULL),
(35, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-05-28 20:36:35', NULL),
(36, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-05-28 20:36:35', NULL),
(37, 79, 'status', 'Changed establishment status to: inactive', 'update_status', NULL, '2025-05-28 20:36:35', NULL),
(38, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-05-28 20:36:44', NULL),
(39, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-05-28 20:36:44', NULL),
(40, 79, 'status', 'Changed establishment status to: inactive', 'update_status', NULL, '2025-05-28 20:36:44', NULL),
(41, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-05-28 20:44:15', NULL),
(42, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-05-28 20:44:15', NULL),
(43, 79, 'status', 'Changed establishment status to: inactive', 'update_status', NULL, '2025-05-28 20:44:15', NULL),
(44, 79, 'service', 'Archived service: kharl', 'archive', 75, '2025-05-28 20:44:31', NULL),
(45, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-05-28 20:46:46', NULL),
(46, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-05-28 20:46:46', NULL),
(47, 79, 'status', 'Changed establishment status to: inactive', 'update_status', NULL, '2025-05-28 20:46:46', NULL),
(48, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-05-28 20:46:53', NULL),
(49, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-05-28 20:46:53', NULL),
(50, 79, 'status', 'Changed establishment status to: inactive', 'update_status', NULL, '2025-05-28 20:46:53', NULL),
(51, 79, 'profile', 'Updated admin name', 'update_name', NULL, '2025-05-28 20:54:15', NULL),
(52, 79, 'profile', 'Updated admin email', 'update_email', NULL, '2025-05-28 20:54:15', NULL),
(53, 79, 'profile', 'Updated admin name', 'update_name', NULL, '2025-05-28 21:01:32', NULL),
(54, 79, 'profile', 'Updated admin email', 'update_email', NULL, '2025-05-28 21:01:32', NULL),
(55, 79, 'profile', 'Updated admin name', 'update_name', NULL, '2025-05-28 21:01:46', NULL),
(56, 79, 'profile', 'Updated admin email', 'update_email', NULL, '2025-05-28 21:01:46', NULL),
(57, 79, 'profile', 'Updated admin name', 'update_name', NULL, '2025-05-28 21:01:52', NULL),
(58, 79, 'profile', 'Updated admin email', 'update_email', NULL, '2025-05-28 21:01:52', NULL),
(59, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-05-28 21:21:14', NULL),
(60, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-05-28 21:21:14', NULL),
(61, 79, 'status', 'Changed establishment status to: maintenance', 'update_status', NULL, '2025-05-28 21:21:14', NULL),
(62, 79, 'queue', 'Accepted queue RCCS-001 for CCS', 'new_queue', 0, '2025-05-29 01:24:54', NULL),
(63, 79, 'queue', 'Accepted queue SCCS-001 for CCS', 'new_queue', 0, '2025-05-29 01:25:59', NULL),
(64, 79, 'queue', 'Accepted queue PCCS-001 for CCS', 'new_queue', 0, '2025-05-29 01:26:14', NULL),
(65, 79, 'queue', 'Accepted queue RCCS-002 for CCS', 'new_queue', 0, '2025-05-29 03:14:28', NULL),
(66, 79, 'queue', 'Accepted queue SCCS-002 for CCS', 'new_queue', 0, '2025-05-29 03:33:05', NULL),
(67, 79, 'queue', 'Accepted queue RCEAS-001 for CEAS', 'new_queue', 0, '2025-05-29 03:33:18', NULL),
(68, 79, 'queue', 'Accepted queue PCEAS-001 for CEAS', 'new_queue', 0, '2025-05-29 03:33:23', NULL),
(69, 79, 'service', 'Archived service: CBAAAAA', 'archive', 36, '2025-05-29 03:47:37', NULL),
(70, 79, 'queue', 'Accepted queue RCEAS-001 for CEAS', 'new_queue', 0, '2025-06-05 19:01:09', NULL),
(71, 79, 'queue', 'Accepted queue SCEAS-001 for CEAS', 'new_queue', 0, '2025-06-05 19:15:57', NULL),
(72, 79, 'establishment', 'Updated establishment description', 'update_description', NULL, '2025-06-05 19:24:14', NULL),
(73, 79, 'establishment', 'Updated establishment location', 'update_location', NULL, '2025-06-05 19:24:14', NULL),
(74, 79, 'status', 'Changed establishment status to: active', 'update_status', NULL, '2025-06-05 19:24:14', NULL),
(75, 79, 'queue', 'Accepted queue RCCS-001 for CCS', 'new_queue', 0, '2025-06-06 05:18:39', NULL),
(76, 79, 'queue', 'Accepted queue RCEAS-001 for CEAS', 'new_queue', 0, '2025-06-06 05:25:57', NULL),
(77, 79, 'queue', 'Accepted queue RCEAS-002 for CEAS', 'new_queue', 0, '2025-06-06 05:34:28', NULL),
(78, 79, 'profile', 'Updated profile picture', 'update_avatar', NULL, '2025-06-06 06:05:38', NULL),
(79, 79, 'queue', 'Accepted queue RCEAS-001 for CEAS', 'new_queue', 0, '2025-06-07 06:02:42', NULL),
(80, 79, 'queue', 'Accepted queue RCCS-002 for CCS', 'new_queue', 0, '2025-06-07 06:17:36', NULL),
(81, 79, 'queue', 'Accepted queue RCEAS-001 for CEAS', 'new_queue', 0, '2025-06-07 06:25:46', NULL),
(82, 79, 'queue', 'Accepted queue SCEAS-001 for CEAS', 'new_queue', 0, '2025-06-07 06:28:32', NULL),
(83, 79, 'queue', 'Accepted queue PCEAS-001 for CEAS', 'new_queue', 0, '2025-06-07 06:28:37', NULL),
(84, 79, 'queue', 'Accepted queue RCCS-001 for CCS', 'new_queue', 0, '2025-06-07 06:33:15', NULL),
(85, 79, 'queue', 'Accepted queue RCCS-002 for CCS', 'new_queue', 0, '2025-06-07 06:34:05', NULL),
(86, 79, 'queue', 'Accepted queue RCCS-003 for CCS', 'new_queue', 0, '2025-06-07 06:34:20', NULL),
(87, 79, 'queue', 'Accepted queue RCEAS-001 for CEAS', 'new_queue', 0, '2025-06-07 08:25:27', NULL),
(88, 79, 'queue', 'Accepted queue SCEAS-001 for CEAS', 'new_queue', 0, '2025-06-07 09:14:53', '2025-06-07 12:52:42'),
(89, 79, 'queue', 'Accepted queue SCEAS-002 for CEAS', 'new_queue', 0, '2025-06-07 09:14:57', '2025-06-07 12:20:57'),
(90, 79, 'queue', 'Accepted queue SCEAS-003 for CEAS', 'new_queue', 0, '2025-06-07 09:15:07', NULL),
(91, 79, 'queue', 'Accepted queue PCEAS-001 for CEAS', 'new_queue', 0, '2025-06-07 09:15:41', NULL),
(92, 79, 'queue', 'Accepted queue PCEAS-002 for CEAS', 'new_queue', 0, '2025-06-07 09:16:01', NULL),
(93, 79, 'service', 'Updated service: CCS', 'edit', 4, '2025-06-07 17:28:45', NULL),
(94, 79, 'service', 'Updated service: CCS', 'edit', 4, '2025-06-07 17:29:02', NULL),
(95, 79, 'queue', 'Accepted queue RCCS-004 for CCS', 'new_queue', 0, '2025-06-07 18:15:30', NULL),
(96, 79, 'queue', 'Accepted queue RCCS-005 for CCS', 'new_queue', 0, '2025-06-07 19:01:43', NULL),
(97, 79, 'queue', 'Accepted queue SCEAS-004 for CEAS', 'new_queue', 0, '2025-06-07 19:05:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `archived_queues`
--

CREATE TABLE `archived_queues` (
  `id` int(11) NOT NULL,
  `queue_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_first_name` varchar(25) DEFAULT NULL,
  `user_last_name` varchar(25) DEFAULT NULL,
  `queue_number` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `queue_type_id` int(11) DEFAULT NULL,
  `scheduled_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `serving_start_time` timestamp NULL DEFAULT NULL,
  `elapsed_time` time DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `restored_at` timestamp NULL DEFAULT NULL,
  `archive_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archived_queues`
--

INSERT INTO `archived_queues` (`id`, `queue_id`, `service_id`, `user_id`, `user_first_name`, `user_last_name`, `queue_number`, `status`, `queue_type_id`, `scheduled_time`, `created_at`, `serving_start_time`, `elapsed_time`, `archived_at`, `restored_at`, `archive_reason`) VALUES
(17, 351, 2, 55, NULL, NULL, 'RCEAS-001', 'completed', 1, NULL, '2025-05-29 03:33:18', '2025-06-05 18:38:24', '00:00:06', '2025-06-05 18:38:38', NULL, 'Manually archived by admin'),
(18, 356, 2, 55, NULL, NULL, 'RCEAS-001', 'completed', 1, NULL, '2025-06-06 05:25:57', '2025-06-06 05:29:18', '00:00:00', '2025-06-06 05:39:56', NULL, 'Manually archived by admin'),
(19, 357, 2, 55, NULL, NULL, 'RCEAS-002', 'completed', 1, NULL, '2025-06-06 05:34:28', '2025-06-06 05:39:57', '00:00:01', '2025-06-06 05:40:40', NULL, 'Manually archived by admin'),
(20, 360, 2, 55, NULL, NULL, 'RCEAS-001', 'cancelled', 1, NULL, '2025-06-07 06:25:46', NULL, '00:00:00', '2025-06-07 06:25:49', NULL, 'User cancelled queue'),
(21, 362, 2, 55, NULL, NULL, 'PCEAS-001', 'cancelled', 2, NULL, '2025-06-07 06:28:37', NULL, '00:00:00', '2025-06-07 06:28:44', NULL, 'User cancelled queue'),
(22, 361, 2, 55, NULL, NULL, 'SCEAS-001', 'cancelled', 3, '2025-06-07 14:02:00', '2025-06-07 06:28:32', NULL, '00:00:00', '2025-06-07 06:28:49', NULL, 'User cancelled queue'),
(23, 364, 4, 55, NULL, NULL, 'RCCS-002', 'cancelled', 1, NULL, '2025-06-07 06:34:05', NULL, '00:00:00', '2025-06-07 06:34:13', NULL, 'User cancelled queue'),
(24, 365, 4, 55, NULL, NULL, 'RCCS-003', 'cancelled', 1, NULL, '2025-06-07 06:34:20', NULL, '00:00:00', '2025-06-07 09:18:46', NULL, 'User cancelled queue'),
(25, 372, 4, 55, NULL, NULL, 'RCCS-004', 'cancelled', 1, NULL, '2025-06-07 18:15:30', NULL, '00:00:00', '2025-06-07 19:00:54', NULL, 'User cancelled queue'),
(26, 369, 2, 55, NULL, NULL, 'SCEAS-003', 'cancelled', 3, '2025-06-08 02:02:00', '2025-06-07 09:15:07', NULL, '00:00:00', '2025-06-07 19:00:59', NULL, 'User cancelled queue'),
(27, 368, 2, 55, NULL, NULL, 'SCEAS-002', 'cancelled', 3, '2025-06-10 02:02:00', '2025-06-07 09:14:57', NULL, '00:00:00', '2025-06-07 19:01:05', NULL, 'User cancelled queue'),
(28, 371, 2, 55, NULL, NULL, 'PCEAS-002', 'cancelled', 2, NULL, '2025-06-07 09:16:01', NULL, '00:00:00', '2025-06-07 19:01:13', NULL, 'User cancelled queue'),
(29, 367, 2, 55, NULL, NULL, 'SCEAS-001', 'cancelled', 3, '2025-06-08 02:02:00', '2025-06-07 09:14:53', NULL, '00:00:00', '2025-06-07 19:01:15', NULL, 'User cancelled queue');

-- --------------------------------------------------------

--
-- Table structure for table `archived_services`
--

CREATE TABLE `archived_services` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `hours_start` time DEFAULT NULL,
  `hours_end` time DEFAULT NULL,
  `max_queues` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `location` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `ticket_prefix` varchar(10) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `restored_at` timestamp NULL DEFAULT NULL,
  `archive_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archived_services`
--

INSERT INTO `archived_services` (`id`, `service_id`, `admin_id`, `name`, `description`, `hours_start`, `hours_end`, `max_queues`, `address`, `location`, `email`, `phone`, `ticket_prefix`, `archived_at`, `restored_at`, `archive_reason`) VALUES
(17, 41, 79, 'Abc', '', '11:11:00', '14:22:00', 900, '', 'room 204', 'kharl@gmail.com', '0938276283', '', '2025-05-27 16:13:21', NULL, 'Manual archive by admin'),
(18, 42, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:18:51', NULL, 'Manual archive by admin'),
(19, 43, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:19:00', NULL, 'Manual archive by admin'),
(20, 44, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:19:02', NULL, 'Manual archive by admin'),
(21, 45, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:19:34', NULL, 'Manual archive by admin'),
(22, 46, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:19:57', NULL, 'Manual archive by admin'),
(23, 47, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:19:59', NULL, 'Manual archive by admin'),
(24, 48, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:01', NULL, 'Manual archive by admin'),
(25, 49, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:02', NULL, 'Manual archive by admin'),
(26, 50, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:05', NULL, 'Manual archive by admin'),
(27, 53, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:06', NULL, 'Manual archive by admin'),
(28, 54, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:08', NULL, 'Manual archive by admin'),
(29, 51, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:09', NULL, 'Manual archive by admin'),
(30, 52, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:17', NULL, 'Manual archive by admin'),
(31, 56, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:19', NULL, 'Manual archive by admin'),
(32, 58, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:20', NULL, 'Manual archive by admin'),
(33, 55, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:22', NULL, 'Manual archive by admin'),
(34, 59, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:23', NULL, 'Manual archive by admin'),
(35, 57, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:24', NULL, 'Manual archive by admin'),
(36, 60, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:26', NULL, 'Manual archive by admin'),
(37, 62, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:28', NULL, 'Manual archive by admin'),
(38, 61, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:30', NULL, 'Manual archive by admin'),
(39, 63, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:32', NULL, 'Manual archive by admin'),
(40, 64, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:33', NULL, 'Manual archive by admin'),
(41, 65, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:35', NULL, 'Manual archive by admin'),
(42, 66, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:37', NULL, 'Manual archive by admin'),
(43, 67, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:20:39', NULL, 'Manual archive by admin'),
(44, 68, 79, 'kahrl', '', '11:11:00', '14:22:00', 909, '', 'room 293', 'kqj@gmail.com', '0983732212', '', '2025-05-27 17:51:07', NULL, 'Manual archive by admin'),
(45, 69, 79, 'kahrl', '', '11:11:00', '14:22:00', 909, '', 'room 293', 'kqj@gmail.com', '0983732212', '', '2025-05-27 17:51:21', NULL, 'Manual archive by admin'),
(47, 70, 79, 'kharl', '', '11:11:00', '14:22:00', 900, '', 'room 203', 'kharlryan@gmail.com', '09863527281', '', '2025-05-27 19:47:22', NULL, 'Manual archive by admin'),
(48, 72, 79, 'adc', '', '11:11:00', '15:33:00', 90, '', 'room 305', 'kharl03@gmail.com', '0983625621', '', '2025-05-28 05:27:45', NULL, 'Manual archive by admin'),
(49, 71, 79, 'kharl', '', '14:22:00', '15:33:00', 90, '', 'room 204', 'kharl@gmail.com', '0984736221', '', '2025-05-28 05:31:45', NULL, 'Manual archive by admin'),
(50, 73, 79, 'kharl', '', '11:11:00', '14:22:00', 90, '', 'room 204', 'kharl@gmail.com', '0947254113', '', '2025-05-28 17:03:52', NULL, 'Manual archive by admin'),
(51, 74, 79, 'kharl', '', '11:11:00', '14:22:00', 98, '', 'kharl', 'dee@gmail.com', '08937219329', '', '2025-05-28 18:23:48', NULL, 'Manual archive by admin'),
(53, 75, 79, 'kharl', '', '11:11:00', '14:22:00', 90, '', 'room 204', 'kharl@gmail.com', '0983467221', '', '2025-05-28 20:44:31', NULL, 'Manual archive by admin'),
(54, 36, 79, 'CBAAAAA', '', '14:02:00', '14:03:00', 100, '', 'Floor 205', 'eqwewq@gmail.com', '09048234233', '', '2025-05-29 03:47:37', NULL, 'Manual archive by admin');

-- --------------------------------------------------------

--
-- Table structure for table `archived_user_queues`
--

CREATE TABLE `archived_user_queues` (
  `id` int(11) NOT NULL,
  `queue_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `queue_number` varchar(50) NOT NULL,
  `queue_type_id` int(11) NOT NULL,
  `status` enum('completed','cancelled') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `scheduled_time` datetime DEFAULT NULL,
  `estimated_wait` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `serving_start_time` datetime DEFAULT NULL,
  `elapsed_time` varchar(8) DEFAULT '00:00:00',
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archive_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `establishments`
--

CREATE TABLE `establishments` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `queue_status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `location` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `building_type` varchar(100) NOT NULL,
  `hours_start` time NOT NULL,
  `hours_end` time NOT NULL,
  `avatar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `establishments`
--

INSERT INTO `establishments` (`id`, `admin_id`, `description`, `queue_status`, `location`, `address`, `building_type`, `hours_start`, `hours_end`, `avatar`, `created_at`, `updated_at`) VALUES
(7, 79, 'Hello', 'active', 'Olongapo', '88 A Kessing St.', 'School', '07:00:00', '21:00:00', NULL, '2025-05-19 03:47:44', '2025-06-07 09:47:27'),
(9, 82, 'Leading provider of IT services and solutions.', 'inactive', 'Makati', '123 Tech Avenue', 'Office Building', '09:00:00', '18:00:00', '/flow-application-cc/uploads/avatars/avatar_82_1747901385.png', '2025-05-22 07:26:36', '2025-05-28 07:38:05'),
(10, 83, 'Supplier of fresh produce and gardening essentials.', 'active', 'Cebu City', '456 Green Street', 'Retail Store', '08:00:00', '17:00:00', '/flow-application-cc/uploads/avatars/avatar_83_1747901488.jpg', '2025-05-22 07:26:36', '2025-05-22 08:27:15'),
(11, 84, 'Comprehensive medical and wellness services.', 'active', 'Davao City', '789 Health Plaza', 'Hospital', '07:00:00', '20:00:00', '/flow-application-cc/uploads/avatars/avatar_84_1747901583.png', '2025-05-22 07:26:36', '2025-05-22 08:27:24'),
(12, 85, 'A hub for groundbreaking research and development.', 'active', 'Quezon City', 'Innovation Park, Diliman', 'Research Center', '08:00:00', '19:00:00', NULL, '2025-05-22 07:26:50', '2025-05-22 08:27:30'),
(13, 86, 'Your premier destination for health and wellness.', 'active', 'Pasig', 'Ortigas Avenue', 'Gym/Fitness Center', '06:00:00', '22:00:00', NULL, '2025-05-22 07:26:50', '2025-05-22 08:27:37'),
(14, 87, 'Nurturing the next generation of culinary professionals.', 'active', 'Taguig', '123 Global Street', 'School', '09:00:00', '18:00:00', NULL, '2025-05-22 07:26:50', '2025-05-22 08:27:50');

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

CREATE TABLE `otp_verifications` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified` tinyint(1) DEFAULT 0,
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_verifications`
--

INSERT INTO `otp_verifications` (`id`, `email`, `otp`, `expires_at`, `created_at`, `verified`, `verified_at`) VALUES
(38, 'dejesukharl03@gmail.com', '$2y$10$NGlEiMwsbeJRlrBE7YK8x.hB93alDD0F4qDS2ZmMche6nda.O2bRm', '2025-05-26 00:35:24', '2025-05-25 16:30:24', 0, NULL),
(59, 'kharlryan03@gmail.com', '$2y$10$DHWyFZroM8cdPhG6JeCQOOa5C07vfGdW205KYn.hEUdaGy/7gHYYC', '2025-06-08 00:16:06', '2025-06-07 16:11:06', 1, '2025-06-08 00:11:24');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `queues`
--

CREATE TABLE `queues` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `queue_number` varchar(20) NOT NULL,
  `queue_type_id` int(11) NOT NULL,
  `status` enum('pending','waiting','serving','completed','declined') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `scheduled_time` datetime DEFAULT NULL,
  `estimated_wait` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `serving_start_time` timestamp NULL DEFAULT NULL,
  `elapsed_time` varchar(8) DEFAULT '00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queues`
--

INSERT INTO `queues` (`id`, `user_id`, `service_id`, `queue_number`, `queue_type_id`, `status`, `created_at`, `scheduled_time`, `estimated_wait`, `notes`, `updated_at`, `serving_start_time`, `elapsed_time`) VALUES
(363, 55, 4, 'RCCS-001', 1, 'completed', '2025-06-07 06:33:15', NULL, 0, '', '2025-06-07 06:33:42', '2025-06-07 06:33:40', '00:00:02'),
(366, 55, 2, 'RCEAS-001', 1, 'serving', '2025-06-07 08:25:27', NULL, 0, '', '2025-06-07 08:56:57', '2025-06-07 08:56:57', '00:00:00'),
(370, 55, 2, 'PCEAS-001', 2, 'pending', '2025-06-07 09:15:41', NULL, 0, 'Senior Citizen', '2025-06-07 09:15:41', NULL, '00:00:00'),
(373, 55, 4, 'RCCS-005', 1, 'pending', '2025-06-07 19:01:43', NULL, 0, '', '2025-06-07 19:01:43', NULL, '00:00:00'),
(374, 55, 2, 'SCEAS-004', 3, 'pending', '2025-06-07 19:05:59', '2025-06-08 02:02:00', 0, '', '2025-06-07 19:05:59', NULL, '00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `queue_counters`
--

CREATE TABLE `queue_counters` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `queue_type` varchar(10) NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue_counters`
--

INSERT INTO `queue_counters` (`id`, `service_id`, `queue_type`, `last_number`, `date`) VALUES
(1, 4, '1', 4, '2025-06-07'),
(4, 2, '1', 0, '2025-06-07'),
(5, 2, '3', 3, '2025-06-07'),
(8, 2, '2', 1, '2025-06-07');

-- --------------------------------------------------------

--
-- Table structure for table `queue_types`
--

CREATE TABLE `queue_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue_types`
--

INSERT INTO `queue_types` (`id`, `name`, `description`) VALUES
(1, 'regular', 'Standard queue for regular customers'),
(2, 'priority', 'Priority queue for seniors, PWD, and pregnant women'),
(3, 'scheduled', 'Pre-scheduled queue appointment');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `hours_start` time NOT NULL,
  `hours_end` time NOT NULL,
  `max_queues` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `ticket_prefix` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `admin_id`, `name`, `description`, `hours_start`, `hours_end`, `max_queues`, `address`, `location`, `email`, `phone`, `ticket_prefix`, `created_at`, `updated_at`, `is_archived`) VALUES
(2, 79, 'CEAS', '', '02:02:00', '23:11:00', 90, '', 'Room 204', 'ceas@gmail.com', '2353453312', '', '2025-05-19 11:20:20', '2025-05-27 15:51:09', 0),
(4, 79, 'CCS', '', '01:00:00', '15:00:00', 100, '', 'room 205', 'CCS@gmail.com', '0978981722', '', '2025-05-20 03:20:21', '2025-06-07 17:29:02', 0),
(6, 82, 'Technical Support', 'Assistance with software and hardware issues.', '09:00:00', '17:00:00', 50, '123 Tech Avenue, Makati City', 'IT Department', 'support@techsolutions.com', '0281234567', 'TS-TECH', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(7, 82, 'Client Consultations', 'Meeting with clients for project discussions.', '10:00:00', '16:00:00', 20, '123 Tech Avenue, Makati City', 'Meeting Room A', 'consult@techsolutions.com', '0281234568', 'TS-CON', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(8, 82, 'System Upgrades', 'Scheduling for system and network enhancements.', '09:00:00', '15:00:00', 15, '123 Tech Avenue, Makati City', 'Server Room', 'upgrades@techsolutions.com', '0281234569', 'TS-SYS', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(9, 82, 'Software Demos', 'Demonstrations of new software products.', '11:00:00', '16:00:00', 30, '123 Tech Avenue, Makati City', 'Demo Area', 'demos@techsolutions.com', '0281234570', 'TS-DEMO', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(10, 82, 'Hardware Repair', 'Repair services for computer hardware.', '09:00:00', '17:00:00', 25, '123 Tech Avenue, Makati City', 'Repair Shop', 'repair@techsolutions.com', '0281234571', 'TS-HARD', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(11, 82, 'Network Setup', 'Assistance with setting up new networks.', '10:00:00', '16:00:00', 10, '123 Tech Avenue, Makati City', 'Client Premises', 'network@techsolutions.com', '0281234572', 'TS-NET', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(12, 82, 'Data Recovery', 'Services for recovering lost data.', '09:00:00', '15:00:00', 10, '123 Tech Avenue, Makati City', 'Data Lab', 'data@techsolutions.com', '0281234573', 'TS-DATA', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(13, 82, 'Cloud Solutions', 'Consultation for cloud migration and services.', '10:00:00', '16:00:00', 20, '123 Tech Avenue, Makati City', 'Cloud Office', 'cloud@techsolutions.com', '0281234574', 'TS-CLOUD', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(14, 82, 'Cybersecurity Audit', 'Security assessment and recommendations.', '09:00:00', '17:00:00', 10, '123 Tech Avenue, Makati City', 'Security Office', 'security@techsolutions.com', '0281234575', 'TS-SEC', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(15, 82, 'IT Training', 'Training sessions for various IT skills.', '13:00:00', '17:00:00', 40, '123 Tech Avenue, Makati City', 'Training Room', 'training@techsolutions.com', '0281234576', 'TS-TRAIN', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(16, 83, 'Plant Sales', 'Purchase of various plants and saplings.', '08:00:00', '16:00:00', 100, '456 Green Street, Cebu City', 'Nursery Area', 'sales@greengardens.com', '0321234567', 'GG-PLANT', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(17, 83, 'Gardening Tools', 'Assistance with selecting gardening tools.', '08:00:00', '16:00:00', 50, '456 Green Street, Cebu City', 'Tools Section', 'tools@greengardens.com', '0321234568', 'GG-TOOL', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(18, 83, 'Pest Control Advice', 'Consultation on organic pest control.', '09:00:00', '15:00:00', 20, '456 Green Street, Cebu City', 'Consultation Desk', 'pest@greengardens.com', '0321234569', 'GG-PEST', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(19, 83, 'Fertilizer Recommendations', 'Recommendations for suitable fertilizers.', '08:00:00', '16:00:00', 40, '456 Green Street, Cebu City', 'Garden Supplies', 'fertilizer@greengardens.com', '0321234570', 'GG-FERT', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(20, 83, 'Landscape Design', 'Appointments for landscape design services.', '10:00:00', '14:00:00', 10, '456 Green Street, Cebu City', 'Design Office', 'design@greengardens.com', '0321234571', 'GG-LAND', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(21, 83, 'Potting Services', 'Assistance with repotting plants.', '09:00:00', '15:00:00', 30, '456 Green Street, Cebu City', 'Potting Station', 'potting@greengardens.com', '0321234572', 'GG-POT', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(22, 83, 'Workshop Registration', 'Registration for gardening workshops.', '10:00:00', '16:00:00', 60, '456 Green Street, Cebu City', 'Workshop Area', 'workshop@greengardens.com', '0321234573', 'GG-WORK', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(23, 83, 'Seed Collection', 'Browse and purchase various seeds.', '08:00:00', '16:00:00', 50, '456 Green Street, Cebu City', 'Seed Section', 'seeds@greengardens.com', '0321234574', 'GG-SEED', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(24, 83, 'Garden Maintenance Booking', 'Booking for garden maintenance services.', '09:00:00', '15:00:00', 15, '456 Green Street, Cebu City', 'Service Desk', 'maintenance@greengardens.com', '0321234575', 'GG-MAIN', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(25, 83, 'Compost and Soil', 'Purchase of organic compost and soil mixes.', '08:00:00', '16:00:00', 70, '456 Green Street, Cebu City', 'Compost Area', 'soil@greengardens.com', '0321234576', 'GG-SOIL', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(26, 84, 'General Check-up', 'Routine medical check-ups.', '07:00:00', '19:00:00', 80, '789 Health Plaza, Davao City', 'Clinic A', 'checkup@healthfirst.com', '0821234567', 'HF-GEN', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(27, 84, 'Dental Services', 'Dental consultations and procedures.', '08:00:00', '17:00:00', 30, '789 Health Plaza, Davao City', 'Dental Clinic', 'dental@healthfirst.com', '0821234568', 'HF-DEN', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(28, 84, 'Pediatric Care', 'Medical services for children.', '09:00:00', '16:00:00', 40, '789 Health Plaza, Davao City', 'Pediatric Ward', 'pedia@healthfirst.com', '0821234569', 'HF-PED', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(29, 84, 'Laboratory Services', 'Blood tests, urinalysis, etc.', '07:00:00', '18:00:00', 60, '789 Health Plaza, Davao City', 'Laboratory', 'lab@healthfirst.com', '0821234570', 'HF-LAB', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(30, 84, 'Vaccinations', 'Scheduling for various vaccinations.', '09:00:00', '16:00:00', 50, '789 Health Plaza, Davao City', 'Immunization Room', 'vaccine@healthfirst.com', '0821234571', 'HF-VAC', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(31, 84, 'Physical Therapy', 'Appointments for physical therapy sessions.', '08:00:00', '17:00:00', 25, '789 Health Plaza, Davao City', 'Therapy Center', 'pt@healthfirst.com', '0821234572', 'HF-PT', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(32, 84, 'Eye Care', 'Optometry and ophthalmology services.', '09:00:00', '18:00:00', 20, '789 Health Plaza, Davao City', 'Eye Clinic', 'eye@healthfirst.com', '0821234573', 'HF-EYE', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(33, 84, 'Mental Health Counseling', 'Counseling sessions for mental well-being.', '10:00:00', '19:00:00', 15, '789 Health Plaza, Davao City', 'Counseling Room', 'mentalhealth@healthfirst.com', '0821234574', 'HF-COUN', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(34, 84, 'Pharmacy Refills', 'Prescription refills and consultations.', '07:00:00', '20:00:00', 70, '789 Health Plaza, Davao City', 'Pharmacy', 'pharmacy@healthfirst.com', '0821234575', 'HF-PHARM', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(35, 84, 'Dermatology', 'Consultations for skin conditions.', '09:00:00', '17:00:00', 20, '789 Health Plaza, Davao City', 'Dermatology Clinic', 'derma@healthfirst.com', '0821234576', 'HF-DERMA', '2025-05-22 07:26:36', '2025-05-22 07:26:36', 0),
(36, 79, 'CBAAAAA', '', '14:02:00', '14:03:00', 100, '', 'Floor 205', 'eqwewq@gmail.com', '09048234233', '', '2025-05-24 07:35:58', '2025-05-29 03:47:37', 1),
(37, 79, 'abc', '', '23:11:00', '03:03:00', 100, '', 'romm 104', 'kharl@gmail.com', '093123812', '', '2025-05-26 14:21:57', '2025-05-27 13:10:02', 1),
(38, 79, 'asdf', '', '11:11:00', '14:22:00', 100, '', 'room123', 'de@gmail.com', '948234', '', '2025-05-26 14:41:03', '2025-05-27 14:03:36', 1),
(39, 79, 'adas', '', '11:11:00', '14:22:00', 9000, '', 'room 204', 'deje@gmail.com', '058349534', '', '2025-05-27 13:58:21', '2025-05-27 14:00:26', 1),
(40, 79, 'kharl', '', '11:11:00', '14:22:00', 900, '', 'Room 204', 'deks@gmail.com', '08472982', '', '2025-05-27 14:18:18', '2025-05-27 14:34:47', 1),
(41, 79, 'Abc', '', '11:11:00', '14:22:00', 900, '', 'room 204', 'kharl@gmail.com', '0938276283', '', '2025-05-27 15:45:57', '2025-05-27 16:13:21', 1),
(42, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:30', '2025-05-27 17:18:51', 1),
(43, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:34', '2025-05-27 17:19:00', 1),
(44, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:35', '2025-05-27 17:19:02', 1),
(45, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:35', '2025-05-27 17:19:34', 1),
(46, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:36', '2025-05-27 17:19:57', 1),
(47, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:37', '2025-05-27 17:19:59', 1),
(48, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:37', '2025-05-27 17:20:01', 1),
(49, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:38', '2025-05-27 17:20:02', 1),
(50, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:38', '2025-05-27 17:20:05', 1),
(51, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:38', '2025-05-27 17:20:09', 1),
(52, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:39', '2025-05-27 17:20:17', 1),
(53, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:39', '2025-05-27 17:20:06', 1),
(54, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:39', '2025-05-27 17:20:08', 1),
(55, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:14:40', '2025-05-27 17:20:22', 1),
(56, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:15:04', '2025-05-27 17:20:19', 1),
(57, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:15:05', '2025-05-27 17:20:24', 1),
(58, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:15:06', '2025-05-27 17:20:20', 1),
(59, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:18:24', '2025-05-27 17:20:23', 1),
(60, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:18:28', '2025-05-27 17:20:26', 1),
(61, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:18:28', '2025-05-27 17:20:30', 1),
(62, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:18:29', '2025-05-27 17:20:28', 1),
(63, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:18:31', '2025-05-27 17:20:32', 1),
(64, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:18:41', '2025-05-27 17:20:33', 1),
(65, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:18:41', '2025-05-27 17:20:35', 1),
(66, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:18:41', '2025-05-27 17:20:37', 1),
(67, 79, 'ASheera', '', '11:11:00', '14:22:00', 900, '', 'room294', 'dejksus@gmail.com', '0984940495', '', '2025-05-27 17:18:41', '2025-05-27 17:20:39', 1),
(68, 79, 'kahrl', '', '11:11:00', '14:22:00', 909, '', 'room 293', 'kqj@gmail.com', '0983732212', '', '2025-05-27 17:50:40', '2025-05-27 17:51:07', 1),
(69, 79, 'kahrl', '', '11:11:00', '14:22:00', 909, '', 'room 293', 'kqj@gmail.com', '0983732212', '', '2025-05-27 17:50:46', '2025-05-27 17:51:21', 1),
(70, 79, 'kharl', '', '11:11:00', '14:22:00', 900, '', 'room 203', 'kharlryan@gmail.com', '09863527281', '', '2025-05-27 18:34:34', '2025-05-27 19:47:22', 1),
(71, 79, 'kharl', '', '14:22:00', '15:33:00', 90, '', 'room 204', 'kharl@gmail.com', '0984736221', '', '2025-05-27 19:11:38', '2025-05-28 05:31:45', 1),
(72, 79, 'adc', '', '11:11:00', '15:33:00', 90, '', 'room 305', 'kharl03@gmail.com', '0983625621', '', '2025-05-27 19:48:04', '2025-05-28 05:27:45', 1),
(73, 79, 'kharl', '', '11:11:00', '14:22:00', 90, '', 'room 204', 'kharl@gmail.com', '0947254113', '', '2025-05-28 05:34:58', '2025-05-28 17:03:52', 1),
(74, 79, 'kharl', '', '11:11:00', '14:22:00', 98, '', 'kharl', 'dee@gmail.com', '08937219329', '', '2025-05-28 18:14:15', '2025-05-28 18:23:48', 1),
(75, 79, 'kharl', '', '11:11:00', '14:22:00', 90, '', 'room 204', 'kharl@gmail.com', '0983467221', '', '2025-05-28 19:49:32', '2025-05-28 20:44:31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `first_name` varchar(25) NOT NULL,
  `last_name` varchar(25) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `session_token` varchar(64) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `created_at`, `updated_at`, `first_name`, `last_name`, `role`, `session_token`, `avatar`) VALUES
(21, 'user01@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'John', 'Doe', 'user', NULL, NULL),
(22, 'user02@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Jane', 'Smith', 'user', NULL, NULL),
(23, 'user03@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Peter', 'Jones', 'user', NULL, NULL),
(24, 'user04@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Alice', 'Brown', 'user', NULL, NULL),
(25, 'user05@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Bob', 'White', 'user', NULL, NULL),
(26, 'user06@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Charlie', 'Green', 'user', NULL, NULL),
(27, 'user07@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Diana', 'Black', 'user', NULL, NULL),
(28, 'user08@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Eve', 'Grey', 'user', NULL, NULL),
(29, 'user09@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Frank', 'Blue', 'user', NULL, NULL),
(30, 'user10@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Grace', 'Red', 'user', NULL, NULL),
(31, 'user11@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Harry', 'Yellow', 'user', NULL, NULL),
(32, 'user12@example.com', '$2y$10$diqZ2iw2d77zrI4TvIb8sOPVTKvuYo3mjXe0yTnwMhYcakdjpxzFG', '2025-05-21 23:50:00', '2025-05-21 23:50:00', 'Ivy', 'Purple', 'user', NULL, NULL),
(46, 'user123@gmail.com', '$2y$10$oBXUpnoD2/Q75xpYssD/bOkhYVXCtch/2IH8ejQH/20SDncvDZ.VS', '2025-05-22 07:53:08', '2025-05-25 11:54:41', 'Kharl', 'Tamondong', 'user', 'cdf288838c3063dc58e5c91ac2a0b29b02b382ed1c9c910d9ba0221e4c5be796', '/secure_uploads/avatars/avatar_46_1748072630.jpg'),
(47, 'user@gmail.com', '$2y$10$YIbrAkjc0bZZWKKO2eHzD.1XC0uPcgpqttNFSv7ZPIRuQUKaTVV3y', '2025-05-22 08:54:10', '2025-05-22 08:54:16', 'user', 'user', 'user', '6d7febee790fc3e6e641bcc35b2671dd65d644e7c049f26d429306918ac64aa3', NULL),
(48, 'user12345@gmail.com', '$2y$10$rOUPrDUekgiz/zC7ssfJkeo0KdtWctY.ACcNZ6Pq/260D1IKHSRu2', '2025-05-24 06:40:50', '2025-05-24 06:41:25', 'kharl', 'De Jesus', 'user', '41648ef76f0c854a6491f6d38eaedf39e25697d911eebcfcf8bae885cff187ac', NULL),
(49, 'user123456@gmail.com', '$2y$10$rAVltgD442xdqFOQCu4ivumlBPihlGaPLwLCuLPN414gubWhovHnm', '2025-05-24 08:29:33', '2025-05-24 08:29:33', 'kharl', 'De Jesus', 'user', NULL, NULL),
(53, 'dejesuskharl32@gmail.com', '$2y$10$pOsI0AMHvcMNIKcrScGHNexT62lR0ElBd23wZZ56YFpGye4vWbpUK', '2025-05-25 03:55:31', '2025-06-07 17:00:49', 'kharl', 'De Jesus', 'user', '27f106c98bcd8b19160c7d1cdd11c82fc283af28d4aea5e70d978f08be1aa4fb', NULL),
(54, 'dejesukharl03@gmail.com', '$2y$10$QxA.PTFeJDIk7P4cma2t3uxOM5oYHV/yDUvyiSZ92tfDe41Pb5I1a', '2025-05-25 11:05:47', '2025-05-25 16:30:24', 'kharl', 'De Jesus', 'user', '34526084dcf24a1567e5beb636f7371b89b67789669584c631feff0790b37912', NULL),
(55, 'kharlryan03@gmail.com', '$2y$10$lUw8SizKpblWoZHJna/XCOavARlgx47cbW1UWEWUnsHk/CC6YBCkK', '2025-05-25 13:04:25', '2025-06-07 17:43:46', 'khar', 'dejesus', 'user', 'e6ac1a7900670f1d4bf4e02884ad3c331b899cb42e09cabe984674d1da2a1573', '/flow-application-cc/uploads/avatars/avatar_55_1749189961.jpg'),
(56, 'dejsuskharl0302@gmail.com', '$2y$10$iIky0Zl9xMrADfkAhdQrdOZ1PES/dj5o2laoD3ikJcCfPPV71B0K2', '2025-05-29 22:55:24', '2025-05-29 22:55:24', 'kharl', 'De Jesus', 'user', NULL, NULL),
(59, 'alleslosen03@gmail.com', '', '2025-06-07 17:09:45', '2025-06-07 17:09:45', 'Kharl', 'De Jesus', 'user', '01f6905d5895a36675f9a73e799e45b698bb6d2ca6099deb21b2e1b72992db1e', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `queue_number` varchar(20) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `archived_queues`
--
ALTER TABLE `archived_queues`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archived_services`
--
ALTER TABLE `archived_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archived_user_queues`
--
ALTER TABLE `archived_user_queues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_service_id` (`service_id`),
  ADD KEY `idx_queue_number` (`queue_number`),
  ADD KEY `idx_archived_at` (`archived_at`);

--
-- Indexes for table `establishments`
--
ALTER TABLE `establishments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `queues`
--
ALTER TABLE `queues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `queue_type_id` (`queue_type_id`);

--
-- Indexes for table `queue_counters`
--
ALTER TABLE `queue_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_counter` (`service_id`,`queue_type`,`date`);

--
-- Indexes for table `queue_types`
--
ALTER TABLE `queue_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_services_archived` (`is_archived`),
  ADD KEY `idx_services_admin_archived` (`admin_id`,`is_archived`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_read_at` (`read_at`),
  ADD KEY `service_id` (`service_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `archived_queues`
--
ALTER TABLE `archived_queues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `archived_services`
--
ALTER TABLE `archived_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `archived_user_queues`
--
ALTER TABLE `archived_user_queues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `establishments`
--
ALTER TABLE `establishments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `queues`
--
ALTER TABLE `queues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375;

--
-- AUTO_INCREMENT for table `queue_counters`
--
ALTER TABLE `queue_counters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `queue_types`
--
ALTER TABLE `queue_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `establishments`
--
ALTER TABLE `establishments`
  ADD CONSTRAINT `establishments_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `queues`
--
ALTER TABLE `queues`
  ADD CONSTRAINT `queues_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `queues_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `queues_ibfk_3` FOREIGN KEY (`queue_type_id`) REFERENCES `queue_types` (`id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`);

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_notifications_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
