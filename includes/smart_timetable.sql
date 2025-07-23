-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2025 at 08:42 AM
-- Server version: 10.1.29-MariaDB
-- PHP Version: 7.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_timetable`
--

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `module_code` varchar(50) NOT NULL,
  `periods_per_week` int(11) NOT NULL,
  `preferred_time` enum('Any','Morning','Afternoon') DEFAULT 'Any',
  `teacher_id` int(11) DEFAULT NULL,
  `class_ids` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `name`, `module_code`, `periods_per_week`, `preferred_time`, `teacher_id`, `class_ids`) VALUES
(1, 'Analyse project requirements', 'SWDPR301', 2, 'Any', 6, '5'),
(2, 'Apply JavaScript', 'SWDJF301', 2, 'Any', 5, '5'),
(3, 'Develop website', 'SWDWD301', 6, 'Morning', 7, '5'),
(4, 'Apply Algebra and Trigonometry', 'CCMAT302', 4, 'Morning', 3, '5'),
(5, 'Apply general physics', 'GENPY301', 2, 'Any', 3, '5'),
(6, 'Apply basic graphics design', 'GENGD301', 4, 'Morning', 6, '5'),
(7, 'Pratiquer les activitÃ©s de communication dans le mÃ©tier', 'CCMFT302', 2, 'Afternoon', 2, '5'),
(8, 'Gukoresha ikinyarwanda kiboneye', 'CCMKN302', 2, 'Any', 2, '5'),
(9, 'Use Pre-intermediate English at the Workplace', 'CCMEN302', 2, 'Any', 2, '5'),
(10, 'Apply computer literacy', 'CCMCL302', 2, 'Afternoon', 5, '5');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`) VALUES
(1, 'S1'),
(2, 'S2'),
(3, 'S3A'),
(4, 'S3B'),
(5, 'L3-SOD'),
(6, 'L4-SOD'),
(7, 'L5-SOD');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `periods_per_week` int(11) NOT NULL,
  `preferred_time` enum('Any','Morning','Afternoon') DEFAULT 'Any',
  `teacher_id` int(11) DEFAULT NULL,
  `class_id` varchar(255) DEFAULT NULL,
  `class_ids` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `periods_per_week`, `preferred_time`, `teacher_id`, `class_id`, `class_ids`) VALUES
(1, 'Mathematics', 6, 'Morning', 3, '', '1,2,3,4'),
(2, 'English', 5, 'Any', 2, '', '1,2,3,4'),
(3, 'Biology', 4, 'Morning', 1, '', '1,2,3,4'),
(4, 'Chemistry', 4, 'Morning', 1, '', '1,2,3,4'),
(5, 'Physics', 4, 'Morning', 3, '', '1,2,3,4'),
(6, 'History', 3, 'Any', 4, '', '1,2,3,4'),
(7, 'Geography', 3, 'Any', 4, '', '1,2,3,4'),
(8, 'Entrepreurship', 3, 'Any', 4, '', '1,2,3,4'),
(9, 'Kinyarwanda', 3, 'Any', 2, '', '1,2,3,4'),
(10, 'French', 3, 'Any', 2, '', '1,2,3,4'),
(11, 'Kiswahili', 3, 'Any', 2, '', '1,2,3,4'),
(12, 'ICT', 2, 'Afternoon', 5, '', '1,2,3,4'),
(13, 'Phyical Education and Sport', 2, 'Afternoon', 4, '', '1,2,3,4'),
(14, 'Religious and Ethics Education', 1, 'Afternoon', 1, '', '1,2,3,4');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `name`) VALUES
(1, 'NZAYIKORERA Emmanuel'),
(2, 'HARERIMANA Jean'),
(3, 'NIYONSABA JOSELYNE'),
(4, 'Uwoyezantije Damascene'),
(5, 'TWIZEYIMANA Elie'),
(6, 'MUNYAKAYANZA Jean Pierre'),
(7, 'NIYOMUHOZA Jean de Dieu');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `type` enum('olevel','tvet') NOT NULL,
  `day` varchar(20) NOT NULL,
  `hour` int(11) NOT NULL,
  `subject_module_id` int(11) NOT NULL,
  `subject_module_name` varchar(100) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `type`, `day`, `hour`, `subject_module_id`, `subject_module_name`, `room_id`, `class_id`) VALUES
(820, 'olevel', 'Monday', 1, 1, 'Mathematics', NULL, 1),
(821, 'olevel', 'Monday', 2, 1, 'Mathematics', NULL, 1),
(822, 'olevel', 'Monday', 3, 1, 'Mathematics', NULL, 1),
(823, 'olevel', 'Monday', 4, 1, 'Mathematics', NULL, 1),
(824, 'olevel', 'Monday', 5, 2, 'English', NULL, 1),
(825, 'olevel', 'Monday', 6, 2, 'English', NULL, 1),
(826, 'olevel', 'Monday', 7, 2, 'English', NULL, 1),
(827, 'olevel', 'Monday', 8, 12, 'ICT', NULL, 1),
(828, 'olevel', 'Monday', 9, 12, 'ICT', NULL, 1),
(829, 'olevel', 'Monday', 10, 13, 'Phyical Education and Sport', NULL, 1),
(830, 'olevel', 'Tuesday', 1, 1, 'Mathematics', NULL, 1),
(831, 'olevel', 'Tuesday', 2, 1, 'Mathematics', NULL, 1),
(832, 'olevel', 'Tuesday', 3, 3, 'Biology', NULL, 1),
(833, 'olevel', 'Tuesday', 4, 3, 'Biology', NULL, 1),
(834, 'olevel', 'Tuesday', 5, 2, 'English', NULL, 1),
(835, 'olevel', 'Tuesday', 6, 2, 'English', NULL, 1),
(836, 'olevel', 'Tuesday', 7, 6, 'History', NULL, 1),
(837, 'olevel', 'Tuesday', 8, 13, 'Phyical Education and Sport', NULL, 1),
(838, 'olevel', 'Tuesday', 9, 14, 'Religious and Ethics Education', NULL, 1),
(839, 'olevel', 'Tuesday', 10, 6, 'History', NULL, 1),
(840, 'olevel', 'Wednesday', 1, 3, 'Biology', NULL, 1),
(841, 'olevel', 'Wednesday', 2, 3, 'Biology', NULL, 1),
(842, 'olevel', 'Wednesday', 3, 4, 'Chemistry', NULL, 1),
(843, 'olevel', 'Wednesday', 4, 4, 'Chemistry', NULL, 1),
(844, 'olevel', 'Wednesday', 5, 6, 'History', NULL, 1),
(845, 'olevel', 'Wednesday', 6, 7, 'Geography', NULL, 1),
(846, 'olevel', 'Wednesday', 7, 7, 'Geography', NULL, 1),
(847, 'olevel', 'Wednesday', 8, 7, 'Geography', NULL, 1),
(848, 'olevel', 'Wednesday', 9, 8, 'Entrepreurship', NULL, 1),
(849, 'olevel', 'Wednesday', 10, 8, 'Entrepreurship', NULL, 1),
(850, 'olevel', 'Thursday', 1, 4, 'Chemistry', NULL, 1),
(851, 'olevel', 'Thursday', 2, 4, 'Chemistry', NULL, 1),
(852, 'olevel', 'Thursday', 3, 5, 'Physics', NULL, 1),
(853, 'olevel', 'Thursday', 4, 5, 'Physics', NULL, 1),
(854, 'olevel', 'Thursday', 5, 8, 'Entrepreurship', NULL, 1),
(855, 'olevel', 'Thursday', 6, 9, 'Kinyarwanda', NULL, 1),
(856, 'olevel', 'Thursday', 7, 9, 'Kinyarwanda', NULL, 1),
(857, 'olevel', 'Thursday', 8, 9, 'Kinyarwanda', NULL, 1),
(858, 'olevel', 'Thursday', 9, 10, 'French', NULL, 1),
(859, 'olevel', 'Thursday', 10, 10, 'French', NULL, 1),
(860, 'olevel', 'Friday', 1, 5, 'Physics', NULL, 1),
(861, 'olevel', 'Friday', 2, 5, 'Physics', NULL, 1),
(862, 'olevel', 'Friday', 3, 10, 'French', NULL, 1),
(863, 'olevel', 'Friday', 4, 11, 'Kiswahili', NULL, 1),
(864, 'olevel', 'Friday', 5, 11, 'Kiswahili', NULL, 1),
(865, 'olevel', 'Friday', 6, 11, 'Kiswahili', NULL, 1),
(866, 'olevel', 'Monday', 1, 3, 'Biology', NULL, 2),
(867, 'olevel', 'Monday', 2, 3, 'Biology', NULL, 2),
(868, 'olevel', 'Monday', 3, 3, 'Biology', NULL, 2),
(869, 'olevel', 'Monday', 4, 3, 'Biology', NULL, 2),
(870, 'olevel', 'Monday', 5, 6, 'History', NULL, 2),
(871, 'olevel', 'Monday', 6, 6, 'History', NULL, 2),
(872, 'olevel', 'Monday', 7, 6, 'History', NULL, 2),
(873, 'olevel', 'Monday', 8, 13, 'Phyical Education and Sport', NULL, 2),
(874, 'olevel', 'Monday', 9, 13, 'Phyical Education and Sport', NULL, 2),
(875, 'olevel', 'Monday', 10, 12, 'ICT', NULL, 2),
(876, 'olevel', 'Tuesday', 1, 4, 'Chemistry', NULL, 2),
(877, 'olevel', 'Tuesday', 2, 4, 'Chemistry', NULL, 2),
(878, 'olevel', 'Tuesday', 3, 1, 'Mathematics', NULL, 2),
(879, 'olevel', 'Tuesday', 4, 1, 'Mathematics', NULL, 2),
(880, 'olevel', 'Tuesday', 5, 7, 'Geography', NULL, 2),
(881, 'olevel', 'Tuesday', 6, 7, 'Geography', NULL, 2),
(882, 'olevel', 'Tuesday', 7, 2, 'English', NULL, 2),
(883, 'olevel', 'Tuesday', 8, 12, 'ICT', NULL, 2),
(884, 'olevel', 'Tuesday', 9, 2, 'English', NULL, 2),
(885, 'olevel', 'Tuesday', 10, 14, 'Religious and Ethics Education', NULL, 2),
(886, 'olevel', 'Wednesday', 1, 1, 'Mathematics', NULL, 2),
(887, 'olevel', 'Wednesday', 2, 1, 'Mathematics', NULL, 2),
(888, 'olevel', 'Wednesday', 3, 1, 'Mathematics', NULL, 2),
(889, 'olevel', 'Wednesday', 4, 1, 'Mathematics', NULL, 2),
(890, 'olevel', 'Wednesday', 5, 2, 'English', NULL, 2),
(891, 'olevel', 'Wednesday', 6, 2, 'English', NULL, 2),
(892, 'olevel', 'Wednesday', 7, 2, 'English', NULL, 2),
(893, 'olevel', 'Wednesday', 8, 9, 'Kinyarwanda', NULL, 2),
(894, 'olevel', 'Wednesday', 9, 9, 'Kinyarwanda', NULL, 2),
(895, 'olevel', 'Wednesday', 10, 9, 'Kinyarwanda', NULL, 2),
(896, 'olevel', 'Thursday', 1, 5, 'Physics', NULL, 2),
(897, 'olevel', 'Thursday', 2, 5, 'Physics', NULL, 2),
(898, 'olevel', 'Thursday', 3, 4, 'Chemistry', NULL, 2),
(899, 'olevel', 'Thursday', 4, 4, 'Chemistry', NULL, 2),
(900, 'olevel', 'Thursday', 5, 10, 'French', NULL, 2),
(901, 'olevel', 'Thursday', 6, 7, 'Geography', NULL, 2),
(902, 'olevel', 'Thursday', 7, 8, 'Entrepreurship', NULL, 2),
(903, 'olevel', 'Thursday', 8, 8, 'Entrepreurship', NULL, 2),
(904, 'olevel', 'Thursday', 9, 8, 'Entrepreurship', NULL, 2),
(905, 'olevel', 'Friday', 1, 10, 'French', NULL, 2),
(906, 'olevel', 'Friday', 2, 10, 'French', NULL, 2),
(907, 'olevel', 'Friday', 3, 5, 'Physics', NULL, 2),
(908, 'olevel', 'Friday', 4, 5, 'Physics', NULL, 2),
(909, 'olevel', 'Friday', 7, 11, 'Kiswahili', NULL, 2),
(910, 'olevel', 'Friday', 8, 11, 'Kiswahili', NULL, 2),
(911, 'olevel', 'Friday', 9, 11, 'Kiswahili', NULL, 2),
(912, 'olevel', 'Monday', 1, 2, 'English', NULL, 3),
(913, 'olevel', 'Monday', 2, 2, 'English', NULL, 3),
(914, 'olevel', 'Monday', 3, 2, 'English', NULL, 3),
(915, 'olevel', 'Monday', 4, 2, 'English', NULL, 3),
(916, 'olevel', 'Monday', 5, 1, 'Mathematics', NULL, 3),
(917, 'olevel', 'Monday', 6, 1, 'Mathematics', NULL, 3),
(918, 'olevel', 'Monday', 7, 1, 'Mathematics', NULL, 3),
(919, 'olevel', 'Monday', 8, 14, 'Religious and Ethics Education', NULL, 3),
(920, 'olevel', 'Monday', 9, 2, 'English', NULL, 3),
(921, 'olevel', 'Monday', 10, 9, 'Kinyarwanda', NULL, 3),
(922, 'olevel', 'Tuesday', 1, 6, 'History', NULL, 3),
(923, 'olevel', 'Tuesday', 2, 6, 'History', NULL, 3),
(924, 'olevel', 'Tuesday', 3, 6, 'History', NULL, 3),
(925, 'olevel', 'Tuesday', 4, 7, 'Geography', NULL, 3),
(926, 'olevel', 'Tuesday', 5, 1, 'Mathematics', NULL, 3),
(927, 'olevel', 'Tuesday', 6, 1, 'Mathematics', NULL, 3),
(928, 'olevel', 'Tuesday', 7, 1, 'Mathematics', NULL, 3),
(929, 'olevel', 'Tuesday', 8, 9, 'Kinyarwanda', NULL, 3),
(930, 'olevel', 'Tuesday', 9, 12, 'ICT', NULL, 3),
(931, 'olevel', 'Tuesday', 10, 12, 'ICT', NULL, 3),
(932, 'olevel', 'Wednesday', 1, 7, 'Geography', NULL, 3),
(933, 'olevel', 'Wednesday', 2, 7, 'Geography', NULL, 3),
(934, 'olevel', 'Wednesday', 3, 8, 'Entrepreurship', NULL, 3),
(935, 'olevel', 'Wednesday', 4, 8, 'Entrepreurship', NULL, 3),
(936, 'olevel', 'Wednesday', 5, 4, 'Chemistry', NULL, 3),
(937, 'olevel', 'Wednesday', 6, 4, 'Chemistry', NULL, 3),
(938, 'olevel', 'Wednesday', 7, 4, 'Chemistry', NULL, 3),
(939, 'olevel', 'Wednesday', 8, 4, 'Chemistry', NULL, 3),
(940, 'olevel', 'Wednesday', 9, 5, 'Physics', NULL, 3),
(941, 'olevel', 'Wednesday', 10, 5, 'Physics', NULL, 3),
(942, 'olevel', 'Thursday', 1, 8, 'Entrepreurship', NULL, 3),
(943, 'olevel', 'Thursday', 2, 9, 'Kinyarwanda', NULL, 3),
(944, 'olevel', 'Thursday', 3, 10, 'French', NULL, 3),
(945, 'olevel', 'Thursday', 4, 10, 'French', NULL, 3),
(946, 'olevel', 'Thursday', 5, 5, 'Physics', NULL, 3),
(947, 'olevel', 'Thursday', 6, 5, 'Physics', NULL, 3),
(948, 'olevel', 'Thursday', 10, 13, 'Phyical Education and Sport', NULL, 3),
(949, 'olevel', 'Friday', 1, 3, 'Biology', NULL, 3),
(950, 'olevel', 'Friday', 2, 3, 'Biology', NULL, 3),
(951, 'olevel', 'Friday', 3, 3, 'Biology', NULL, 3),
(952, 'olevel', 'Friday', 4, 3, 'Biology', NULL, 3),
(953, 'olevel', 'Friday', 8, 13, 'Phyical Education and Sport', NULL, 3),
(954, 'olevel', 'Friday', 10, 10, 'French', NULL, 3),
(955, 'olevel', 'Monday', 1, 6, 'History', NULL, 4),
(956, 'olevel', 'Monday', 2, 6, 'History', NULL, 4),
(957, 'olevel', 'Monday', 3, 6, 'History', NULL, 4),
(958, 'olevel', 'Monday', 4, 7, 'Geography', NULL, 4),
(959, 'olevel', 'Monday', 5, 3, 'Biology', NULL, 4),
(960, 'olevel', 'Monday', 6, 3, 'Biology', NULL, 4),
(961, 'olevel', 'Monday', 7, 3, 'Biology', NULL, 4),
(962, 'olevel', 'Monday', 8, 2, 'English', NULL, 4),
(963, 'olevel', 'Monday', 9, 14, 'Religious and Ethics Education', NULL, 4),
(964, 'olevel', 'Monday', 10, 1, 'Mathematics', NULL, 4),
(965, 'olevel', 'Tuesday', 1, 2, 'English', NULL, 4),
(966, 'olevel', 'Tuesday', 2, 2, 'English', NULL, 4),
(967, 'olevel', 'Tuesday', 3, 2, 'English', NULL, 4),
(968, 'olevel', 'Tuesday', 4, 2, 'English', NULL, 4),
(969, 'olevel', 'Tuesday', 5, 3, 'Biology', NULL, 4),
(970, 'olevel', 'Tuesday', 6, 4, 'Chemistry', NULL, 4),
(971, 'olevel', 'Tuesday', 7, 4, 'Chemistry', NULL, 4),
(972, 'olevel', 'Tuesday', 8, 1, 'Mathematics', NULL, 4),
(973, 'olevel', 'Tuesday', 9, 13, 'Phyical Education and Sport', NULL, 4),
(974, 'olevel', 'Tuesday', 10, 9, 'Kinyarwanda', NULL, 4),
(975, 'olevel', 'Wednesday', 1, 9, 'Kinyarwanda', NULL, 4),
(976, 'olevel', 'Wednesday', 2, 9, 'Kinyarwanda', NULL, 4),
(977, 'olevel', 'Wednesday', 3, 10, 'French', NULL, 4),
(978, 'olevel', 'Wednesday', 4, 10, 'French', NULL, 4),
(979, 'olevel', 'Wednesday', 5, 1, 'Mathematics', NULL, 4),
(980, 'olevel', 'Wednesday', 6, 1, 'Mathematics', NULL, 4),
(981, 'olevel', 'Wednesday', 7, 1, 'Mathematics', NULL, 4),
(982, 'olevel', 'Wednesday', 8, 12, 'ICT', NULL, 4),
(983, 'olevel', 'Wednesday', 9, 12, 'ICT', NULL, 4),
(984, 'olevel', 'Wednesday', 10, 4, 'Chemistry', NULL, 4),
(985, 'olevel', 'Thursday', 1, 10, 'French', NULL, 4),
(986, 'olevel', 'Thursday', 2, 7, 'Geography', NULL, 4),
(987, 'olevel', 'Thursday', 3, 7, 'Geography', NULL, 4),
(988, 'olevel', 'Thursday', 4, 8, 'Entrepreurship', NULL, 4),
(989, 'olevel', 'Thursday', 5, 4, 'Chemistry', NULL, 4),
(990, 'olevel', 'Thursday', 7, 1, 'Mathematics', NULL, 4),
(991, 'olevel', 'Thursday', 8, 5, 'Physics', NULL, 4),
(992, 'olevel', 'Thursday', 9, 5, 'Physics', NULL, 4),
(993, 'olevel', 'Thursday', 10, 5, 'Physics', NULL, 4),
(994, 'olevel', 'Friday', 1, 8, 'Entrepreurship', NULL, 4),
(995, 'olevel', 'Friday', 2, 8, 'Entrepreurship', NULL, 4),
(996, 'olevel', 'Friday', 5, 5, 'Physics', NULL, 4),
(997, 'olevel', 'Friday', 9, 13, 'Phyical Education and Sport', NULL, 4),
(1026, 'tvet', 'Monday', 1, 3, 'Develop website', NULL, 5),
(1027, 'tvet', 'Monday', 2, 3, 'Develop website', NULL, 5),
(1028, 'tvet', 'Monday', 3, 3, 'Develop website', NULL, 5),
(1029, 'tvet', 'Monday', 4, 3, 'Develop website', NULL, 5),
(1030, 'tvet', 'Monday', 5, 1, 'Analyse project requirements', NULL, 5),
(1031, 'tvet', 'Monday', 6, 1, 'Analyse project requirements', NULL, 5),
(1032, 'tvet', 'Monday', 7, 2, 'Apply JavaScript', NULL, 5),
(1033, 'tvet', 'Monday', 8, 7, 'Pratiquer les activitÃ©s de communication dans le mÃ©tier', NULL, 5),
(1034, 'tvet', 'Monday', 9, 7, 'Pratiquer les activitÃ©s de communication dans le mÃ©tier', NULL, 5),
(1035, 'tvet', 'Monday', 10, 10, 'Apply computer literacy', NULL, 5),
(1036, 'tvet', 'Tuesday', 1, 3, 'Develop website', NULL, 5),
(1037, 'tvet', 'Tuesday', 2, 3, 'Develop website', NULL, 5),
(1038, 'tvet', 'Tuesday', 3, 4, 'Apply Algebra and Trigonometry', NULL, 5),
(1039, 'tvet', 'Tuesday', 4, 4, 'Apply Algebra and Trigonometry', NULL, 5),
(1040, 'tvet', 'Tuesday', 5, 2, 'Apply JavaScript', NULL, 5),
(1041, 'tvet', 'Tuesday', 6, 5, 'Apply general physics', NULL, 5),
(1042, 'tvet', 'Tuesday', 7, 5, 'Apply general physics', NULL, 5),
(1043, 'tvet', 'Tuesday', 8, 10, 'Apply computer literacy', NULL, 5),
(1044, 'tvet', 'Tuesday', 9, 8, 'Gukoresha ikinyarwanda kiboneye', NULL, 5),
(1045, 'tvet', 'Tuesday', 10, 8, 'Gukoresha ikinyarwanda kiboneye', NULL, 5),
(1046, 'tvet', 'Wednesday', 1, 4, 'Apply Algebra and Trigonometry', NULL, 5),
(1047, 'tvet', 'Wednesday', 2, 4, 'Apply Algebra and Trigonometry', NULL, 5),
(1048, 'tvet', 'Wednesday', 3, 6, 'Apply basic graphics design', NULL, 5),
(1049, 'tvet', 'Wednesday', 4, 6, 'Apply basic graphics design', NULL, 5),
(1050, 'tvet', 'Wednesday', 5, 9, 'Use Pre-intermediate English at the Workplace', NULL, 5),
(1051, 'tvet', 'Wednesday', 6, 9, 'Use Pre-intermediate English at the Workplace', NULL, 5),
(1052, 'tvet', 'Thursday', 1, 6, 'Apply basic graphics design', NULL, 5),
(1053, 'tvet', 'Thursday', 2, 6, 'Apply basic graphics design', NULL, 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1054;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
