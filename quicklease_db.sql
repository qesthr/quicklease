-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2025 at 06:50 PM
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
(1, 3, 1, 'Malaybalay City', '2024-05-10', '2024-05-15', 'GPS, Child Seat', 'Pending', '2025-05-07 09:20:08'),
(23, 3, 8, 'dawdaw', '2025-05-09', '2025-05-10', '', 'Active', '2025-05-08 21:07:49'),
(24, 1, 12, 'Shyne\'s Car Rental, 44PG+V8Q, Malaybalay, Bukidnon', '2025-05-10', '2025-05-12', NULL, 'Completed', '2025-05-09 16:06:33');

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

INSERT INTO `car` (`id`, `model`, `plate_no`, `price`, `status`, `image`, `seats`, `transmission`, `mileage`, `features`) VALUES
(1, 'toyota', '123-qws', 1000.00, 'Rented', '68190da42551c_fortuner.jpg', 342, 'automatic', 10000, 'ede sheng'),
(8, 'navarra', '123-qwe', 123.00, 'Pending', '681cbceb7db36_nissannavara.jpg', 2, 'manual', 12313, '1312312'),
(9, 'wigo', '342-fhs', 4354.00, 'Rented', '681cbd11b024c_wigo.jpg', 5, 'automatic', 345345, '353453'),
(10, 'Innova', '234-asd', 3123.00, 'Maintenance', '681cbd2f6700f_innova.jpg', 12, 'manual', 3123123, 'fasdfsaasdva'),
(11, 'montero sport', '312-fdas', 32123.00, 'Maintenance', '681cbd8b649be_montero.jpg', 121, 'automatic', 123123123, 'fasdasdas'),
(12, 'fortuner', '123-asd', 4214.00, 'Pending', '681cbdaf5a3fc_fortuner.jpg', 21, 'automatic', 4142313, 'fsadfasdfasdfa'),
(13, 'minivan', '100-asd', 1250.00, 'Available', '681cc1977212f_minivan.jpg', 10, 'manual', 10000, 'gamay nga bann'),
(14, 'hiace', '809', 2500.00, 'Available', '681cc1c791189_hiace.jpg', 18, 'automatic', 10000, 'dako nga ban');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `customer_id` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `customer_id`, `message`, `is_read`, `created_at`) VALUES
(1, '1', 'Your booking has been approved.', 0, '2025-05-07 07:19:05'),
(2, '1', 'Your profile has been updated successfully.', 0, '2025-05-07 07:19:05'),
(3, '1', 'Your booking has been rejected.', 1, '2025-05-07 07:19:05'),
(4, '1', 'Your booking has been approved.', 0, '2025-05-07 10:22:09');

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
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('Pending Approval','Approved','Rejected','Verified') NOT NULL,
  `user_type` enum('admin','client') NOT NULL DEFAULT 'client',
  `reset_code` varchar(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `password`, `customer_phone`, `submitted_id`, `profile_picture`, `status`, `user_type`, `reset_code`, `created_at`) VALUES
(1, 'joemar', 'acero', 'joemaracero', 'joenilacero20@gmail.com', '$2y$10$l72/7sQ0T7HR95nP40DYn.8IGqxH0dxdRm/c7lUNx1sygkaJ4FGlG', '', '', NULL, 'Approved', 'client', NULL, '2025-05-08 20:33:42'),
(3, 'joenil', 'pogi', 'joenil', '2301107552@student.buksu.edu.ph', '$2y$10$APcuoUEQ.Lyk8s33Jug46e7rq549O4hFskhR2yJKoM11lvdmnmbfW', '09332472942', '1746794891_Screenshot 2025-05-05 010641.png', NULL, 'Approved', 'admin', NULL, '2025-05-05 18:08:50'),
(4, 'rayden', 'delfin', 'raydendelfin', 'joenilpanal@gmail.com', '$2y$10$16SBVXhLgw2fIYQAHD7rX.vtt9QgvJOifjJNGwQc06RLUYC6f4Fx2', '', '', NULL, 'Pending Approval', 'client', NULL, '2025-05-09 14:09:43');

-- --------------------------------------------------------

--
-- Table structure for table `car_categories`
--

CREATE TABLE `car_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `base_rate_multiplier` decimal(3,2) DEFAULT '1.00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_categories`
--

INSERT INTO `car_categories` (`name`, `description`, `base_rate_multiplier`) VALUES
('Economy', 'Fuel-efficient compact cars perfect for city driving and small groups', 1.00),
('Sedan', 'Comfortable mid-size cars ideal for families and business trips', 1.20),
('SUV', 'Spacious vehicles suitable for rough terrain and large groups', 1.50),
('Luxury', 'Premium vehicles offering superior comfort and style', 2.00),
('Van', 'Large capacity vehicles perfect for group travel and cargo', 1.75),
('Sports', 'High-performance vehicles for an exciting driving experience', 2.50);

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
  ADD UNIQUE KEY `plate_no` (`plate_no`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `car`
--
ALTER TABLE `car`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Count', AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  ADD CONSTRAINT `fk_car_category` FOREIGN KEY (`category_id`) REFERENCES `car_categories`(`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
