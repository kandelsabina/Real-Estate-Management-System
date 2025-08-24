-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 24, 2025 at 02:55 PM
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
-- Database: `realestate`
--

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `property_id`, `created_at`) VALUES
(11, 19, 3, '2025-07-26 09:24:16'),
(12, 19, 4, '2025-07-26 09:24:19');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `booking_type` varchar(50) NOT NULL,
  `type` enum('house','apartment','land','commercial') DEFAULT NULL,
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `area` int(11) DEFAULT NULL,
  `photo_paths` text DEFAULT NULL,
  `document_paths` text DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved` tinyint(1) DEFAULT 0,
  `availability_status` enum('available','sold','taken') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `title`, `description`, `location`, `latitude`, `longitude`, `price`, `booking_type`, `type`, `bedrooms`, `bathrooms`, `area`, `photo_paths`, `document_paths`, `seller_id`, `status`, `created_at`, `approved`, `availability_status`) VALUES
(3, '1BHK', '1BHK fully furnished flat', 'Mid-Baneshwor', 27.71030000, 85.32220000, 15000, 'sale', 'apartment', 1, 1, 2, 'uploads/photos/1748022839_jpg1.jpg,uploads/photos/1748022839_jpg2.jpg', 'uploads/docs/1748022839_proposal project.docx', 18, 'approved', '2025-05-23 17:53:59', 1, 'available'),
(4, '2bhk in sukedhara', '2bhk', 'Sukedhara', 27.72940000, 85.34670000, 20000, 'rent', 'house', 2, 1, 400, 'uploads/photos/1748248426_jpg6.jpg,uploads/photos/1748248426_jpg7.jpg,uploads/photos/1748248426_jpg8.jpg', 'uploads/docs/1748248426_proposal project.docx', 18, 'approved', '2025-05-26 08:33:46', 1, 'available'),
(6, '2BHK', 'This is a full furnished 2bhk', 'Chabahil', 27.71660000, 85.34850000, 22000, 'sale', 'house', 2, 1, 290, 'uploads/photos/1753370142_photo_jpg1.jpg,uploads/photos/1753370142_photo_jpg2.jpg,uploads/photos/1753370142_photo_jpg3.jpg,uploads/photos/1753370142_photo_jpg5.jpg,uploads/photos/1753370142_photo_jpg6.jpg,uploads/photos/1753370142_photo_jpg7.jpg', 'uploads/docs/1753370142_doc_proposal project.docx', 18, 'approved', '2025-07-24 15:15:42', 1, 'available'),
(7, '1bhk', 'This a 1bhk full furnished flat', 'Chabahil', 27.71660000, 85.34850000, 200000, 'sale', 'house', 1, 1, 100, 'uploads/photos/1753381795_jpg6.jpg,uploads/photos/1753381795_jpg7.jpg,uploads/photos/1753381795_jpg8.jpg', 'uploads/docs/1753381795_proposal project.docx', 18, 'approved', '2025-07-24 18:29:55', 1, 'sold'),
(8, '4bhk', 'This a 4bhk full furnished apartment near sukedhara', 'sukedhara', 27.71660000, 85.34850000, 25000, 'sale', 'apartment', 4, 1, 300, 'uploads/photos/1753382296_jpg2.jpg,uploads/photos/1753382296_jpg3.jpg,uploads/photos/1753382296_jpg6.jpg,uploads/photos/1753382296_jpg7.jpg', 'uploads/docs/1753382296_proposal project.docx', 18, 'approved', '2025-07-24 18:38:16', 1, 'available'),
(9, '2BHK Apartment', 'This is a full furnished apartment having 2 bedrooms.', 'Baneshwor', 27.69510000, 85.33517000, 20000, 'rent', 'apartment', 2, 2, 300, 'uploads/photos/1753674355_jpg3.jpg,uploads/photos/1753674355_jpg4.jpg,uploads/photos/1753674355_jpg5.jpg,uploads/photos/1753674355_jpg7.jpg,uploads/photos/1753674355_jpg8.jpg', 'uploads/docs/1753674355_proposal project.docx', 18, 'approved', '2025-07-28 03:45:55', 1, 'available'),
(10, '2BHK', 'This is a semi-furnished 2bhk apartment available in sukedhara', 'Sukedhara', 27.71660000, 85.34850000, 20000, 'lease', 'apartment', 2, 1, 200, 'uploads/photos/1754320599_images.jpeg,uploads/photos/1754320599_jpg1.jpg,uploads/photos/1754320599_jpg4.jpg,uploads/photos/1754320599_jpg5.jpg', 'uploads/docs/1754320599_proposal project.docx', 18, 'approved', '2025-08-04 15:16:39', 1, 'available'),
(11, '1BHK', 'This is a full furnished 1BHK with parking available', 'Baneshwor', 27.71030000, 85.32220000, 25000, 'Rent', 'apartment', 1, 2, 400, 'uploads/photos/1754708393_images.jpeg,uploads/photos/1754708393_jpg1.jpg,uploads/photos/1754708393_jpg4.jpg,uploads/photos/1754708393_jpg5.jpg', 'uploads/docs/1754708393_proposal project.docx', 18, 'approved', '2025-08-09 02:59:53', 1, 'available'),
(12, '2BHK', 'this is a fully furnished apartment', 'baneshwor', 27.71660000, 85.34850000, 20000, 'Sale', 'apartment', 2, 1, 200, 'uploads/photos/1754796861_images.jpeg,uploads/photos/1754796861_jpg4.jpg', 'uploads/docs/1754796861_proposal project.docx', 18, 'pending', '2025-08-10 03:34:21', 0, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `property_views`
--

CREATE TABLE `property_views` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `viewed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_views`
--

INSERT INTO `property_views` (`id`, `user_id`, `property_id`, `viewed_at`) VALUES
(2, 19, 3, '2025-07-24 20:09:27'),
(4, 19, 4, '2025-07-24 20:11:35'),
(5, 19, 6, '2025-07-25 00:12:37'),
(6, 19, 8, '2025-07-25 00:30:22'),
(7, 19, 7, '2025-07-27 00:57:59'),
(8, 19, 9, '2025-07-28 09:33:57'),
(12, 19, 10, '2025-08-04 21:03:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('buyer','seller','admin') NOT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `contact_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `otp_code`, `is_verified`, `status`, `created_at`, `contact_number`) VALUES
(12, 'Admin', 'kandelsabina111@gmail.com', '$2y$10$N2J0SwICzTWNOMIsEORrGul9.KHwvJoIUxiVA9V.vfPPiD4xst/gi', 'admin', '0', 1, 'approved', '2025-05-25 13:13:13', '9848542574'),
(18, 'SabinaKandel', 'kandelsabina24@gmail.com', '$2y$10$g5k8danfQJeUK/NheAJeKOnxYDy75F8dXMnI8z17SygL3PXZWhgwG', 'seller', '558929', 1, 'approved', '2025-05-25 13:04:11', '9848542574'),
(19, 'Sabina', 'kandelsabina9@gmail.com', '$2y$10$EOO9XgTjQdJOKu4Yzjjn1OIKdMkS6TETXd.gerIUr3JgGcVLnfu2W', 'buyer', '828080', 1, 'approved', '2025-05-25 13:16:33', '9848542574'),
(22, 'rajkandel', 'rajkandel954@gmail.com', '$2y$10$er6fFDKgcjMRl9RR1Oan/.KQGUPMRLfitNpzfDwAsrB8P2NdLAXCa', 'buyer', '783538', 1, 'approved', '2025-08-09 18:03:42', '9829846646');

-- --------------------------------------------------------

--
-- Table structure for table `visit_requests`
--

CREATE TABLE `visit_requests` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preferred_date` date NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'pending',
  `seller_response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visit_requests`
--

INSERT INTO `visit_requests` (`id`, `property_id`, `user_id`, `preferred_date`, `message`, `created_at`, `status`, `seller_response`) VALUES
(1, 3, 19, '2025-07-30', 'I am available after 5 pm can i visit this property after 5', '2025-07-29 13:29:15', 'reschedule_requested', 'Seller has requested rescheduling'),
(2, 3, 19, '2025-07-30', 'Can you show property after 5pm??', '2025-07-29 13:39:20', 'accepted', 'Visit accepted by seller');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`property_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `properties_ibfk_1` (`seller_id`);

--
-- Indexes for table `property_views`
--
ALTER TABLE `property_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_views_ibfk_2` (`property_id`),
  ADD KEY `property_views_ibfk_1` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `visit_requests`
--
ALTER TABLE `visit_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `property_views`
--
ALTER TABLE `property_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `visit_requests`
--
ALTER TABLE `visit_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_views`
--
ALTER TABLE `property_views`
  ADD CONSTRAINT `property_views_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_views_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visit_requests`
--
ALTER TABLE `visit_requests`
  ADD CONSTRAINT `visit_requests_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `visit_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
