-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2017 at 12:59 PM
-- Server version: 10.1.21-MariaDB
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `customer_care`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth`
--

CREATE TABLE `auth` (
  `USERNAME` varchar(255) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `auth_request`
--

CREATE TABLE `auth_request` (
  `TOKEN` varchar(255) NOT NULL,
  `IP` varchar(100) NOT NULL,
  `DATE` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mapping_chat_group`
--

CREATE TABLE `mapping_chat_group` (
  `GROUP_CODE` varchar(10) NOT NULL,
  `GROUP_NAME` varchar(255) NOT NULL,
  `CHAT_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pushchat`
--

CREATE TABLE `pushchat` (
  `ticket_id` int(11) NOT NULL,
  `username_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sync_ticket_system`
--

CREATE TABLE `sync_ticket_system` (
  `TICKET_ID` int(11) NOT NULL,
  `TICKET_STATUS` enum('ON PROCESS','SOLVED') NOT NULL,
  `TICKET_UPDATED_DATE` datetime DEFAULT NULL,
  `TICKET_SOLUTION` text,
  `DISPOSITION_GROUP_CODE` varchar(10) DEFAULT NULL,
  `SYSTEM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `telegram_message`
--

CREATE TABLE `telegram_message` (
  `UPDATE_ID` int(11) NOT NULL,
  `MESSAGE_ID` int(11) NOT NULL,
  `USERNAME` varchar(255) NOT NULL,
  `FULLNAME` varchar(255) NOT NULL,
  `CHAT_ID` int(11) NOT NULL,
  `CHAT_GROUP_NAME` varchar(255) NOT NULL,
  `MESSAGE` text NOT NULL,
  `MESSAGE_DATE` datetime NOT NULL,
  `TICKET_ID` int(11) NOT NULL,
  `USERNAME_ID` int(11) NOT NULL,
  `TICKET_STATUS` enum('ON PROCESS','SOLVED') DEFAULT 'ON PROCESS',
  `TICKET_UPDATED_DATE` datetime DEFAULT NULL,
  `TICKET_SOLUTION` text,
  `DISPOSITION_GROUP_CODE` varchar(10) DEFAULT NULL,
  `SYSTEM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_accepted_request`
--

CREATE TABLE `user_accepted_request` (
  `USERNAME_ID` int(11) NOT NULL,
  `CREATED_DATE` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `TICKET_ID` int(11) NOT NULL,
  `QUOTA_DESCRIPTION` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth`
--
ALTER TABLE `auth`
  ADD PRIMARY KEY (`USERNAME`);

--
-- Indexes for table `auth_request`
--
ALTER TABLE `auth_request`
  ADD PRIMARY KEY (`TOKEN`,`IP`);

--
-- Indexes for table `mapping_chat_group`
--
ALTER TABLE `mapping_chat_group`
  ADD PRIMARY KEY (`GROUP_CODE`);

--
-- Indexes for table `sync_ticket_system`
--
ALTER TABLE `sync_ticket_system`
  ADD PRIMARY KEY (`TICKET_ID`,`TICKET_STATUS`);

--
-- Indexes for table `telegram_message`
--
ALTER TABLE `telegram_message`
  ADD PRIMARY KEY (`UPDATE_ID`,`MESSAGE_ID`,`TICKET_ID`);

--
-- Indexes for table `user_accepted_request`
--
ALTER TABLE `user_accepted_request`
  ADD PRIMARY KEY (`USERNAME_ID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
