SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `lease_agreements` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `rent_amount` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `lease_agreements` (`id`, `property_id`, `tenant_id`, `manager_id`, `start_date`, `end_date`, `rent_amount`, `status`, `created_at`) VALUES
(1, 15, 4, 9, '2026-03-06', '2027-04-07', 26000.00, 'Signed', '2026-06-08 07:31:36');

CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `issue` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `maintenance` (`id`, `tenant_id`, `property_id`, `issue`, `status`, `created_at`) VALUES
(1, 4, 1, 'water leaking issue', 'pending', '2026-06-05 17:44:26'),
(2, 4, 2, 'light issues', 'pending', '2026-06-05 18:26:12'),
(3, 4, 2, 'light issues', 'pending', '2026-06-06 06:17:12');

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT 'PayFast'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `payments` (`id`, `tenant_id`, `property_id`, `amount`, `status`, `payment_date`, `payment_method`) VALUES
(1, 4, 1, 100, 'Paid', '2026-06-07 13:01:24', 'PayFast'),
(2, 4, 1, 100, 'Paid', '2026-06-07 16:42:25', 'PayFast');

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `status` enum('available','occupied') DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `properties` (`id`, `title`, `location`, `price`, `status`, `manager_id`, `created_at`, `Image`) VALUES
(1, 'Modern Flat', 'Civil Lines Gujrat', 26000, 'available', 1, '2026-06-04 10:43:01', ''),
(2, 'Family House', 'Peoples Colony Gujrat', 35000, 'available', 1, '2026-06-04 10:43:01', ''),
(3, 'Studio Apartment', 'Satellite Town Gujrat', 18000, 'available', 1, '2026-06-04 10:43:01', ''),
(4, 'Upper Portion', 'Model Town Gujrat', 22000, 'occupied', 1, '2026-06-04 10:43:01', ''),
(5, 'Single Storey House', 'Rehmat Pura Gujrat', 30000, 'available', 1, '2026-06-04 10:43:01', ''),
(6, 'Double Storey House', 'Gulshan Colony Gujrat', 45000, 'available', 1, '2026-06-04 10:43:01', ''),
(7, 'Flat', 'Jinnah Colony Gujrat', 15000, 'occupied', 1, '2026-06-04 10:43:01', ''),
(8, 'Shop', 'GT Road Gujrat', 20000, 'available', 1, '2026-06-04 10:43:01', ''),
(9, 'Modern Flat', 'Civil Lines Gujrat', 25000, 'available', 1, '2026-06-04 10:45:52', ''),
(10, 'Family House', 'Peoples Colony Gujrat', 35000, 'available', 1, '2026-06-04 10:45:52', ''),
(11, 'Studio Apartment', 'Satellite Town Gujrat', 18000, 'available', 1, '2026-06-04 10:45:52', ''),
(12, 'Upper Portion', 'Model Town Gujrat', 22000, 'occupied', 1, '2026-06-04 10:45:52', ''),
(13, 'Single Storey House', 'Kharian', 20000, 'available', 1, '2026-06-04 10:45:52', ''),
(14, 'Double Storey House', 'Lalamusa', 28000, 'available', 1, '2026-06-04 10:45:52', ''),
(15, 'Flat', 'Sarai Alamgir', 15000, '', 1, '2026-06-04 10:45:52', ''),
(16, 'Shop', 'Kunjah', 12000, 'occupied', 1, '2026-06-04 10:45:52', ''),
(17, 'House', 'Dinga', 18000, 'available', 1, '2026-06-04 10:45:52', ''),
(18, 'Portion', 'Jalalpur Jattan', 16000, 'available', 1, '2026-06-04 10:45:52', ''),
(19, 'Apartment', 'GT Road Gujrat', 30000, 'available', 1, '2026-06-04 10:45:52', ''),
(20, 'Villa', 'Rehmat Pura Gujrat', 45000, 'occupied', 1, '2026-06-04 10:45:52', ''),
(21, 'luxury house', 'lalamusa', 50000, 'available', 3, '2026-06-04 14:02:55', ''),
(22, 'single story house', 'Kharian', 40000, 'available', 9, '2026-06-08 10:10:43', '1780914860_images 3.jpg');

CREATE TABLE `rental_requests` (
  `id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rental_requests` (`id`, `property_id`, `tenant_id`, `status`, `created_at`) VALUES
(1, 1, 3, '', '2026-06-04 11:27:35'),
(2, 1, 4, 'approved', '2026-06-05 16:39:58');

CREATE TABLE `site_visits` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `site_visits` (`id`, `property_id`, `tenant_id`, `visit_date`, `visit_time`, `status`, `created_at`) VALUES
(1, 7, 4, '2026-10-05', '15:13:00', 'Approved', '2026-06-07 20:15:50');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('tenant','owner','manager','admin') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `first_name`, `last_name`, `phone`) VALUES
(4, NULL, 'babarians@gmail.com', 'af5b3d