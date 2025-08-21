-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Авг 19 2025 г., 21:52
-- Версия сервера: 10.4.28-MariaDB
-- Версия PHP: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `glowi`
--

-- --------------------------------------------------------

--
-- Структура таблицы `achievements`
--

CREATE TABLE `achievements` (
  `achievementID` int(11) NOT NULL,
  `childID` int(4) NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `fileURL` varchar(255) DEFAULT NULL,
  `dateAwarded` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `medal` enum('gold','silver','bronze','none') NOT NULL DEFAULT 'none',
  `place` int(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `achievements`
--

INSERT INTO `achievements` (`achievementID`, `childID`, `title`, `type`, `fileURL`, `dateAwarded`, `created_at`, `updated_at`, `medal`, `place`) VALUES
(1, 2, 'Koop Cup', 'medal', '', '2025-07-05', '2025-06-24 01:12:18', '2025-07-28 15:41:42', 'bronze', 3),
(5, 2, 'Koop Cup', 'medal', '', '2025-07-15', '2025-06-24 02:05:54', '2025-07-26 22:02:04', 'silver', 2),
(12, 2, 'КККккк', 'medal', NULL, '2025-08-04', '2025-08-17 17:20:04', '2025-08-17 17:20:04', 'gold', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `children`
--

CREATE TABLE `children` (
  `childID` int(4) NOT NULL,
  `parentID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `groupLevel` varchar(50) NOT NULL,
  `photoImage` varchar(255) DEFAULT 'placeholder_100.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `gender` enum('male','female','unknown') DEFAULT 'unknown'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `children`
--

INSERT INTO `children` (`childID`, `parentID`, `name`, `age`, `groupLevel`, `photoImage`, `created_at`, `updated_at`, `gender`) VALUES
(2, 3, 'Kiriia', 11, 'Novice', 'child_6885a47943c351.70511861.png', '2025-06-24 00:25:30', '2025-07-27 04:00:57', 'female'),
(18, 3, 'Mila', 5, 'Level 2A', 'child_688443fd8c14c4.43643140.png', '2025-07-25 15:25:09', '2025-07-26 02:57:01', 'female'),
(22, 3, 'AAAAAAA', 6, 'wwwwww', 'placeholder.png', '2025-07-26 02:44:22', '2025-07-26 02:44:22', 'unknown');

-- --------------------------------------------------------

--
-- Структура таблицы `child_event`
--

CREATE TABLE `child_event` (
  `ID` int(4) NOT NULL,
  `eventID` int(11) NOT NULL,
  `childID` int(4) NOT NULL,
  `createdBy` enum('parent','coach') DEFAULT 'parent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `child_event`
--

INSERT INTO `child_event` (`ID`, `eventID`, `childID`, `createdBy`) VALUES
(18, 16, 2, 'parent'),
(19, 17, 2, 'parent');

-- --------------------------------------------------------

--
-- Структура таблицы `events`
--

CREATE TABLE `events` (
  `eventID` int(11) NOT NULL,
  `eventType` enum('training','competition') NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `location` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `createdBy` enum('parent','coach') DEFAULT 'coach'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `events`
--

INSERT INTO `events` (`eventID`, `eventType`, `title`, `description`, `date`, `time`, `location`, `created_at`, `updated_at`, `createdBy`) VALUES
(1, 'competition', 'Koop Cup', 'new copmetition', '2025-08-22', '12:30:00', 'Markham', '2025-07-08 02:05:52', '2025-07-08 02:05:52', 'coach'),
(2, 'training', 'today', 'Private class', '2025-07-09', '16:00:00', 'PanAMcenter', '2025-07-08 02:08:11', '2025-07-08 02:08:11', 'coach'),
(3, 'training', 'private', 'face to face', '2025-07-10', '12:00:00', 'gggggg', '2025-07-08 03:08:13', '2025-07-08 03:08:13', 'coach'),
(4, 'competition', 'dddddd', 'ddddd', '2025-08-09', '19:00:00', 'h', '2025-07-08 03:09:58', '2025-07-08 03:09:58', 'coach'),
(9, 'training', 'Koop Cup', 'ddddd', '2025-08-05', '11:11:00', 'Канада', '2025-08-01 12:31:26', '2025-08-01 12:31:26', 'parent'),
(10, 'competition', 'Trillium', '', '2025-08-12', '10:10:00', 'Forest', '2025-08-01 12:45:07', '2025-08-01 12:45:07', 'parent'),
(11, 'competition', 'today', 'ллллл', '2025-08-15', '14:00:00', 'Muskoka Lakes place', '2025-08-01 14:10:03', '2025-08-01 14:10:03', 'coach'),
(12, 'training', 'Koop Cup', 'ыыы', '2025-08-19', '14:23:00', 'Ocean', '2025-08-01 14:18:02', '2025-08-01 14:18:02', 'coach'),
(13, 'training', 'private', 'ddddd', '2025-08-20', '12:00:00', 'Gym', '2025-08-01 15:40:56', '2025-08-01 15:40:56', 'coach'),
(14, 'training', 'private', 'ddddd', '2025-08-29', '04:50:00', 'Gym', '2025-08-01 15:42:07', '2025-08-18 02:25:32', 'coach'),
(15, 'training', 'private', 'ddddd', '2025-08-15', '14:00:00', 'Gym', '2025-08-01 15:49:45', '2025-08-01 15:49:45', 'coach'),
(16, 'competition', '2026 Koop Cup', 'Rhythmic Gymnastics Club Competition', '2026-04-22', '09:00:00', 'Markham Pan Am Centre', '2025-08-18 02:23:03', '2025-08-18 02:23:03', 'coach'),
(17, 'competition', '2025 Christmas Cup', 'Glimmer Christmas Cup', '2025-12-11', '09:00:00', '', '2025-08-18 02:24:39', '2025-08-18 02:24:39', 'coach');

-- --------------------------------------------------------

--
-- Структура таблицы `parents`
--

CREATE TABLE `parents` (
  `parentID` int(4) NOT NULL,
  `userName` varchar(100) NOT NULL,
  `emailAddress` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `last_failed_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `parents`
--

INSERT INTO `parents` (`parentID`, `userName`, `emailAddress`, `password`, `failed_attempts`, `last_failed_login`, `created_at`, `updated_at`) VALUES
(3, 'MOM', 'irina.safronova0801@gmail.com', '$2y$10$SsH5nP.3cGM0pyed9qRRUumZGu9PRPe5mTSgCg1VoFsCJVvdoG72e', 0, NULL, '2025-06-24 00:25:07', '2025-06-24 00:25:07'),
(4, 'MOMiiiii', 'mnmnmnm@gmail.com', '$2y$10$d//DEew5LTOj2gTJ50WMLuxoXYEr0DAZfvUxqspHtQJ.9Fs.TQCDe', 0, NULL, '2025-07-23 02:28:45', '2025-07-23 02:28:45'),
(5, 'DADADA', 'unicorn@gmail.com', '$2y$10$Hz3xAnmVOH4h.RumefdYlumGeJFrL3m1uI/XI765tJzNu1zr3iw2m', 0, NULL, '2025-08-15 15:08:53', '2025-08-15 15:08:53');

-- --------------------------------------------------------

--
-- Структура таблицы `payments`
--

CREATE TABLE `payments` (
  `paymentID` int(11) NOT NULL,
  `childID` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('paid','unpaid') NOT NULL,
  `paymentDate` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `payments`
--

INSERT INTO `payments` (`paymentID`, `childID`, `amount`, `status`, `paymentDate`, `created_at`, `updated_at`) VALUES
(8, 2, 200.00, 'paid', '2025-07-24', '2025-07-26 21:02:26', '2025-07-26 21:02:26'),
(9, 2, 400.00, 'paid', '2025-08-17', '2025-08-17 16:49:53', '2025-08-17 16:49:53');

-- --------------------------------------------------------

--
-- Структура таблицы `private_lesson_requests`
--

CREATE TABLE `private_lesson_requests` (
  `requestID` int(11) NOT NULL,
  `childID` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `response` text DEFAULT NULL,
  `requestDate` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `private_lesson_requests`
--

INSERT INTO `private_lesson_requests` (`requestID`, `childID`, `message`, `status`, `response`, `requestDate`, `created_at`, `updated_at`) VALUES
(2, 2, 'qqqqqqqqqq', 'approved', NULL, '2025-08-26 18:00:00', '2025-08-17 22:06:13', '2025-08-18 14:41:26'),
(3, 2, 'урок', 'declined', NULL, '2025-08-27 18:00:00', '2025-08-18 14:20:37', '2025-08-18 14:25:07'),
(4, 2, 'вввв', 'approved', NULL, '2025-08-26 12:24:00', '2025-08-18 14:24:20', '2025-08-18 14:24:51'),
(5, 2, 'class ballet', 'pending', NULL, '2025-08-19 15:00:00', '2025-08-18 14:39:02', '2025-08-18 14:39:02');

-- --------------------------------------------------------

--
-- Структура таблицы `schedule`
--

CREATE TABLE `schedule` (
  `scheduleID` int(11) NOT NULL,
  `childID` int(11) NOT NULL,
  `dayOfWeek` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `activity` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `schedule`
--

INSERT INTO `schedule` (`scheduleID`, `childID`, `dayOfWeek`, `startTime`, `endTime`, `activity`) VALUES
(1, 2, 'Monday', '16:00:00', '21:00:00', 'Training day'),
(2, 2, 'Tuesday', '16:00:00', '21:00:00', 'Training day'),
(3, 2, 'Wednesday', '16:00:00', '21:00:00', 'Training day'),
(4, 2, 'Friday', '16:00:00', '21:00:00', 'Training day'),
(6, 2, 'Sunday', '15:00:00', '20:00:00', 'Ballet, training');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`achievementID`),
  ADD KEY `FK_childID` (`childID`);

--
-- Индексы таблицы `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`childID`),
  ADD KEY `FK_parentID` (`parentID`);

--
-- Индексы таблицы `child_event`
--
ALTER TABLE `child_event`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `FK_event` (`eventID`),
  ADD KEY `FK_childID_child_event` (`childID`);

--
-- Индексы таблицы `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`eventID`);

--
-- Индексы таблицы `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`parentID`),
  ADD UNIQUE KEY `emailAddress` (`emailAddress`);

--
-- Индексы таблицы `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `FK_childID_payments` (`childID`);

--
-- Индексы таблицы `private_lesson_requests`
--
ALTER TABLE `private_lesson_requests`
  ADD PRIMARY KEY (`requestID`),
  ADD KEY `childID` (`childID`);

--
-- Индексы таблицы `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`scheduleID`),
  ADD KEY `childID` (`childID`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `achievements`
--
ALTER TABLE `achievements`
  MODIFY `achievementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `children`
--
ALTER TABLE `children`
  MODIFY `childID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `child_event`
--
ALTER TABLE `child_event`
  MODIFY `ID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT для таблицы `events`
--
ALTER TABLE `events`
  MODIFY `eventID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `parents`
--
ALTER TABLE `parents`
  MODIFY `parentID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `payments`
--
ALTER TABLE `payments`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `private_lesson_requests`
--
ALTER TABLE `private_lesson_requests`
  MODIFY `requestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `schedule`
--
ALTER TABLE `schedule`
  MODIFY `scheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `FK_childID` FOREIGN KEY (`childID`) REFERENCES `children` (`childID`);

--
-- Ограничения внешнего ключа таблицы `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `FK_parentID` FOREIGN KEY (`parentID`) REFERENCES `parents` (`parentID`);

--
-- Ограничения внешнего ключа таблицы `child_event`
--
ALTER TABLE `child_event`
  ADD CONSTRAINT `FK_childID_child_event` FOREIGN KEY (`childID`) REFERENCES `children` (`childID`),
  ADD CONSTRAINT `FK_eventID` FOREIGN KEY (`eventID`) REFERENCES `events` (`eventID`);

--
-- Ограничения внешнего ключа таблицы `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `FK_childID_payments` FOREIGN KEY (`childID`) REFERENCES `children` (`childID`);

--
-- Ограничения внешнего ключа таблицы `private_lesson_requests`
--
ALTER TABLE `private_lesson_requests`
  ADD CONSTRAINT `private_lesson_requests_ibfk_1` FOREIGN KEY (`childID`) REFERENCES `children` (`childID`);

--
-- Ограничения внешнего ключа таблицы `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`childID`) REFERENCES `children` (`childID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
