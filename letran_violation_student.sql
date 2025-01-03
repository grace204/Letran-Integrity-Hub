-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2025 at 02:28 PM
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
-- Database: `letran_violation_student`
--

-- --------------------------------------------------------

--
-- Table structure for table `compliance`
--

CREATE TABLE `compliance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `violation_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `approval_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `background_image` varchar(255) NOT NULL,
  `favicon` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `capture_image` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `security_settings` varchar(5) DEFAULT NULL,
  `demo` int(1) NOT NULL,
  `passcode` varchar(255) NOT NULL,
  `auth_code` tinyint(1) DEFAULT 0,
  `sms_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `user_id`, `background_image`, `favicon`, `created_at`, `updated_at`, `capture_image`, `logo`, `security_settings`, `demo`, `passcode`, `auth_code`, `sms_url`) VALUES
(1, 1, '../assets/462646663_522929183854406_7548979948839859813_n.jpg', '../assets/52889902_2488780057802287_7045192790166208512_n.png', '2024-10-18 08:32:27', '2024-11-28 11:22:25', '0', '../assets/52889902_2488780057802287_7045192790166208512_n.png', '0', 0, '', 0, ''),
(2, 2, '../assets/123730838_10158886072914485_1171035478838908393_n.jpg', '../assets/52889902_2488780057802287_7045192790166208512_n.png', '2024-10-19 09:29:26', '2024-12-04 06:58:33', '0', '../assets/52889902_2488780057802287_7045192790166208512_n.png', '0', 0, '', 0, 'http://100.87.58.151:8080/v1/sms/');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `security_id` varchar(255) NOT NULL DEFAULT uuid(),
  `school_id` varchar(50) NOT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `middlename` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `suffix` varchar(50) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `student_email` varchar(100) DEFAULT NULL,
  `level` varchar(255) NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `security_id`, `school_id`, `student_name`, `middlename`, `lastname`, `suffix`, `contact`, `student_email`, `level`, `qr_code`, `user_id`) VALUES
(20, '897a3c6a-ad7c-11ef-b8ce-0045e2c10f9a', '17-01595', 'Carlos', 'Angala Fenuliar', 'Ragojos', 'Jr.', '09558923144', 'recardo09398658022@gmail.com', '', '../qr_codes/20.png', NULL),
(21, '8e4ff8aa-b1e0-11ef-8b61-0045e2c10f9a', '17-02246', 'Maria Jane', 'Esteron', 'Serrana', '', '09463181241', 'serranamjane@gmail.com', '', '../qr_codes/21.png', NULL),
(22, '2ec069b5-b202-11ef-873a-0045e2c10f9a', '17-02222', 'marmar', 'k', 'bas', '', '09666666663', 'marmarbas@gmail.com', '', '../qr_codes/22.png', NULL),
(24, 'f56ddaa4-b20a-11ef-97e0-0045e2c10f9a', '17-02004', 'atang', 'na', 'ba', '', '09939418078', 'atang@gmail.com', '', '../qr_codes/24.png', NULL),
(25, '4905ea84-b20b-11ef-97e0-0045e2c10f9a', '12-00002', 'Ben', 's', 'Abr', '', '09455822993', 'benabr@gmail.com', '', '../qr_codes/25.png', NULL),
(26, '3273511c-b20c-11ef-97e0-0045e2c10f9a', '12-02006', 'mc', 'esteron', 'abarabar', '', '09105148233', 'abarabarmcleo@gmail.com', '', '../qr_codes/26.png', NULL),
(27, 'f83a6155-b20c-11ef-97e0-0045e2c10f9a', '17-02222', 'mc', 'asd', 'asd', '', '09927706725', 'mc@gmail.com', '', '../qr_codes/27.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `school_id` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `level` varchar(255) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `visitor` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','staff','student') DEFAULT NULL,
  `middlename` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `barangay` varchar(255) DEFAULT '',
  `profile` varchar(255) DEFAULT NULL,
  `authenticator_secret` varchar(255) DEFAULT NULL,
  `capture_image_path` varchar(255) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `school_id`, `email`, `level`, `contact`, `visitor`, `name`, `password`, `role`, `middlename`, `lastname`, `suffix`, `age`, `birthdate`, `barangay`, `profile`, `authenticator_secret`, `capture_image_path`, `student_id`, `is_deleted`) VALUES
(1, '', 'ricardohaloglll@gmail.com', '', '09558923149', '', 'Ricardo', '2dafa360cdd10ad2fdb7199592fad49ccb60fcfe4a78ed6e362ccfd33bae7b9f', 'superadmin', 'Ragojos', 'Halog', 'Jr.', 24, '1999-11-11', 'Brgy. Letran', '52889902_2488780057802287_7045192790166208512_n.png', NULL, NULL, 1, 0),
(2, '', 'admin@gmail.com', '', '(075) 529 0121', '', 'Admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'admin', 'admin', 'min', '', 913, '1111-11-11', 'Baritao, Manaoag, Pangasinan', '52889902_2488780057802287_7045192790166208512_n.png', NULL, NULL, 2, 0),
(19, '', 'Staff@gmail.com', '', '', '', 'staff', '1562206543da764123c21bd524674f0a8aaf49c8a89744c97352fe677f7e4006', 'staff', 'staff', 'staff', '', 11111, '1111-11-11', 'barangay di ko alam', '52889902_2488780057802287_7045192790166208512_n.png', NULL, NULL, NULL, 0),
(20, '17-01595', 'recardo09398658022@gmail.com', '', '9558923144', '', 'Carlos', '2dafa360cdd10ad2fdb7199592fad49ccb60fcfe4a78ed6e362ccfd33bae7b9f', 'student', 'Angala Fenuliar', 'Ragojos', 'Jr.', 111111, '1111-11-11', 'Brgy. Pantal Manaoag Pangasinan', 'bubu-dudu-bubu.gif', NULL, NULL, 20, 0),
(21, '17-02246', 'serranamjane@gmail.com', '', '9463181241', '', 'Maria Jane', '1fa9097117dc96fc7b2507c48b8f7a24e25b023ea3499a9c1b977cb450373c6c', 'student', 'Esteron', 'Serrana', '', 22, '2002-05-08', 'Brgy. Oraan East Manaoag Pangasinan', '', NULL, NULL, 21, 0),
(22, '17-02222', 'marmarbas@gmail.com', '', '9666666663', '', 'marmar', 'd17f25ecfbcc7857f7bebea469308be0b2580943e96d13a3ad98a13675c4bfc2', 'student', 'k', 'bas', '', 21, '2111-07-05', 'Brgy. babasit', '', NULL, NULL, 22, 0),
(24, '17-02004', 'atang@gmail.com', '', '9939418078', '', 'atang', 'e7b237442cc205068a8bc0bf2accd1ea9a98c8ea2cbe719b04552d80f5534820', 'student', 'na', 'ba', '', 22, '2002-11-21', 'Brgy. cabanabn', '', NULL, NULL, 24, 0),
(25, '12-00002', 'benabr@gmail.com', '', '9455822993', '', 'Ben', 'd1ab289a30f541702aa3028e019cd8d7d6534db89475f56743be0df083cd7e10', 'student', 's', 'Abr', '', 27, '1997-05-14', 'Brgy. Baritao', '', NULL, NULL, 25, 0),
(26, '12-02006', 'abarabarmcleo@gmail.com', '', '9105148233', '', 'mc', 'aed6e0c6e68dbfdf39064b7fe464cc0b6441823dd0ed9616e9cb71b89646e79c', 'student', 'esteron', 'abarabar', '', 22, '2002-05-25', 'Brgy. cabanbanan', '', NULL, NULL, 26, 0),
(27, '17-02222', 'mc@gmail.com', '', '9927706725', '', 'mc', 'aed6e0c6e68dbfdf39064b7fe464cc0b6441823dd0ed9616e9cb71b89646e79c', 'student', 'asd', 'asd', '', 22, '2002-02-02', 'Brgy. cannfjasf', '', NULL, NULL, 27, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `role` varchar(100) NOT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `email`, `name`, `action`, `role`, `log_time`, `image_path`, `description`) VALUES
(86, 2, 'admin@gmail.com', 'ad', 'Logged in', 'admin', '2024-11-28 11:24:08', NULL, NULL),
(87, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-11-28 11:29:26', NULL, NULL),
(88, 20, 'recardo09398658022@gmail.com', 'Carlos', 'Logged in', 'student', '2024-11-28 11:33:23', NULL, NULL),
(89, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-11-28 11:37:35', NULL, NULL),
(90, 20, 'recardo09398658022@gmail.com', 'Carlos', 'Logged in', 'student', '2024-11-28 11:38:48', NULL, NULL),
(91, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-11-28 11:39:14', NULL, NULL),
(92, 20, 'recardo09398658022@gmail.com', 'Carlos', 'Logged in', 'student', '2024-11-28 11:40:09', NULL, NULL),
(93, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-11-28 11:40:37', NULL, NULL),
(94, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-11-28 11:41:21', NULL, NULL),
(95, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-11-28 11:41:35', NULL, NULL),
(96, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-11-28 11:48:02', NULL, NULL),
(97, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-11-29 12:32:30', NULL, NULL),
(98, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-11-29 12:42:00', NULL, NULL),
(99, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-12-04 01:09:42', NULL, NULL),
(100, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-12-04 01:11:42', NULL, NULL),
(101, 21, 'serranamjane@gmail.com', 'Maria Jane', 'Logged in', 'student', '2024-12-04 01:40:51', NULL, NULL),
(102, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-12-04 04:52:58', NULL, NULL),
(103, 2, 'jocelyngracegarcia06@gmail.com', 'Jocelyn Grace ', 'Logged in', 'admin', '2024-12-04 05:30:31', NULL, NULL),
(104, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 05:32:01', NULL, NULL),
(105, 22, 'marmarbas@gmail.com', 'marmar', 'Logged in', 'student', '2024-12-04 05:40:04', NULL, NULL),
(106, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 05:40:45', NULL, NULL),
(107, 22, 'marmarbas@gmail.com', 'marmar', 'Logged in', 'student', '2024-12-04 05:41:38', NULL, NULL),
(108, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 05:42:21', NULL, NULL),
(109, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 05:44:57', NULL, NULL),
(110, 22, 'marmarbas@gmail.com', 'marmar', 'Logged in', 'student', '2024-12-04 05:46:29', NULL, NULL),
(111, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 05:48:00', NULL, NULL),
(112, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 05:52:30', NULL, NULL),
(113, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 05:59:20', NULL, NULL),
(114, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 06:23:58', NULL, NULL),
(115, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 06:38:08', NULL, NULL),
(116, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 06:42:50', NULL, NULL),
(117, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 06:45:16', NULL, NULL),
(118, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 06:51:44', NULL, NULL),
(119, 26, 'abarabarmcleo@gmail.com', 'mc', 'Logged in', 'student', '2024-12-04 06:55:14', NULL, NULL),
(120, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 06:57:18', NULL, NULL),
(121, 26, 'abarabarmcleo@gmail.com', 'mc', 'Logged in', 'student', '2024-12-04 07:08:35', NULL, NULL),
(122, 26, 'abarabarmcleo@gmail.com', 'mc', 'Logged in', 'student', '2024-12-04 11:34:51', NULL, NULL),
(123, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 11:37:24', NULL, NULL),
(124, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 11:43:12', NULL, NULL),
(125, 19, 'Staff@gmail.com', 'staff', 'Logged in', 'staff', '2024-12-04 12:01:40', NULL, NULL),
(126, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-04 12:07:55', NULL, NULL),
(127, 26, 'abarabarmcleo@gmail.com', 'mc', 'Logged in', 'student', '2024-12-04 12:15:29', NULL, NULL),
(128, 19, 'Staff@gmail.com', 'staff', 'Logged in', 'staff', '2024-12-04 12:41:11', NULL, NULL),
(129, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2024-12-05 15:28:24', NULL, NULL),
(130, 26, 'abarabarmcleo@gmail.com', 'mc', 'Logged in', 'student', '2024-12-05 15:29:19', NULL, NULL),
(131, 26, 'abarabarmcleo@gmail.com', 'mc', 'Logged in', 'student', '2024-12-21 03:52:13', NULL, NULL),
(132, 2, 'admin@gmail.com', 'Admin', 'Logged in', 'admin', '2025-01-03 13:23:06', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `expiry_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `login_time`, `expiry_time`) VALUES
(92, 20, 'lttt9fngl5lo9orkhnni70746s', '2024-11-28 19:40:09', '2024-11-28 21:40:09'),
(101, 21, 's49604jdlic2ppqv7vm58oqj5n', '2024-12-04 09:40:51', '2024-12-04 11:40:51'),
(110, 22, '94b751vdeh7rtblevsnb818tam', '2024-12-04 13:46:29', '2024-12-04 15:46:29'),
(128, 19, 'u952lljrbk3jm25le3fv43i1b2', '2024-12-04 20:41:11', '2024-12-04 22:41:11'),
(131, 26, '1sfrf9e7g9ndpv0a3admi43j24', '2024-12-21 11:52:13', '2024-12-21 13:52:13'),
(132, 2, '1q0j0tcchd8q2bccg351dvncvv', '2025-01-03 21:23:06', '2025-01-03 23:23:06');

-- --------------------------------------------------------

--
-- Table structure for table `violations`
--

CREATE TABLE `violations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `violation_type` varchar(50) DEFAULT NULL,
  `level` varchar(255) NOT NULL,
  `violation_description` text DEFAULT NULL,
  `violation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `compliance_response` text DEFAULT NULL,
  `compliance_status` enum('Approved','Rejected','Pending') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `violations`
--

INSERT INTO `violations` (`id`, `student_id`, `violation_type`, `level`, `violation_description`, `violation_date`, `compliance_response`, `compliance_status`) VALUES
(11, 20, 'Child Abuse', '', 'Mr. Ragojos ano yan?', '2024-11-28 11:38:20', 'malay ko po sayo sir hahaha', 'Approved'),
(12, 20, 'No I\'D', '', 'Naiwan', '2024-12-04 05:34:39', NULL, 'Pending'),
(13, 22, 'No I\'D', '', 'nahulog', '2024-12-04 05:41:14', NULL, 'Pending'),
(14, 20, 'no id', '', 'naiwan', '2024-12-04 06:38:33', NULL, 'Pending'),
(15, 25, 'masyadong maganda', '', 'hnd makasalanan', '2024-12-04 06:46:45', NULL, 'Pending'),
(16, 24, 'masyadong maganda', '', 'wow', '2024-12-04 06:48:47', NULL, 'Pending'),
(17, 24, 'masyadong maganda', '', 'mafhahdfhad', '2024-12-04 06:50:07', NULL, 'Pending'),
(18, 26, 'masyadong maganda', '', 'ashfafsa', '2024-12-04 06:52:02', NULL, 'Pending'),
(19, 26, 'No I\'D', '', 'sdgsdgsd', '2024-12-04 06:53:59', NULL, 'Pending'),
(20, 27, 'masyadong maganda', '', 'asdasdad', '2024-12-04 06:57:36', NULL, 'Pending'),
(21, 26, 'dfs', '', 'ssgs\r\n', '2024-12-04 06:58:53', NULL, 'Pending'),
(22, 25, 'Unexcused Absence', 'college', 'dasdasd', '2025-01-03 13:26:48', NULL, 'Pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `compliance`
--
ALTER TABLE `compliance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `violation_id` (`violation_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `violations`
--
ALTER TABLE `violations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `compliance`
--
ALTER TABLE `compliance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `violations`
--
ALTER TABLE `violations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `compliance`
--
ALTER TABLE `compliance`
  ADD CONSTRAINT `compliance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `compliance_ibfk_2` FOREIGN KEY (`violation_id`) REFERENCES `violations` (`id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
