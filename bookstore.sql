-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 11, 2022 at 11:59 AM
-- Server version: 8.0.29
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int NOT NULL,
  `title` varchar(124) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `author` varchar(32) COLLATE utf8mb4_general_ci NOT NULL,
  `published` int NOT NULL,
  `category` int NOT NULL DEFAULT '1',
  `status` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `published`, `category`, `status`) VALUES
(1, 'Da Vinci Code,The', 'Da Vinci  ', 1997, 1, 'IN'),
(2, 'Harry Potter and the Deathly Hallows', 'Harry Potter', 1999, 1, 'IN'),
(3, 'The Paris Apartment', 'Lucy Foley', 2002, 1, 'IN'),
(4, 'The Maid', 'Nita Prose', 2005, 1, 'IN'),
(31, 'The Legend', 'unknown', 2000, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'General'),
(2, 'Fiction');

-- --------------------------------------------------------

--
-- Table structure for table `lend`
--

CREATE TABLE `lend` (
  `id` int NOT NULL,
  `bookid` int NOT NULL,
  `libraryid` int NOT NULL,
  `release_date` timestamp NULL DEFAULT NULL,
  `return_date` datetime DEFAULT NULL,
  `status` varchar(3) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'IN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lend`
--

INSERT INTO `lend` (`id`, `bookid`, `libraryid`, `release_date`, `return_date`, `status`) VALUES
(18, 2, 3, NULL, NULL, 'IN'),
(19, 3, 1, NULL, NULL, 'IN'),
(20, 4, 1, NULL, NULL, 'IN'),
(21, 3, 3, NULL, NULL, 'IN'),
(22, 4, 3, NULL, NULL, 'IN'),
(23, 4, 2, NULL, NULL, 'IN'),
(24, 1, 2, NULL, NULL, 'IN'),
(25, 1, 7, NULL, NULL, 'IN'),
(26, 3, 7, NULL, NULL, 'IN'),
(27, 2, 4, NULL, '2022-09-11 16:48:28', 'OUT'),
(28, 2, 1, NULL, NULL, 'IN'),
(29, 3, 1, '2022-09-11 08:57:23', NULL, 'IN'),
(30, 1, 2, '2022-09-11 09:00:36', NULL, 'IN'),
(31, 1, 6, '2022-09-11 09:01:30', NULL, 'IN'),
(32, 1, 7, '2022-09-11 09:01:46', NULL, 'IN'),
(34, 2, 4, '2022-09-11 09:10:10', NULL, 'IN');

-- --------------------------------------------------------

--
-- Table structure for table `library`
--

CREATE TABLE `library` (
  `id` int NOT NULL,
  `name` varchar(124) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `library`
--

INSERT INTO `library` (`id`, `name`) VALUES
(1, 'City Library '),
(2, 'St. Agustine Library'),
(3, 'St. Mary\'s Academy'),
(4, 'STI Computer Academy'),
(5, 'University of The East'),
(6, 'SouthEastern University'),
(7, 'Urius College');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `user` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pass` varchar(124) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `access` int NOT NULL,
  `status` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user`, `pass`, `access`, `status`) VALUES
(1, 'admin', 'SHmV/xasCfzL', 1, 1),
(2, 'rey', 'eDyGqgnyJaI=', 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lend`
--
ALTER TABLE `lend`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `library`
--
ALTER TABLE `library`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lend`
--
ALTER TABLE `lend`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `library`
--
ALTER TABLE `library`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
