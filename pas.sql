-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 07, 2020 at 11:51 AM
-- Server version: 10.3.22-MariaDB-1ubuntu1
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pas`
--
CREATE DATABASE IF NOT EXISTS `pas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pas`;

-- --------------------------------------------------------

--
-- Table structure for table `application`
--

CREATE TABLE `application` (
  `application_number` int(11) NOT NULL,
  `applied_on` date NOT NULL,
  `full_name` varchar(64) NOT NULL,
  `surname` varchar(64) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `date_of_birth` date NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `email` varchar(64) NOT NULL,
  `address` varchar(128) NOT NULL,
  `state` varchar(64) NOT NULL,
  `citizenship` varchar(64) NOT NULL,
  `id_proof_number` varchar(64) NOT NULL,
  `voter_id` varchar(64) NOT NULL,
  `rpo_state` varchar(64) NOT NULL,
  `rpo_district` varchar(64) NOT NULL,
  `rpo_centre` varchar(64) NOT NULL,
  `passport_type` varchar(64) NOT NULL,
  `passport_booklet_pages` varchar(2) NOT NULL,
  `passport_photo` varchar(256) NOT NULL,
  `id_proof` varchar(256) NOT NULL,
  `address_proof` varchar(256) NOT NULL,
  `passport_number` varchar(12) NOT NULL,
  `status` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `application`
--

INSERT INTO `application` (`application_number`, `applied_on`, `full_name`, `surname`, `gender`, `date_of_birth`, `mobile_number`, `phone_number`, `email`, `address`, `state`, `citizenship`, `id_proof_number`, `voter_id`, `rpo_state`, `rpo_district`, `rpo_centre`, `passport_type`, `passport_booklet_pages`, `passport_photo`, `id_proof`, `address_proof`, `passport_number`, `status`) VALUES
(70, '2020-10-07', 'Saai Vignesh ', 'P', 'Male', '1970-10-28', '1234567890', '00000000', 'saaivignesh20@gmail.com', 'test', 'Tamil Nadu', 'Birth', 'abcd', '', 'Tamil Nadu', 'District1', 'Passport Office', 'Normal', '36', 'saaivignesh20@gmail.com-passportPhoto.png', 'saaivignesh20@gmail.com-idProof.png', 'saaivignesh20@gmail.com-addressProof.png', '0V03UHR8X99R', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `auth_token`
--

CREATE TABLE `auth_token` (
  `email_id` varchar(64) NOT NULL,
  `token` varchar(32) NOT NULL,
  `last_signon` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `auth_token`
--

INSERT INTO `auth_token` (`email_id`, `token`, `last_signon`) VALUES
('admin@pas.com', '567f7dac870148785c4bbe4f089406a3', '2020-10-07 11:35:49'),
('saaivignesh20@gmail.com', '929671feefd180c60973614477699d09', '2020-10-07 11:30:03'),
('test@user.com', 'fe9ad9136ea4bf6caa21d139681b934f', '2020-10-07 11:05:22');

-- --------------------------------------------------------

--
-- Table structure for table `pending_activation`
--

CREATE TABLE `pending_activation` (
  `email_id` varchar(64) NOT NULL,
  `activation_code` varchar(16) NOT NULL,
  `gen_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pending_password_reset`
--

CREATE TABLE `pending_password_reset` (
  `email_id` varchar(64) NOT NULL,
  `reset_code` varchar(16) NOT NULL,
  `gen_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `email_id` varchar(64) NOT NULL,
  `pass_hash` varchar(256) NOT NULL,
  `role` varchar(9) NOT NULL,
  `active` varchar(3) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `middle_name` varchar(20) NOT NULL,
  `last_name` varchar(20) NOT NULL,
  `mobile_number` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`email_id`, `pass_hash`, `role`, `active`, `first_name`, `middle_name`, `last_name`, `mobile_number`) VALUES
('admin@pas.com', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'officer', 'yes', 'Administrator', '', 'PAS', '7358664870'),
('saaivignesh20@gmail.com', 'ba2c0b873f242be5ea0e283801211182e4c5f23bd4960f74f6f1708162411aad', 'applicant', 'yes', 'Saai Vignesh', '', 'P', '8220134736'),
('test@user.com', '9f735e0df9a1ddc702bf0a1a7b83033f9f7153a00c29de82cedadc9957289b05', 'applicant', 'yes', 'Test', '', 'User', '0123456789');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`application_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `passport_number` (`passport_number`);

--
-- Indexes for table `auth_token`
--
ALTER TABLE `auth_token`
  ADD PRIMARY KEY (`email_id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `pending_activation`
--
ALTER TABLE `pending_activation`
  ADD PRIMARY KEY (`email_id`);

--
-- Indexes for table `pending_password_reset`
--
ALTER TABLE `pending_password_reset`
  ADD PRIMARY KEY (`email_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`email_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application`
--
ALTER TABLE `application`
  MODIFY `application_number` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application`
--
ALTER TABLE `application`
  ADD CONSTRAINT `fk_application_email_id` FOREIGN KEY (`email`) REFERENCES `user` (`email_id`);

--
-- Constraints for table `auth_token`
--
ALTER TABLE `auth_token`
  ADD CONSTRAINT `fk_authtoken_email_id` FOREIGN KEY (`email_id`) REFERENCES `user` (`email_id`);

--
-- Constraints for table `pending_activation`
--
ALTER TABLE `pending_activation`
  ADD CONSTRAINT `fk_pendingactivation_email_id` FOREIGN KEY (`email_id`) REFERENCES `user` (`email_id`);

--
-- Constraints for table `pending_password_reset`
--
ALTER TABLE `pending_password_reset`
  ADD CONSTRAINT `fk_pendingreset_email_id` FOREIGN KEY (`email_id`) REFERENCES `user` (`email_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
