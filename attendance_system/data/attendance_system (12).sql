-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 10:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attendance_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `checkin_time` time NOT NULL,
  `checkout_time` time DEFAULT NULL,
  `status` enum('ตรงเวลา','สาย') NOT NULL,
  `remark` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `checkin_time`, `checkout_time`, `status`, `remark`, `created_at`) VALUES
(1, 'USER_002', '10:22:00', '10:22:00', 'สาย', 'รถติด | ควยไรอะ', '2025-05-07 03:22:48');

-- --------------------------------------------------------

--
-- Table structure for table `calendar`
--

CREATE TABLE `calendar` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('holiday','event','leave') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendar`
--

INSERT INTO `calendar` (`id`, `date`, `title`, `description`, `type`) VALUES
(6, '2025-05-01', 'วันแรงงานแห่งชาติ', 'วันแรงงานแห่งชาติ วันพฤหัสบดี ที่ 01 พฤษภาคม 2568	', 'holiday'),
(7, '2025-05-05', 'ชดเชยวันฉัตรมงคล', 'วันจันทร์ ที่ 05 พฤษภาคม 2568 ชดเชยวันฉัตรมงคล (วันอาทิตย์ที่ 4 พฤษภาคม 2568)', 'holiday'),
(8, '2025-05-09', 'วันพืชมงคล', 'วันพืชมงคล วันศุกร์ ที่ 09 พฤษภาคม 2568	', 'holiday'),
(9, '2025-05-12', 'ชดเชยวันวิสาขบูชา ', 'ชดเชยวันวิสาขบูชา (วันอาทิตย์ที่ 11 พฤษภาคม 2568)', 'holiday');

-- --------------------------------------------------------

--
-- Table structure for table `duties`
--

CREATE TABLE `duties` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `duties`
--

INSERT INTO `duties` (`id`, `user_id`, `title`, `date`) VALUES
(11, 10, 'MRP', '2025-05-15');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_type` enum('ลากิจ','ลาป่วย','ลาพักร้อน','ลาบวช','ลาคลอด') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('รอดำเนินการ','อนุมัติ','ไม่อนุมัติ') DEFAULT 'รอดำเนินการ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `created_at`, `updated_at`) VALUES
(1, 'วันพฤหัสบดี ที่ 01 พฤษภาคม 2568', 'วันแรงงานแห่งชาติ', '2025-04-29 02:52:45', '2025-05-02 10:49:50'),
(2, 'วันจันทร์ ที่ 05 พฤษภาคม 2568', 'ชดเชยวันฉัตรมงคล (วันอาทิตย์ที่ 4 พฤษภาคม 2568)', '2025-04-29 02:57:00', '2025-05-02 10:57:02'),
(4, 'วันศุกร์ ที่ 09 พฤษภาคม 2568	', 'วันพืชมงคล วันศุกร์ ที่ 09 พฤษภาคม 2568	', '2025-05-02 03:45:08', '2025-05-06 15:22:58'),
(5, 'วันจันทร์ ที่ 12 พฤษภาคม 2568', 'ชดเชยวันวิสาขบูชา (วันอาทิตย์ที่ 11 พฤษภาคม 2568)', '2025-05-06 08:22:30', '2025-05-06 15:23:10');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('user_to_admin','admin_to_user') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`, `type`) VALUES
(6, 9, 'สถานะการลาของคุณถูกอนุมัติ', 0, '2025-05-06 04:11:46', 'admin_to_user'),
(7, 11, 'สถานะการลาของคุณถูกอนุมัติ', 0, '2025-05-06 08:09:01', 'admin_to_user'),
(8, 11, 'สถานะการลาของคุณถูกอนุมัติ', 0, '2025-05-06 08:16:50', 'admin_to_user');

-- --------------------------------------------------------

--
-- Table structure for table `ot_requests`
--

CREATE TABLE `ot_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ot_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('รอดำเนินการ','อนุมัติ','ปฏิเสธ') DEFAULT 'รอดำเนินการ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','superuser','user') NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `level` varchar(50) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `employee_id`, `fullname`, `position`, `level`, `profile_picture`) VALUES
(10, 'admin', '$2y$10$L4kn9zCCO8Hev2GJ0dBS4upWJJTcjMjOshBg.hraoFDSWswPJiPgG', 'admin', 'USER_001', 'สิทธิกร สาตราทอง', 'Dev', 'Superadminn', 'png-clipart-computer-icons-user-profile-avatar-child-face.png'),
(11, 'nice', '$2y$10$wF.1avQSQMBrCXWd4fM/Q.VQ/XCprtlPAm5u7n9rvlfSErFkcQ1Sm', 'user', 'USER_002', 'Nice', 'ผู้ช่วยพยาบาล', 'user', 'Basic_Ui__28186_29.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calendar`
--
ALTER TABLE `calendar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `duties`
--
ALTER TABLE `duties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ot_requests`
--
ALTER TABLE `ot_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `calendar`
--
ALTER TABLE `calendar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `duties`
--
ALTER TABLE `duties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ot_requests`
--
ALTER TABLE `ot_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ot_requests`
--
ALTER TABLE `ot_requests`
  ADD CONSTRAINT `ot_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
