-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2026 at 09:23 AM
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
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `certificate`
--

CREATE TABLE `certificate` (
  `regNo` varchar(5) NOT NULL,
  `studentId` int(11) NOT NULL,
  `courseid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `course_year` int(11) NOT NULL,
  `course_duration` varchar(20) NOT NULL,
  `course_conducted_by` int(11) NOT NULL,
  `course_begin_date` date NOT NULL,
  `course_end_date` date NOT NULL,
  `course_category` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses_on_web`
--

CREATE TABLE `courses_on_web` (
  `course_id` varchar(10) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `course_image_land` varchar(50) NOT NULL,
  `course_image_detail` varchar(50) NOT NULL,
  `course_instructor` varchar(100) NOT NULL,
  `course_description` varchar(300) NOT NULL,
  `course_duration` varchar(10) NOT NULL,
  `course_fee` float NOT NULL,
  `course_contents` varchar(600) NOT NULL,
  `course_category` varchar(20) NOT NULL,
  `extra` varchar(300) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `courses_on_web`
--

INSERT INTO `courses_on_web` (`course_id`, `course_name`, `course_image_land`, `course_image_detail`, `course_instructor`, `course_description`, `course_duration`, `course_fee`, `course_contents`, `course_category`, `extra`, `is_active`, `created_at`) VALUES
('english', 'Practical English', '/images/courses/6a3cdfd3670797.56311909.jpg', '/images/courses/6a3cdfd36820c9.83747582.jpg', 'Mrs. R.T.S. Ranasinghe', 'Communicative English for students focusing on workplace and technical communication.', '120 hours', 6000, 'Grammar Basics;Workplace Communication;Technical Writing;Presentation Skills', 'ENGLISH', 'Certification issued under the Ministry of Education of Sri Lanka', 1, '2026-06-25 07:59:15'),
('graphic', 'Graphic Designing', '/images/courses/6a3cded6280ee2.05378312.jpg', '/images/courses/6a3cded6295d21.15208276.jpg', 'Mr. T.K.C Talpavila', 'Design fundamentals, image editing and tools like Photoshop and Illustrator.', '120 hours', 7500, 'Design Fundamentals;Image Editing;Photoshop;Illustrator;CorelDraw', 'GRAPHIC', 'Certification issued under the Ministry of Education of Sri Lanka', 1, '2026-06-25 07:55:02'),
('java', 'Programming with Java', '/images/courses/6a3cbbddc3ff65.14766549.jpg', '/images/courses/6a3cbbddc560d5.99375487.jpg', 'Mr. B.D.P. Niranjan', 'Object-oriented programming, Java fundamentals and application development.', '120 hours', 7500, 'Introduction to Programming;Java Basics;OOP with Java;Java Application Development;connecting Java with Databases', 'JAVA', 'Certification issued under the Ministry of Education of Sri Lanka', 1, '2026-06-25 05:25:49'),
('mso', 'MS Office Applications', '/images/courses/mso.png', '/images/courses/mso.png', 'L.A.S. Kumargasinghe', 'Comprehensive, practical training on Microsoft Office suite and essential desktop skills for workplace productivity.', '120 hours', 7500, 'Introduction;Windows OS;MS Word;MS Excel;MS PowerPoint;MS Access;Internet And Email', 'MSO', 'Certification issued under the Ministry of Education of Sri Lanka', 1, '2026-06-24 10:54:43'),
('php', 'Programming with PHP', '/images/courses/6a3cbd289e6d55.46655313.jpg', '/images/courses/6a3cbd289f1b32.17738875.jpg', 'Mr. B.D.P. Niranjan', 'Server-side web development with PHP: building dynamic websites and simple APIs.', '120 hours', 7500, 'Introduction to PHP;PHP Basics;OOP with PHP;Building Dynamic Websites', 'PHP', 'Certification issued under the Ministry of Education of Sri Lanka', 1, '2026-06-25 05:31:20'),
('python', 'Programming with Python', '/images/courses/6a3cb4f92923a9.13538143.webp', '/images/courses/6a3cb4f92a0125.65292221.webp', 'B.D.P. Niranjan', 'Intro to Python programming, data structures, scripting and practical projects.', '120 hours', 7500, 'Introduction to Programming;Python Basics;OOP with Python', 'PYTHON', 'Certification issued under the Ministry of Education of Sri Lanka', 1, '2026-06-25 04:57:08'),
('web', 'Web Designing', '/images/courses/6a3cacd4dc1e52.63442263.webp', '/images/courses/6a3cacd4dcd4f8.53794587.webp', 'B.D.P. Niranjan', 'Learn HTML, CSS, responsive design and modern frontend tooling to build attractive websites.', '120 hours', 7500, 'Introduction;HTML 5.0;CSS 3.0;JavaScript;Bootstrap;wordpress;Responsive Design;Modern Frontend Tooling', 'WEB', 'Certification issued under the Ministry of Education of Sri Lanka', 1, '2026-06-24 14:58:48');

-- --------------------------------------------------------

--
-- Table structure for table `course_category`
--

CREATE TABLE `course_category` (
  `category_id` varchar(10) NOT NULL,
  `category_name` varchar(300) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `course_category`
--

INSERT INTO `course_category` (`category_id`, `category_name`, `created_at`) VALUES
('ENGLISH', 'Practical English', '2026-06-24 10:21:49'),
('GRAPHIC', 'Graphics Designing', '2026-06-24 06:19:56'),
('JAVA', 'Programming with Java', '2026-06-24 06:26:16'),
('MSO', 'MS Office Applications', '0000-00-00 00:00:00'),
('PHP', 'Programming with PHP', '2026-06-26 05:56:29'),
('PYTHON', 'Programming with Python', '2026-06-24 06:24:53'),
('WEB', 'Web Designing', '2026-06-24 14:31:09');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` int(11) NOT NULL,
  `subjectId` int(11) NOT NULL,
  `studentId` int(11) NOT NULL,
  `marks` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `migration` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `created_at`) VALUES
(1, 'm0001_initial.php', '2026-06-19 10:48:38'),
(2, 'm0002_add_password_column.php', '2026-06-19 10:48:38'),
(3, 'm0001_initials.php', '2026-06-20 14:02:23');

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
  `regNo` varchar(5) NOT NULL,
  `studentId` int(11) NOT NULL,
  `couseId` int(11) NOT NULL,
  `course_group` varchar(4) NOT NULL,
  `course_year` int(11) NOT NULL,
  `is_uder_previladge` tinyint(1) NOT NULL,
  `course_fee` float NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `staff_name` varchar(200) NOT NULL,
  `staff_nic` varchar(12) NOT NULL,
  `staff_tel` varchar(10) NOT NULL,
  `staff_watsapp` varchar(10) NOT NULL,
  `gender` varchar(19) NOT NULL,
  `staff_address` varchar(300) NOT NULL,
  `staff_type` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `full-name` varchar(200) NOT NULL,
  `name_with_initials` varchar(100) NOT NULL,
  `birthday` date DEFAULT NULL,
  `address` varchar(400) NOT NULL,
  `Tel` varchar(10) NOT NULL,
  `mobile` varchar(10) NOT NULL,
  `whatsapp` varchar(10) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `nic` varchar(12) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 0,
  `loging_id` varchar(5) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `email`, `full-name`, `name_with_initials`, `birthday`, `address`, `Tel`, `mobile`, `whatsapp`, `gender`, `nic`, `status`, `loging_id`, `created_at`) VALUES
(1, 'prageeth79@gmail.com', '', '', NULL, '', '', '', '', '', '197921209893', 0, '', '2026-06-19 10:50:07'),
(2, 'prageeth1979@gmail.com', '', '', NULL, '', '', '', '', '', '197909898787', 0, '', '2026-06-19 12:23:38');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(200) NOT NULL,
  `course_category` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `loging_id` varchar(10) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `category` varchar(10) NOT NULL,
  `status` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`loging_id`, `firstName`, `lastName`, `email`, `password`, `category`, `status`, `created_at`) VALUES
('admin', 'Prageeth', 'Niranjan', 'prageeth79@gmail.com', '$2y$10$Zcae6zwy0N0VWXl6ydTcwuLfaXOeWrgOAZAAC52.ve2/x3Kh4qeve', 'admin', 0, '2026-06-21 04:57:27'),
('instructor', 'instructor', 'instrouctor', 'instructor@itdlh.com', '$2y$10$YUKeXy51SYW2LW4Zmc6NMukVvz0kqw1VXuXB.5DsWtgPv2PAb7rNW', 'instructor', 0, '2026-06-21 05:00:40'),
('student', 'Student', 'Student', 'student@itdlh.com', '$2y$10$ytqqVHADW.3ky60T9G7spu5gY1lOdPs/UqFi6DiMSD8nZTsQmeOAq', 'student', 0, '2026-06-21 04:59:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses_on_web`
--
ALTER TABLE `courses_on_web`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `course_category`
--
ALTER TABLE `course_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`regNo`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nic` (`nic`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`loging_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
