-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2026 at 10:17 AM
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
-- Database: `mlbb_quiz`
--

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `question` varchar(255) NOT NULL,
  `option_a` varchar(100) NOT NULL,
  `option_b` varchar(100) NOT NULL,
  `option_c` varchar(100) NOT NULL,
  `option_d` varchar(100) NOT NULL,
  `answer` tinyint(4) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `category`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `answer`, `image`) VALUES
(1, 'Game basics', 'Which company developed Mobile Legends: Bang Bang?', 'Moonton', 'Riot Games', 'Garena', 'Supercell', 0, NULL),
(2, 'Game basics', 'How many players are on each team in a classic match?', '3', '4', '5', '6', 2, NULL),
(3, 'Game basics', 'What is the name of the main battlefield map?', 'Land of Dawn', 'Summoner\'s Rift', 'Howling Abyss', 'Magic Chess Arena', 0, NULL),
(4, 'Game basics', 'How many lanes are on the main map?', '1', '2', '3', '4', 2, NULL),
(5, 'Game basics', 'How many hero roles are there (Tank, Fighter, Assassin, Mage, Marksman, Support)?', '4', '5', '6', '7', 2, NULL),
(6, 'Game basics', 'Which big jungle monster, when killed, helps your team push a lane with a powerful minion?', 'Lord', 'Crab', 'Golem', 'Imp', 0, NULL),
(7, 'Heroes', 'What is Tigreal\'s role?', 'Mage', 'Tank', 'Marksman', 'Assassin', 1, NULL),
(8, 'Heroes', 'What is Layla\'s role?', 'Marksman', 'Support', 'Fighter', 'Tank', 0, 'layla.png'),
(9, 'Heroes', 'What is Eudora\'s role?', 'Fighter', 'Tank', 'Mage', 'Support', 2, NULL),
(10, 'Heroes', 'What is Alucard\'s role?', 'Fighter', 'Mage', 'Support', 'Marksman', 0, NULL),
(11, 'Heroes', 'What is Gusion\'s role?', 'Tank', 'Assassin', 'Support', 'Marksman', 1, NULL),
(12, 'Heroes', 'What is Angela\'s role?', 'Mage', 'Assassin', 'Support', 'Fighter', 2, NULL),
(13, 'Heroes', 'Which hero is famous for pulling enemies with an iron hook?', 'Franco', 'Layla', 'Zilong', 'Eudora', 0, NULL),
(14, 'Heroes', 'Which tank can transform into a car?', 'Tigreal', 'Johnson', 'Atlas', 'Minotaur', 1, NULL),
(15, 'Heroes', 'Which mage fights using an umbrella?', 'Eudora', 'Nana', 'Kagura', 'Cecilion', 2, NULL),
(16, 'Heroes', 'Which assassin uses steel cables to zip around the map?', 'Fanny', 'Saber', 'Gusion', 'Hayabusa', 0, NULL),
(17, 'Battle spells', 'Which battle spell is essential for junglers to kill jungle monsters?', 'Retribution', 'Flicker', 'Purify', 'Sprint', 0, NULL),
(18, 'Battle spells', 'Which battle spell teleports you a short distance?', 'Purify', 'Flicker', 'Execute', 'Petrify', 1, NULL),
(19, 'Battle spells', 'Which battle spell removes negative effects from your hero?', 'Sprint', 'Aegis', 'Purify', 'Revitalize', 2, NULL),
(20, 'Ranks & kills', 'How many kills in quick succession earn you a Savage?', '3', '4', '5', '6', 2, NULL),
(21, 'Ranks & kills', 'What is the lowest ranked tier in Mobile Legends?', 'Warrior', 'Elite', 'Master', 'Epic', 0, NULL),
(22, 'Ranks & kills', 'Which rank comes right after Epic?', 'Grandmaster', 'Legend', 'Mythic', 'Master', 1, NULL),
(23, 'Game basics', 'Which company developed Mobile Legends: Bang Bang?', 'Moonton', 'Riot Games', 'Garena', 'Supercell', 0, NULL),
(24, 'Game basics', 'How many players are on each team in a classic match?', '3', '4', '5', '6', 2, NULL),
(25, 'Game basics', 'What is the name of the main battlefield map?', 'Land of Dawn', 'Summoner\'s Rift', 'Howling Abyss', 'Magic Chess Arena', 0, NULL),
(26, 'Game basics', 'How many lanes are on the main map?', '1', '2', '3', '4', 2, NULL),
(27, 'Game basics', 'How many hero roles are there (Tank, Fighter, Assassin, Mage, Marksman, Support)?', '4', '5', '6', '7', 2, NULL),
(28, 'Game basics', 'Which big jungle monster, when killed, helps your team push a lane with a powerful minion?', 'Lord', 'Crab', 'Golem', 'Imp', 0, NULL),
(29, 'Heroes', 'What is Tigreal\'s role?', 'Mage', 'Tank', 'Marksman', 'Assassin', 1, NULL),
(30, 'Heroes', 'What is Layla\'s role?', 'Marksman', 'Support', 'Fighter', 'Tank', 0, 'layla.png'),
(31, 'Heroes', 'What is Eudora\'s role?', 'Fighter', 'Tank', 'Mage', 'Support', 2, NULL),
(32, 'Heroes', 'What is Alucard\'s role?', 'Fighter', 'Mage', 'Support', 'Marksman', 0, NULL),
(33, 'Heroes', 'What is Gusion\'s role?', 'Tank', 'Assassin', 'Support', 'Marksman', 1, NULL),
(34, 'Heroes', 'What is Angela\'s role?', 'Mage', 'Assassin', 'Support', 'Fighter', 2, NULL),
(35, 'Heroes', 'Which hero is famous for pulling enemies with an iron hook?', 'Franco', 'Layla', 'Zilong', 'Eudora', 0, NULL),
(36, 'Heroes', 'Which tank can transform into a car?', 'Tigreal', 'Johnson', 'Atlas', 'Minotaur', 1, NULL),
(37, 'Heroes', 'Which mage fights using an umbrella?', 'Eudora', 'Nana', 'Kagura', 'Cecilion', 2, NULL),
(38, 'Heroes', 'Which assassin uses steel cables to zip around the map?', 'Fanny', 'Saber', 'Gusion', 'Hayabusa', 0, NULL),
(39, 'Battle spells', 'Which battle spell is essential for junglers to kill jungle monsters?', 'Retribution', 'Flicker', 'Purify', 'Sprint', 0, NULL),
(40, 'Battle spells', 'Which battle spell teleports you a short distance?', 'Purify', 'Flicker', 'Execute', 'Petrify', 1, NULL),
(41, 'Battle spells', 'Which battle spell removes negative effects from your hero?', 'Sprint', 'Aegis', 'Purify', 'Revitalize', 2, NULL),
(42, 'Ranks & kills', 'How many kills in quick succession earn you a Savage?', '3', '4', '5', '6', 2, NULL),
(43, 'Ranks & kills', 'What is the lowest ranked tier in Mobile Legends?', 'Warrior', 'Elite', 'Master', 'Epic', 0, NULL),
(44, 'Ranks & kills', 'Which rank comes right after Epic?', 'Grandmaster', 'Legend', 'Mythic', 'Master', 1, NULL),
(45, 'Game basics', 'Which company developed Mobile Legends: Bang Bang?', 'Moonton', 'Riot Games', 'Garena', 'Supercell', 0, NULL),
(46, 'Game basics', 'How many players are on each team in a classic match?', '3', '4', '5', '6', 2, NULL),
(47, 'Game basics', 'What is the name of the main battlefield map?', 'Land of Dawn', 'Summoner\'s Rift', 'Howling Abyss', 'Magic Chess Arena', 0, NULL),
(48, 'Game basics', 'How many lanes are on the main map?', '1', '2', '3', '4', 2, NULL),
(49, 'Game basics', 'How many hero roles are there (Tank, Fighter, Assassin, Mage, Marksman, Support)?', '4', '5', '6', '7', 2, NULL),
(50, 'Game basics', 'Which big jungle monster, when killed, helps your team push a lane with a powerful minion?', 'Lord', 'Crab', 'Golem', 'Imp', 0, NULL),
(51, 'Heroes', 'What is Tigreal\'s role?', 'Mage', 'Tank', 'Marksman', 'Assassin', 1, NULL),
(52, 'Heroes', 'What is Layla\'s role?', 'Marksman', 'Support', 'Fighter', 'Tank', 0, NULL),
(53, 'Heroes', 'What is Eudora\'s role?', 'Fighter', 'Tank', 'Mage', 'Support', 2, NULL),
(54, 'Heroes', 'What is Alucard\'s role?', 'Fighter', 'Mage', 'Support', 'Marksman', 0, NULL),
(55, 'Heroes', 'What is Gusion\'s role?', 'Tank', 'Assassin', 'Support', 'Marksman', 1, NULL),
(56, 'Heroes', 'What is Angela\'s role?', 'Mage', 'Assassin', 'Support', 'Fighter', 2, NULL),
(57, 'Heroes', 'Which hero is famous for pulling enemies with an iron hook?', 'Franco', 'Layla', 'Zilong', 'Eudora', 0, NULL),
(58, 'Heroes', 'Which tank can transform into a car?', 'Tigreal', 'Johnson', 'Atlas', 'Minotaur', 1, NULL),
(59, 'Heroes', 'Which mage fights using an umbrella?', 'Eudora', 'Nana', 'Kagura', 'Cecilion', 2, NULL),
(60, 'Heroes', 'Which assassin uses steel cables to zip around the map?', 'Fanny', 'Saber', 'Gusion', 'Hayabusa', 0, NULL),
(61, 'Battle spells', 'Which battle spell is essential for junglers to kill jungle monsters?', 'Retribution', 'Flicker', 'Purify', 'Sprint', 0, NULL),
(62, 'Battle spells', 'Which battle spell teleports you a short distance?', 'Purify', 'Flicker', 'Execute', 'Petrify', 1, NULL),
(63, 'Battle spells', 'Which battle spell removes negative effects from your hero?', 'Sprint', 'Aegis', 'Purify', 'Revitalize', 2, NULL),
(64, 'Ranks & kills', 'How many kills in quick succession earn you a Savage?', '3', '4', '5', '6', 2, NULL),
(65, 'Ranks & kills', 'What is the lowest ranked tier in Mobile Legends?', 'Warrior', 'Elite', 'Master', 'Epic', 0, NULL),
(66, 'Ranks & kills', 'Which rank comes right after Epic?', 'Grandmaster', 'Legend', 'Mythic', 'Master', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `score` tinyint(4) NOT NULL,
  `total` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scores`
--

INSERT INTO `scores` (`id`, `name`, `score`, `total`, `created_at`, `user_id`) VALUES
(1, 'Aljon', 10, 10, '2026-10-14 07:53:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('player','admin') NOT NULL DEFAULT 'player',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'utoy', '$2y$10$Xgg4Innx3I1eNVLl0D1YkeO06Zf1M0IQIP0R2EQlBMlA8d65GWFhu', 'player', '2026-10-14 08:07:24'),
(2, 'admin', '$2y$10$xnw4Ilpk3EQp5xKvFHGsq.L6z7gcIe4/h1jFVrVn1w3GalCs1UKz2', 'admin', '2026-10-14 08:09:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_scores_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `fk_scores_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
