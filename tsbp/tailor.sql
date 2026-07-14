-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2025 at 06:52 AM
-- Server version: 10.4.6-MariaDB
-- PHP Version: 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tailor`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `admin_id`, `action`, `description`, `ip_address`, `user_agent`, `timestamp`) VALUES
(646, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', '2025-05-06 16:16:38'),
(647, 4, 4, 'certificate', 'Certificate #CERT-202504-5769 regenerated for application #APP202565653', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', '2025-05-06 16:43:56'),
(648, 4, 4, 'certificate', 'Certificate #CERT-202504-5203 regenerated for application #APP202565653', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', '2025-05-06 16:46:20'),
(649, 5, 4, 'update', 'User Suleman Dahiru status changed to inactive', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', '2025-05-06 16:46:30'),
(650, 4, 4, 'approved', 'Application #APP202542129 approved by admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', '2025-05-06 16:47:49'),
(651, 4, 4, 'certificate', 'Certificate #CERT-202505-5314 generated for application #APP202542129', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', '2025-05-06 16:47:49'),
(652, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-06 18:17:35'),
(653, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-06 21:10:27'),
(654, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 01:58:04'),
(655, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 06:37:18'),
(656, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 08:14:24'),
(657, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 08:15:35'),
(658, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 09:22:00'),
(659, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 09:25:27'),
(660, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 09:25:36'),
(661, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 09:26:20'),
(662, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 11:03:17'),
(663, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 11:08:22'),
(664, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 11:33:14'),
(665, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 12:03:14'),
(666, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:28:32'),
(667, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:29:26'),
(668, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:29:35'),
(669, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:33:34'),
(670, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:34:33'),
(671, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:34:45'),
(672, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:35:53'),
(673, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:36:53'),
(674, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:37:12'),
(675, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:38:25'),
(676, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:38:32'),
(677, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 15:40:25'),
(678, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 16:44:34'),
(679, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 18:29:38'),
(680, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 18:33:27'),
(681, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 18:33:39'),
(682, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 18:33:56'),
(683, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 18:34:05'),
(684, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 18:41:30'),
(685, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 18:41:40'),
(686, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 18:42:38'),
(687, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 18:45:56'),
(688, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; U; Android 9; Infinix X626B Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/132.0.6834.163 Mobile Safari/537.36 OPR/89.0.2254.76420', '2025-05-07 18:50:46'),
(689, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 19:04:51'),
(690, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 19:05:43'),
(691, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 19:23:16'),
(692, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 20:28:02'),
(693, 3, NULL, 'tailor_profile_update', 'Tailor updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 20:48:51'),
(694, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 21:59:38'),
(695, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 23:27:17'),
(696, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 23:27:32'),
(697, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-07 23:42:18'),
(698, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 06:11:01'),
(699, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 06:21:03'),
(700, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; U; Android 9; Infinix X626B Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/132.0.6834.163 Mobile Safari/537.36 OPR/89.0.2254.76420', '2025-05-08 06:27:18'),
(701, NULL, 1, 'update_admin', 'Admin status updated for iyu9457@gmail.com to inactive', '127.0.0.1', 'Mozilla/5.0 (Linux; U; Android 9; Infinix X626B Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/132.0.6834.163 Mobile Safari/537.36 OPR/89.0.2254.76420', '2025-05-08 06:50:03'),
(702, NULL, 1, 'update_admin', 'Admin status updated for iyu9457@gmail.com to active', '127.0.0.1', 'Mozilla/5.0 (Linux; U; Android 9; Infinix X626B Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/132.0.6834.163 Mobile Safari/537.36 OPR/89.0.2254.76420', '2025-05-08 06:50:23'),
(703, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 07:18:51'),
(704, NULL, 1, 'profile_update', 'Admin updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 07:22:54'),
(705, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 08:39:53'),
(706, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 08:53:00'),
(707, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 12:19:19'),
(708, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-05-08 13:27:53'),
(709, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 14:38:17'),
(710, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 14:39:22'),
(711, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:19:14'),
(712, NULL, 1, 'profile_update', 'Admin updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:23:48'),
(713, NULL, 1, 'profile_update', 'Admin updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:23:57'),
(714, NULL, 1, 'profile_update', 'Admin updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:24:05'),
(715, NULL, 1, 'profile_update', 'Admin updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:25:07'),
(716, NULL, 1, 'profile_update', 'Admin updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:25:30'),
(717, NULL, 1, 'profile_update', 'Admin updated profile information', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-05-08 15:25:40'),
(718, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:38:54'),
(719, 3, NULL, 'tailor_profile_update', 'Tailor updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:39:48'),
(720, 3, NULL, 'tailor_profile_update', 'Tailor updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:41:47'),
(721, 3, NULL, 'tailor_profile_update', 'Tailor updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:42:19'),
(722, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:42:59'),
(723, NULL, 1, 'profile_update', 'Admin updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 15:43:10'),
(724, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 16:35:23'),
(725, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 21:18:23'),
(726, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 21:18:47'),
(727, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-08 22:22:19'),
(728, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36', '2025-05-09 12:21:50'),
(729, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-09 19:29:56'),
(730, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36', '2025-05-09 21:47:00'),
(731, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Mobile Safari/537.36', '2025-05-11 07:12:12'),
(732, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '2025-08-25 06:17:23'),
(733, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-08-25 06:31:36'),
(734, NULL, NULL, 'login', 'Tailor Aliyu umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-08-25 06:33:57'),
(735, 2, NULL, 'tailor_profile_update', 'Tailor updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-08-25 06:37:10'),
(736, 2, NULL, 'tailor_profile_update', 'Tailor updated profile information', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-08-25 06:42:07'),
(737, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '2025-08-25 07:00:48'),
(738, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-08-25 07:11:46'),
(739, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-08-25 09:18:40'),
(740, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-08-25 09:21:15'),
(741, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-08-25 09:22:03'),
(742, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-09-29 14:30:05'),
(743, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2025-10-01 16:41:44'),
(744, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2025-10-01 17:03:59'),
(745, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-10-02 06:26:39'),
(746, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-10-02 07:01:42'),
(747, NULL, 1, 'login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-10-02 07:02:50'),
(748, NULL, NULL, 'login', 'Tailor Hassan umar logged in', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-10-02 07:04:18');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','super_admin') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `fullname`, `email`, `phone`, `password`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'Administrator', 'admin@admin.com', '1234567890', '$2y$10$NHoeO7RPTDmjOtcbGqlMpu5HIixJVOIt.pNOQZmb2SMo8YG7HK5sO', 'super_admin', 'active', '2025-10-02 07:02:50', '2025-04-28 00:57:51'),
(2, 'Aliyu umar', 'iyu9457@gmail.com', '09060782817', '$2y$10$gaPkpC1uXRKtLKYd2CgrRupb5ULb72E/lul4OxLGsUXMFivxyEvV2', 'admin', 'active', NULL, '2025-04-28 01:28:24');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `message_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `sender_type` enum('customer','tailor','admin') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `read_status` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`message_id`, `order_id`, `sender_type`, `sender_id`, `message`, `attachment`, `read_status`, `created_at`) VALUES
(1, 6, 'customer', 2, 'Eieu', NULL, 1, '2025-05-07 10:13:30'),
(2, 6, 'customer', 2, 'Sent an attachment', '681b247726857_FB_IMG_17206865676584870.jpg', 1, '2025-05-07 10:14:31'),
(3, 6, 'customer', 2, '', '1746617445_FB_IMG_16932535834350471.jpg', 1, '2025-05-07 12:30:45'),
(4, 6, 'customer', 2, '', '1746617569_FB_IMG_16932535834350471.jpg', 1, '2025-05-07 12:32:49'),
(5, 5, 'tailor', 3, 'Slm', NULL, 1, '2025-05-07 12:59:34'),
(6, 5, 'customer', 2, 'Wslm', NULL, 1, '2025-05-07 13:00:32'),
(7, 5, 'tailor', 3, '', '1746619282_FB_IMG_17206865676584870.jpg', 1, '2025-05-07 13:01:22'),
(8, 5, 'tailor', 3, 'I have completed the public section of the application, where users can add products to their cart and place orders. They can also make successful payments using Flutterwave.\r\nThe admin section includes the following functionalities:\r\n* Admins can manage products (add, edit, delete).\r\n* Admins can manage sub-admins (add, edit, delete).\r\n* Admins can manage tailors (add, edit, delete).\r\n* There is an orders page where admins can manage customer orders.\r\n* Admins can assign a tailor to each order.\r\nFor the customer section, I have updated the consumer order page to include a chat button on each order card.\r\nFor the tailor interface, I have already implemented the login page, dashboard, header, sidebar,  footer, and tailor_orders.php', NULL, 1, '2025-05-07 13:17:43'),
(9, 5, 'customer', 2, 'I have completed the public section of the application, where users can add products to their cart and place orders. They can also make successful payments using Flutterwave.\r\nThe admin section includes the following functionalities:\r\n* Admins can manage products (add, edit, delete).\r\n* Admins can manage sub-admins (add, edit, delete).\r\n* Admins can manage tailors (add, edit, delete).\r\n* There is an orders page where admins can manage customer orders.\r\n* Admins can assign a tailor to each order.\r\nFor the customer section, I have updated the consumer order page to include a chat button on each order card.\r\nFor the tailor interface, I have already implemented the login page, dashboard, header, sidebar,  footer, and tailor_orders.php', NULL, 1, '2025-05-07 13:20:25'),
(10, 5, 'tailor', 3, 'Rrrt', NULL, 1, '2025-05-07 13:26:57'),
(11, 6, 'tailor', 3, 'Hi', NULL, 1, '2025-05-07 15:55:22'),
(12, 5, 'tailor', 3, '', '1746629903_logo1.png', 1, '2025-05-07 15:58:23'),
(13, 5, 'tailor', 3, '', '1746630986_Screenshot_20250507-155833.png', 1, '2025-05-07 16:16:26'),
(14, 5, 'tailor', 3, 'Slm', NULL, 1, '2025-05-07 17:03:32'),
(15, 6, 'customer', 2, '', '1746634007_1746617569_FB_IMG_16932535834350471.jpg', 1, '2025-05-07 17:06:47'),
(16, 5, 'tailor', 3, '', '1746634995_1746619282_FB_IMG_17206865676584870.jpg', 1, '2025-05-07 17:23:15'),
(17, 6, 'customer', 2, '', '1746635664_1746619282_FB_IMG_17206865676584870.jpg', 1, '2025-05-07 17:34:24'),
(18, 6, 'customer', 2, '', '1746638519_IMG-20250207-WA0001.jpg', 1, '2025-05-07 18:21:59'),
(19, 6, 'tailor', 3, 'Rtrt', NULL, 1, '2025-05-07 21:03:47'),
(20, 6, 'admin', 1, 'Hi', NULL, 1, '2025-05-07 22:00:05'),
(21, 6, 'customer', 2, 'Hi', NULL, 1, '2025-05-07 22:02:08'),
(22, 6, 'admin', 1, 'Ekrjr', NULL, 1, '2025-05-07 22:05:07'),
(23, 6, 'admin', 1, 'Hi', NULL, 1, '2025-05-07 22:27:45'),
(24, 6, 'customer', 2, 'Gh ood', NULL, 1, '2025-05-07 22:30:25'),
(25, 6, 'admin', 1, 'Eeueur', NULL, 1, '2025-05-07 22:32:22'),
(26, 6, 'admin', 1, '', '1746653582_1746603506728.jpg', 1, '2025-05-07 22:33:02'),
(27, 6, 'customer', 2, 'Slm', NULL, 1, '2025-05-07 22:51:30'),
(28, 6, 'customer', 2, '.message-content {\r\n    padding: 0.8rem 0.2rem;\r\n    border-radius: 18px;\r\n    position: relative;\r\n    word-wrap: break-word;\r\n    box-shadow: var(--shadow-sm);\r\n    transition: var(--transition);\r\n}', NULL, 1, '2025-05-07 23:07:10'),
(29, 6, 'customer', 2, '', '1746656722_1746603822662.jpg', 1, '2025-05-07 23:25:22'),
(30, 6, 'admin', 1, 'Slm', NULL, 1, '2025-05-08 12:51:43'),
(31, 6, 'customer', 2, 'Slm', NULL, 1, '2025-05-08 16:34:20'),
(32, 6, 'tailor', 3, 'Wslm', NULL, 1, '2025-05-08 16:36:20'),
(33, 6, 'customer', 2, 'Ya mgn aikina ka fara', NULL, 1, '2025-05-09 19:28:44'),
(34, 6, 'tailor', 3, 'Eh na fara', NULL, 1, '2025-05-09 19:30:42'),
(35, 6, 'customer', 2, 'Ok da kau', NULL, 1, '2025-05-09 21:47:59'),
(36, 6, 'tailor', 3, 'ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€šÃ‚Â¤Ãƒâ€šÃ‚Â£ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸Ãƒâ€šÃ‚Â¤Ãƒâ€šÃ‚Â£', NULL, 1, '2025-05-09 21:49:11'),
(37, 6, 'tailor', 3, 'Nice', NULL, 1, '2025-05-09 21:49:53'),
(38, 5, 'customer', 2, 'Slm', NULL, 1, '2025-05-09 22:54:45'),
(39, 6, 'customer', 2, 'Hi', NULL, 1, '2025-05-10 15:15:32'),
(40, 3, 'tailor', 2, 'Hi, I have completed, your order.', NULL, 0, '2025-08-25 07:09:47'),
(41, 7, 'customer', 1, 'Hi', NULL, 1, '2025-10-02 06:30:01'),
(42, 7, 'admin', 1, 'Hi', NULL, 1, '2025-10-02 06:53:21');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `address`, `city`, `state`, `zip_code`, `created_at`) VALUES
(1, 'Aliyu', 'Umar', 'customer1@customer.com', '$2y$10$NHoeO7RPTDmjOtcbGqlMpu5HIixJVOIt.pNOQZmb2SMo8YG7HK5sO', '09060782817', NULL, NULL, NULL, NULL, '2025-03-20 02:13:39'),
(2, 'Aliyu', 'Umar', 'customer2@gcustomer.com', '$2y$10$wkXXb0feHV0wxsBkOiyEZOwFeYeYgrWvMaKHqcHdBvIZqhRqmquey', '09060782817', 'Isa', 'Sokoto', 'Zamfara', '101338', '2025-03-21 00:29:14'),
(3, 'Ibrahim ', 'Abubakar Dan Ali ', 'iabubakardanali@gmail.com', '$2y$10$VRi9kxtigbuFQvdSCWPV6em83PYQtkfI1RfZhnp8QEe31/7YF/L/6', '09041302275', '', '', '', '', '2025-03-21 11:29:19'),
(4, 'ibrahim', 'abubakar dan ali', 'admin@admin.com', '$2y$10$CH8AKpVDQIArQS2CVAm5uOl2lARjRfcVit7FqAPesh94YMfmnxOH6', '00000000000', '', '', '', '', '2025-03-22 10:24:18'),
(5, 'usman', 'isa', 'usmanisa@gmail.com', '$2y$10$SaHZKtBza6OcGdGGvjI0luBd9UwC3TZi0wGhU85jlg21CcpNiI0kG', '09156107851', NULL, NULL, NULL, NULL, '2025-03-23 02:59:08'),
(6, 'sanusi', 'ibrahim', 'sanusiibrahim@gmail.com', '$2y$10$DM1ZSerf89jbWHTWiWvEW.nX2tBHEOe38XHW0k0j.m.tT.QE7GpjS', '9035536835', NULL, NULL, NULL, NULL, '2025-03-23 13:41:35'),
(7, 'Aliyu', 'Umar', 'customer3@gcustomer.com', '$2y$10$wkXXb0feHV0wxsBkOiyEZOwFeYeYgrWvMaKHqcHdBvIZqhRqmquey', '09060782817', NULL, NULL, NULL, NULL, '2025-05-06 17:54:43'),
(8, 'Aliyu ', 'Umar', 'au2@gmail.com', '$2y$10$H90AhzIFEGocQ8bDuXHepOZpwOHk9PHi7Z/NF28tUtxlH5//LeYWq', '12345678901', NULL, NULL, NULL, NULL, '2025-08-25 05:10:22'),
(9, 'Umar sani', 'Sani', 'user2@user.com', '$2y$10$0vpzjlQl1jKGQ9GnxCeWB.J67PLjAqa6WKYMQ818bnYTO6JItTCEW', '1234567890', NULL, NULL, NULL, NULL, '2025-08-28 19:53:38'),
(10, 'Aliyu ', 'Umar', 'au@au.com', '$2y$10$pTuiI6lmqiTd9dePBRwove08Bu6TMJ8864a1PNLMr0Vlm5TM.Gp8G', '1234567890', NULL, NULL, NULL, NULL, '2025-09-25 14:42:13');

-- --------------------------------------------------------

--
-- Table structure for table `customer_measurements`
--

CREATE TABLE `customer_measurements` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `neck` decimal(5,2) DEFAULT NULL,
  `chest` decimal(5,2) DEFAULT NULL,
  `shoulder` decimal(5,2) DEFAULT NULL,
  `sleeve` decimal(5,2) DEFAULT NULL,
  `bicep` decimal(5,2) DEFAULT NULL,
  `wrist` decimal(5,2) DEFAULT NULL,
  `waist` decimal(5,2) DEFAULT NULL,
  `hip` decimal(5,2) DEFAULT NULL,
  `inseam` decimal(5,2) DEFAULT NULL,
  `thigh` decimal(5,2) DEFAULT NULL,
  `knee` decimal(5,2) DEFAULT NULL,
  `ankle` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customer_measurements`
--

INSERT INTO `customer_measurements` (`id`, `customer_id`, `neck`, `chest`, `shoulder`, `sleeve`, `bicep`, `wrist`, `waist`, `hip`, `inseam`, `thigh`, `knee`, `ankle`, `notes`, `created_at`, `updated_at`) VALUES
(2, 2, 12.00, 12.00, 12.00, 12.00, 12.00, 12.00, 12.00, 12.00, 12.00, 12.00, 12.00, 12.00, NULL, '2025-05-07 11:10:00', '2025-05-10 11:35:23'),
(3, 1, 20.00, 20.00, 20.00, 25.00, NULL, NULL, 32.00, 38.00, 20.00, 30.00, NULL, NULL, 'Dirhd', '2025-08-25 06:34:53', '2025-10-01 17:53:10');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `payment_reference` varchar(100) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `delivery_city` varchar(50) DEFAULT NULL,
  `delivery_state` varchar(50) DEFAULT NULL,
  `delivery_zip` varchar(20) DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tailor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `payment_status`, `payment_reference`, `delivery_address`, `delivery_city`, `delivery_state`, `delivery_zip`, `delivery_instructions`, `created_at`, `tailor_id`) VALUES
(1, 2, 110000.00, 'completed', '8462836', 'Isa', 'Sokoto', 'Zamfara', '101338', '', '2025-03-22 15:11:57', NULL),
(2, 2, 100000.00, 'completed', 'cash', 'isa', 'isa', 'sokoto', '12345678', 'wertyu', '2025-03-22 16:01:18', NULL),
(3, 3, 3008.00, 'completed', '8463950', 'isa sabon gari', 'isa', 'Sokoto', '23456789', '', '2025-03-23 09:45:27', NULL),
(4, 3, 30000.00, 'completed', '8464199', 'isa sabon gari area', 'isa', 'Sokoto', '12345678', '', '2025-03-23 11:44:40', NULL),
(5, 2, 103008.00, 'completed', '8464889', 'Isa', 'Sokoto', 'Zamfara', '101338', '', '2025-03-23 20:40:23', NULL),
(6, 2, 57000.00, 'completed', '9238751', 'Isa', 'Sokoto', 'Zamfara', '101338', '', '2025-05-06 16:19:57', NULL),
(7, 1, 22500.00, 'completed', '9687219', 'Isa', 'Isa', 'Sokoto', '123456', '', '2025-10-01 20:08:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `color_id` int(11) DEFAULT NULL,
  `color_name` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `color_id`, `color_name`, `quantity`, `price`) VALUES
(1, 1, 2, NULL, NULL, 4, 20000.00),
(2, 1, 3, NULL, NULL, 1, 30000.00),
(3, 2, 7, NULL, NULL, 2, 50000.00),
(4, 3, 1, NULL, NULL, 1, 3008.00),
(5, 4, 85, NULL, NULL, 1, 30000.00),
(6, 5, 1, NULL, NULL, 1, 3008.00),
(10, 6, 1, NULL, NULL, 1, 7000.00),
(14, 5, 4, NULL, NULL, 1, 50000.00),
(15, 5, 4, NULL, NULL, 1, 50000.00),
(16, 6, 4, NULL, NULL, 1, 50000.00),
(17, 7, 1, 6, 'Dark Green ', 1, 7500.00),
(18, 7, 1, 5, 'Blue ', 2, 7500.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_tailor_assignments`
--

CREATE TABLE `order_tailor_assignments` (
  `assignment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `tailor_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assignment_date` datetime DEFAULT current_timestamp(),
  `status` enum('assigned','in_progress','completed','cancelled') DEFAULT 'assigned',
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_tailor_assignments`
--

INSERT INTO `order_tailor_assignments` (`assignment_id`, `order_id`, `tailor_id`, `assigned_by`, `assignment_date`, `status`, `due_date`, `notes`) VALUES
(3, 6, 3, 1, '2025-05-07 06:45:13', 'completed', '2025-05-31', ''),
(4, 5, 3, 1, '2025-05-07 09:21:47', 'completed', '2025-05-07', 'This '),
(5, 4, 4, 1, '2025-08-25 06:55:05', 'assigned', '2025-09-24', ''),
(6, 3, 2, 1, '2025-08-25 06:55:31', 'completed', '2025-09-26', ''),
(7, 2, 3, 1, '2025-08-25 06:55:54', 'completed', '2025-09-26', ''),
(8, 1, 3, 1, '2025-08-25 06:56:23', 'in_progress', '2025-08-30', ''),
(9, 7, 3, 1, '2025-10-02 06:29:22', 'assigned', '2025-10-09', '');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(9, 'customer1@gcustomer.com', '600bba0b2d8c44dce6bf513b70b888db4d74c80ec79e1d1af4b54510ecb19b31', '2025-09-29 15:25:46', '2025-08-28 19:50:50');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `category`, `image`) VALUES
(1, '7 Star ', 7500.00, 'Without yaddi dunki only', 'Yadis', 'IMG-20250502-WA0026.jpg'),
(2, 'Staro ', 20000.00, 'Dunki only', 'Yadis', 'IMG-20230608-WA0044.jpg'),
(3, '7 Star ', 30000.00, 'Complete ', 'Yadis', 'IMG-20250207-WA0001.jpg'),
(4, '7 Star', 50000.00, 'Complete ', 'Yadis', 'FB_IMG_16947616539598923.jpg'),
(6, '5 star ', 30000.00, 'Complete ', 'Yadis', 'Screenshot_20250430-061002.jpg'),
(7, 'Staror ', 50000.00, 'Without yaddi assigning only.', 'Shaddas', 'FB_IMG_17206865676584870.jpg'),
(8, 'Dan Abba', 40000.00, 'Complete', 'Yadis', 'FB_IMG_16932535834350471.jpg'),
(9, 'Galilah ', 30000.00, 'Complete dressing ', 'Shaddas', 'IMG-20250507-WA0036-1.jpg'),
(10, 'Singers', 50000.00, 'Complete dressing ', 'Shaddas', 'IMG-20250507-WA0004.jpg'),
(12, 'Meter Shedda', 30000.00, 'Complete dress ', 'Shaddas', 'IMG-20250507-WA0002.jpg'),
(13, 'Meter Shedda', 50000.00, 'Complete dress ', 'Kaftanis', 'IMG-20250507-WA0000.jpg'),
(16, 'Meter Shedda', 50000.00, 'Complete dress ', 'Yadis', 'IMG-20250507-WA0000.jpg'),
(17, 'Meter Shedda', 4000.00, 'Tailoring only ', 'Kaftanis', '1746603847942.jpg'),
(18, 'Kaftani', 30000.00, 'Complete dress ', 'Kaftanis', 'Screenshot_20240920-071903.png'),
(19, 'Dan Abbah', 50000.00, 'Complete ', 'Yadis', '1729203012733.jpg'),
(24, 'Dan Abbah', 30000.00, 'Complete ', 'Yadis', '1729202957969.jpg'),
(26, 'BAMA', 50000.00, 'Complete dress ', 'Yadis', '1729203002495.jpg'),
(27, 'Complete dress ', 50000.00, 'Complete dress ', 'Kaftanis', 'IMG-20250507-WA0008.jpg'),
(28, 'Protectional design ', 50000.00, 'Complete dress ', 'Shaddas', 'IMG-20250507-WA0035.jpg'),
(29, 'Complete dress ', 50000.00, 'Complete dress ', 'Shaddas', 'IMG-20250507-WA0001.jpg'),
(30, 'Sheddah', 50000.00, 'Complete dress ', 'Shaddas', '1746634007_1746617569_FB_IMG_16932535834350471.jpg'),
(31, 'Shaddah ', 50000.00, 'Complete dressing ', 'Shaddas', '1729203002495.jpg'),
(32, 'Kaftani', 50000.00, 'Dunki Only', 'Kaftanis', 'IMG-20250507-WA0035.jpg'),
(33, 'Shaddah', 50000.00, 'Complete dressing ', 'Kaftanis', '1746638519_IMG-20250207-WA0001.jpg'),
(36, 'Sheddah', 50000.00, 'Complete dress ', 'Shaddas', 'IMG-20250507-WA0002.jpg'),
(85, 'Shaddah ', 30000.00, 'Complete dressing ', 'Shaddas', 'Screenshot_20250508-162636.png');

-- --------------------------------------------------------

--
-- Table structure for table `product_colors`
--

CREATE TABLE `product_colors` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `color_code` varchar(7) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `product_colors`
--

INSERT INTO `product_colors` (`id`, `product_id`, `color_name`, `color_code`, `quantity`, `created_at`, `updated_at`) VALUES
(5, 1, 'Blue ', '#0000ff', 48, '2025-10-01 16:05:02', '2025-10-01 20:08:20'),
(6, 1, 'Dark Green ', '#044a09', 0, '2025-10-01 16:05:02', '2025-10-01 20:08:20');

-- --------------------------------------------------------

--
-- Table structure for table `tailors`
--

CREATE TABLE `tailors` (
  `tailor_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tailors`
--

INSERT INTO `tailors` (`tailor_id`, `fullname`, `email`, `phone`, `password`, `address`, `specialty`, `status`, `last_login`, `created_at`) VALUES
(2, 'Aliyu umar', 'tailor3@tailor.com', '09060782817', '$2y$10$wkXXb0feHV0wxsBkOiyEZOwFeYeYgrWvMaKHqcHdBvIZqhRqmquey', 'Sokoto, State', 'Telan Mata', 'active', '2025-08-25 06:33:57', '2025-05-07 02:03:29'),
(3, 'Hassan umar', 'tailor1@tailor.com', '09060782817', '$2y$10$Ascu60blewDOt6MMwCKH2u0x8XAguRuXpo5.Hi1Uxjn7KT9We7Taq', 'Wamakko Sokoto', 'Good', 'active', '2025-10-02 07:04:18', '2025-05-07 02:05:15'),
(4, 'Admin Sani', 'tailor2@tailor.com', '1234567890', '$2y$10$bkEJK3CVmE/ggEXWP3beSuxqvGwaJiollECP/TPUW2jhVa2m41TEq', 'Sokoto ', 'Telan Mata', 'active', NULL, '2025-08-25 06:48:02');

-- --------------------------------------------------------

--
-- Table structure for table `temp_orders`
--

CREATE TABLE `temp_orders` (
  `order_id` int(11) NOT NULL,
  `order_time` datetime NOT NULL,
  `order_status` tinyint(1) DEFAULT 0,
  `order_notes` text COLLATE utf8mb4_unicode_ci DEFAULT 'Service temporarily unavailable. Please contact support.',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `temp_orders`
--

INSERT INTO `temp_orders` (`order_id`, `order_time`, `order_status`, `order_notes`, `order_date`, `last_update`) VALUES
(1, '2035-09-30 15:34:00', 0, 'e2jTpP66toebptfbPpCeSkM8z9NtyRdXUfwmc366ZmcbWy0c6sBeW4gjLFcxNSiJ', '2025-08-25 08:09:16', '2025-10-01 15:40:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `customer_measurements`
--
ALTER TABLE `customer_measurements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `orders_ibfk_2` (`tailor_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `color_id` (`color_id`);

--
-- Indexes for table `order_tailor_assignments`
--
ALTER TABLE `order_tailor_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `tailor_id` (`tailor_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_password_resets_email` (`email`),
  ADD KEY `idx_password_resets_token` (`token`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `tailors`
--
ALTER TABLE `tailors`
  ADD PRIMARY KEY (`tailor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `temp_orders`
--
ALTER TABLE `temp_orders`
  ADD PRIMARY KEY (`order_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=749;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customer_measurements`
--
ALTER TABLE `customer_measurements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_tailor_assignments`
--
ALTER TABLE `order_tailor_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tailors`
--
ALTER TABLE `tailors`
  MODIFY `tailor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `temp_orders`
--
ALTER TABLE `temp_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_measurements`
--
ALTER TABLE `customer_measurements`
  ADD CONSTRAINT `fk_customer_measurements` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`tailor_id`) REFERENCES `tailors` (`tailor_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`color_id`) REFERENCES `product_colors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_tailor_assignments`
--
ALTER TABLE `order_tailor_assignments`
  ADD CONSTRAINT `order_tailor_assignments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_tailor_assignments_ibfk_2` FOREIGN KEY (`tailor_id`) REFERENCES `tailors` (`tailor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_tailor_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD CONSTRAINT `product_colors_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;