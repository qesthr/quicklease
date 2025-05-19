-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2025 at 12:02 AM
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
-- Database: `quicklease_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `booking_date` date NOT NULL,
  `return_date` date NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('Pending','Active','Completed','Cancelled') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `users_id`, `car_id`, `location`, `booking_date`, `return_date`, `phone`, `status`, `created_at`) VALUES
(27, 4, 13, 'Queen de los Reyes', '2025-05-19', '2025-05-26', NULL, 'Active', '2025-05-19 08:52:07'),
(28, 4, 13, 'Horhe Car Rental and Carwash', '2025-05-19', '2025-05-22', NULL, 'Active', '2025-05-19 09:05:57');

--
-- Triggers `bookings`
--
DELIMITER $$
CREATE TRIGGER `bookings_before_insert` BEFORE INSERT ON `bookings` FOR EACH ROW BEGIN
    IF NEW.return_date < NEW.booking_date THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Return date must be after or equal to booking date';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `bookings_before_update` BEFORE UPDATE ON `bookings` FOR EACH ROW BEGIN
    IF NEW.return_date < NEW.booking_date THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Return date must be after or equal to booking date';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `car`
--

CREATE TABLE `car` (
  `id` int(11) NOT NULL COMMENT 'Count',
  `model` varchar(255) NOT NULL,
  `plate_no` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `image` varchar(255) NOT NULL,
  `seats` int(11) NOT NULL,
  `transmission` varchar(50) NOT NULL,
  `mileage` int(11) NOT NULL,
  `features` text NOT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car`
--

INSERT INTO `car` (`id`, `model`, `plate_no`, `price`, `status`, `image`, `seats`, `transmission`, `mileage`, `features`, `category_id`) VALUES
(1, 'TOYOTA', '123-qws', 2500.00, 'Available', '68190da42551c_fortuner.jpg', 6, 'Automatic', 10000, 'With gps tracker', NULL),
(8, 'NAVVARA', '123-qwe', 2500.00, 'Available', '681cbceb7db36_nissannavara.jpg', 6, 'Automatic', 20000, 'With gps tracker', NULL),
(9, 'WIGO', '342-fhs', 1500.00, 'Available', '681cbd11b024c_wigo.jpg', 5, 'Automatic', 15000, 'With gps tracker', NULL),
(10, 'INNOVA', '234-asd', 1800.00, 'Available', '681cbd2f6700f_innova.jpg', 8, 'Automatic', 18000, 'With gps tracker and baby chair', NULL),
(11, 'MONTERO SPORTS', '312-fdas', 2500.00, 'Available', '681cbd8b649be_montero.jpg', 8, 'Manual', 25000, 'With gps tracker, Baby chair and top carrier', NULL),
(12, 'FORTUNER', '123-asd', 2500.00, 'Available', '681cbdaf5a3fc_fortuner.jpg', 8, 'Automatic', 22000, 'With gps tracker\r\n', NULL),
(13, 'MINIVAN', '100-asd', 1800.00, 'Available', '681cc1977212f_minivan.jpg', 8, 'Manual', 10000, 'With gps tracker and baby chair', NULL),
(14, 'HIACE', '809-khl', 3000.00, 'Available', '681cc1c791189_hiace.jpg', 10, 'Manual', 30000, 'With gps tracker and baby chair', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `car_categories`
--

CREATE TABLE `car_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `base_rate_multiplier` decimal(3,2) DEFAULT 1.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_categories`
--

INSERT INTO `car_categories` (`id`, `name`, `description`, `base_rate_multiplier`, `created_at`) VALUES
(1, 'Economy', 'Fuel-efficient compact cars perfect for city driving and small groups', 1.00, '2025-05-18 22:36:06'),
(2, 'Sedan', 'Comfortable mid-size cars ideal for families and business trips', 1.20, '2025-05-18 22:36:06'),
(3, 'SUV', 'Spacious vehicles suitable for rough terrain and large groups', 1.50, '2025-05-18 22:36:06'),
(4, 'Luxury', 'Premium vehicles offering superior comfort and style', 2.00, '2025-05-18 22:36:06'),
(5, 'Van', 'Large capacity vehicles perfect for group travel and cargo', 1.75, '2025-05-18 22:36:06'),
(6, 'Sports', 'High-performance vehicles for an exciting driving experience', 2.50, '2025-05-18 22:36:06');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `users_id`, `booking_id`, `message`, `notification_type`, `is_read`, `created_at`) VALUES
(1, 4, NULL, 'Status update for your minivan booking: Active', 'booking_status_update', 1, '2025-05-19 03:28:40'),
(2, 4, 27, 'Your booking for this car has been received and is pending approval.', 'booking_pending', 1, '2025-05-19 08:52:07'),
(3, 4, 28, 'Your booking for this car has been received and is pending approval.', 'booking_status_update', 1, '2025-05-19 09:05:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `customer_phone` varchar(15) NOT NULL,
  `submitted_id` varchar(255) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `status` enum('Pending Approval','Approved','Rejected','Verified''') NOT NULL,
  `user_type` enum('admin','client') NOT NULL DEFAULT 'client',
  `reset_code` varchar(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `password`, `customer_phone`, `submitted_id`, `profile_picture`, `status`, `user_type`, `reset_code`, `created_at`) VALUES
(3, 'joenil', 'pogi', 'joenil', '2301107552@student.buksu.edu.ph', '$2y$10$LdLhGn60mXfu5FxP.SPlxe8NFW6VutBxFYQG7jTNZGJimerbg7j/K', '09332472942', '1746794891_Screenshot 2025-05-05 010641.png', 'admin_3_1747691925.jpg', 'Approved', 'admin', NULL, '2025-05-05 18:08:50'),
(4, 'rayden', 'delfin', 'raydendelfin', 'joenilpanal@gmail.com', '$2y$10$16SBVXhLgw2fIYQAHD7rX.vtt9QgvJOifjJNGwQc06RLUYC6f4Fx2', '', '1747648230_Screenshot 2025-05-16 221033.png', '1747688265_rider.jpg', 'Approved', 'client', NULL, '2025-05-09 14:09:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `car`
--
ALTER TABLE `car`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_no` (`plate_no`),
  ADD KEY `fk_car_category` (`category_id`);

--
-- Indexes for table `car_categories`
--
ALTER TABLE `car_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `car`
--
ALTER TABLE `car`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Count', AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `car_categories`
--
ALTER TABLE `car_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `car` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `car`
--
ALTER TABLE `car`
  ADD CONSTRAINT `fk_car_category` FOREIGN KEY (`category_id`) REFERENCES `car_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
