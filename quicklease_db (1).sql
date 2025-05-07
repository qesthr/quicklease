-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 02:49 PM
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
  `customer_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `booking_date` date NOT NULL,
  `return_date` date NOT NULL,
  `preferences` text DEFAULT NULL,
  `status` enum('Active','Completed','Cancelled') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `users_id`, `customer_id`, `car_id`, `location`, `booking_date`, `return_date`, `preferences`, `status`, `created_at`) VALUES
(1, 3, 1, 1, 'Malaybalay City', '2024-05-10', '2024-05-15', 'GPS, Child Seat', 'Active', '2025-05-07 09:20:08'),
(7, 3, 2, 2, 'Los Angeles', '2025-06-01', '2025-06-07', 'Extra Insurance', 'Active', '2025-05-07 11:12:56');

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
  `features` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car`
--

INSERT INTO `car` (`id`, `model`, `plate_no`, `price`, `status`, `image`, `seats`, `transmission`, `mileage`, `features`) VALUES
(1, 'toyota', '123-qws', 1000.00, 'Available', '68190da42551c_fortuner.jpg', 5, 'automatic', 10000, 'ede sheng'),
(2, 'navarra', '123-asd', 1233.00, 'Available', '681b3fd654533_nissannavara.jpg', 12, 'automatic', 31241, '414dasdasd');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id` int(20) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(15) NOT NULL,
  `status` enum('Pending Approval','Approved','Rejected','') NOT NULL,
  `submitted_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`id`, `customer_name`, `customer_email`, `customer_phone`, `status`, `submitted_id`) VALUES
(1, 'joenil acero', '2301107552@student.buksu.edu.ph', '09856864187', 'Approved', '6817851853455_honndaaccord.jpg'),
(2, 'joenil acero', 'john@example.com', '09363034122', 'Approved', '');

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
-- Table structure for table `rental_period`
--

CREATE TABLE `rental_period` (
  `rp_ID` varchar(255) NOT NULL,
  `rp_bookingDay` date DEFAULT NULL,
  `rp_bookingTime` time DEFAULT NULL,
  `rp_rentDate` date DEFAULT NULL,
  `rp_rentTime` time DEFAULT NULL,
  `rp_returnDate` date DEFAULT NULL,
  `rp_returnTime` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `user_type` enum('admin','client') NOT NULL DEFAULT 'client',
  `reset_code` varchar(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `password`, `user_type`, `reset_code`, `created_at`) VALUES
(2, 'joenil', 'acero', 'joen', 'joenilacero20@gmail.com', '$2y$10$sgoPwgoL45hFvJs6KoZR3uJY1d00F0HRrYrnj.YrIWSWbhD/Drydq', 'admin', NULL, '2025-05-05 15:52:50'),
(3, 'joenil', 'panal', 'joenil', '2301107552@student.buksu.edu.ph', '$2y$10$APcuoUEQ.Lyk8s33Jug46e7rq549O4hFskhR2yJKoM11lvdmnmbfW', 'client', NULL, '2025-05-05 18:08:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `car`
--
ALTER TABLE `car`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_no` (`plate_no`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `rental_period`
--
ALTER TABLE `rental_period`
  ADD PRIMARY KEY (`rp_ID`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `car`
--
ALTER TABLE `car`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Count', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
