-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Maj 20, 2025 at 01:21 PM
-- Wersja serwera: 10.4.28-MariaDB
-- Wersja PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `HRDASHBOARD`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `grafik_pracy`
--

CREATE TABLE `grafik_pracy` (
  `id` int(11) NOT NULL,
  `pracownik_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `godzina_rozpoczecia` time NOT NULL,
  `godzina_zakonczenia` time NOT NULL,
  `status` enum('obecny','nieobecny','urlop','chorobowe') DEFAULT 'obecny',
  `uwagi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grafik_pracy`
--

INSERT INTO `grafik_pracy` (`id`, `pracownik_id`, `data`, `godzina_rozpoczecia`, `godzina_zakonczenia`, `status`, `uwagi`) VALUES
(1, 1, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(2, 2, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(3, 3, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(4, 4, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(5, 5, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(6, 6, '2025-05-02', '08:00:00', '16:00:00', 'urlop', 'Wakacje'),
(7, 7, '2025-05-02', '08:00:00', '16:00:00', 'urlop', 'Wakacje'),
(8, 8, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(9, 9, '2025-05-02', '10:00:00', '16:00:00', 'urlop', 'Wakacje'),
(11, 11, '2025-05-02', '08:00:00', '16:00:00', 'urlop', 'Wakacje'),
(13, 13, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(14, 14, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(15, 15, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(16, 16, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(18, 18, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(19, 19, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(20, 20, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(21, 21, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(22, 22, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(23, 23, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(24, 24, '2025-05-02', '08:00:00', '16:00:00', 'urlop', 'Wakacje'),
(25, 25, '2025-05-02', '10:00:00', '18:00:00', 'obecny', ''),
(26, 1, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(27, 2, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(28, 3, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(29, 4, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(30, 5, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(31, 6, '2025-05-05', '08:00:00', '16:00:00', 'urlop', 'Wakacje'),
(32, 7, '2025-05-05', '08:00:00', '16:00:00', 'urlop', 'Wakacje'),
(33, 8, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(34, 9, '2025-05-05', '08:00:00', '16:00:00', 'urlop', 'Wakacje'),
(35, 10, '2025-05-05', '10:00:00', '16:00:00', 'urlop', 'Wakacje'),
(36, 11, '2025-05-05', '08:00:00', '16:00:00', 'urlop', 'Wakacje'),
(37, 12, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(38, 13, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(39, 14, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(40, 15, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(41, 16, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(42, 17, '2025-05-05', '08:00:00', '16:00:00', 'urlop', 'Wakacje'),
(43, 18, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(44, 19, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(45, 20, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(46, 21, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(47, 22, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(48, 23, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(49, 24, '2025-05-05', '08:00:00', '16:00:00', 'urlop', 'Wakacje'),
(50, 25, '2025-05-05', '10:00:00', '18:00:00', 'obecny', ''),
(51, 1, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(52, 2, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(53, 3, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(54, 5, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(55, 8, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(56, 12, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(57, 13, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(58, 14, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(59, 15, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(60, 16, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(61, 18, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(62, 19, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(63, 20, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(64, 21, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(65, 25, '2025-05-06', '10:00:00', '18:00:00', 'obecny', ''),
(66, 1, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(67, 2, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(68, 3, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(69, 5, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(70, 8, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(71, 12, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(72, 13, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(73, 14, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(74, 15, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(75, 16, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(76, 18, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(77, 19, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(78, 20, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(79, 21, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(80, 25, '2025-05-07', '10:00:00', '18:00:00', 'obecny', ''),
(81, 1, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(82, 2, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(83, 3, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(84, 5, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(85, 8, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(87, 13, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(88, 14, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(89, 15, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(90, 16, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(91, 18, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(92, 19, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(93, 20, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(94, 21, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(95, 25, '2025-05-08', '10:00:00', '18:00:00', 'obecny', ''),
(96, 1, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(97, 2, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(98, 3, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(99, 5, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(103, 14, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(104, 15, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(105, 16, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(106, 18, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(107, 19, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(108, 20, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(109, 21, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(110, 25, '2025-05-09', '10:00:00', '18:00:00', 'obecny', ''),
(111, 1, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(112, 2, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(113, 3, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(114, 5, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(115, 8, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(116, 12, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(117, 13, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(118, 14, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(119, 15, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(120, 16, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(121, 18, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(122, 19, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(123, 20, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(124, 21, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(125, 25, '2025-05-12', '10:00:00', '18:00:00', 'obecny', ''),
(126, 1, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(127, 2, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(128, 3, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(129, 5, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(130, 8, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(131, 12, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(132, 13, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(133, 14, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(134, 15, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(135, 16, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(136, 18, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(137, 19, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(138, 20, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(139, 21, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(140, 25, '2025-05-13', '10:00:00', '18:00:00', 'obecny', ''),
(141, 1, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(142, 2, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(143, 3, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(144, 5, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(145, 8, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(146, 12, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(147, 13, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(148, 14, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(149, 15, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(150, 16, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(151, 18, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(152, 19, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(153, 20, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(154, 21, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(155, 25, '2025-05-14', '10:00:00', '18:00:00', 'obecny', ''),
(156, 1, '2025-05-15', '10:00:00', '18:00:00', 'obecny', ''),
(157, 2, '2025-05-15', '10:00:00', '18:00:00', 'obecny', ''),
(158, 3, '2025-05-15', '10:00:00', '18:00:00', 'obecny', ''),
(159, 5, '2025-05-15', '10:00:00', '18:00:00', 'obecny', ''),
(160, 8, '2025-05-15', '10:00:00', '18:00:00', 'obecny', ''),
(161, 12, '2025-05-15', '10:00:00', '18:00:00', 'obecny', ''),
(162, 13, '2025-05-15', '10:00:00', '18:00:00', 'obecny', ''),
(163, 14, '2025-05-15', '10:00:00', '18:00:00', 'obecny', ''),
(164, 15, '2025-05-15', '10:00:00', '18:00:00', 'obecny', ''),
(165, 16, '2025-05-15', '10:00:00', '18:00:00', 'obecny', ''),
(174, 22, '2025-05-22', '08:00:00', '16:00:00', 'obecny', NULL),
(175, 8, '2025-05-22', '08:00:00', '16:00:00', 'obecny', NULL),
(176, 6, '2025-05-22', '08:00:00', '16:00:00', 'obecny', NULL),
(179, 13, '2025-05-30', '08:00:00', '16:00:00', 'obecny', NULL),
(180, 22, '2025-05-29', '08:00:00', '16:00:00', 'obecny', NULL),
(186, 17, '2025-05-20', '08:00:00', '16:00:00', 'obecny', NULL),
(187, 23, '2025-05-20', '08:00:00', '16:00:00', 'obecny', NULL),
(189, 14, '2025-05-20', '08:00:00', '16:00:00', 'obecny', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pracownicy`
--

CREATE TABLE `pracownicy` (
  `id` int(11) NOT NULL,
  `imie` varchar(100) NOT NULL,
  `nazwisko` varchar(100) NOT NULL,
  `dzial` varchar(100) NOT NULL,
  `stanowisko` varchar(100) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `urlop` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pracownicy`
--

INSERT INTO `pracownicy` (`id`, `imie`, `nazwisko`, `dzial`, `stanowisko`, `telefon`, `email`, `urlop`) VALUES
(1, 'Rozalia', 'Majewska', 'Dział HR', 'Kierownik działu', '500111222', 'rozalia.majewska@firma.pl', 0),
(2, 'Bartosz', 'Lewandowski', 'Dział HR', 'Rekruter', '500111223', '0', 0),
(3, 'Jakub', 'Wójcik', 'Dział HR', 'Pracownik do spraw szkoleń', '5001112240', 'jakub.wojcik@firma.pl', 1),
(4, 'Karolina', 'Mazur', 'Dział HR', 'Pracownik 1', '500111225', 'karolina.mazur@firma.pl', 0),
(5, 'Natalia', 'Kowalczyk', 'Dział HR', 'Pracownik 2', '500111226', 'natalia.kowalczyk@firma.pl', 0),
(6, 'Bartosz', 'Sakowicz', 'Dział HR', 'Pracownik 3', '500111227', 'bartosz.sakowicz@firma.pl', 1),
(7, 'Tomasz', 'Gajda', 'Dział Operacyjny', 'Kierownik operacyjny kina', '500111228', 'tomasz.gajda@firma.pl', 0),
(8, 'Mateusz', 'Domański', 'Dział Operacyjny', 'Koordynator sal kinowych', '500111229', 'mateusz.domanski@firma.pl', 0),
(9, 'Kacper', 'Lis', 'Dział Operacyjny', 'Bileter 1', '500111230', 'kacper.lis@firma.pl', 0),
(10, 'Weronika', 'Baran', 'Dział Operacyjny', 'Bileter 2', '500111231', 'weronika.baran@firma.pl', 0),
(11, 'Klaudia', 'Szymańska', 'Dział Operacyjny', 'Bileter 3', '500111232', 'klaudia.szymanska@firma.pl', 1),
(12, 'Michał', 'Borkowski', 'Dział Operacyjny', 'Bileter 4', '500111233', 'michal.borkowski@firma.pl', 0),
(13, 'Zuzanna', 'Jankowska', 'Dział Obsługi technicznej', 'Kierownik działu technicznego', '500111234', 'zuzanna.jankowska@firma.pl', 0),
(14, 'Patryk', 'Król', 'Dział Obsługi technicznej', 'Administrator systemu', '500111235', 'patryk.krol@firma.pl', 0),
(15, 'Alicja', 'Pawlak', 'Dział Obsługi technicznej', 'Technik sal kinowych 1', '500111236', 'alicja.pawlak@firma.pl', 0),
(16, 'Jakub', 'Sokołowski', 'Dział Obsługi technicznej', 'Technik sal kinowych 2', '500111237', 'jakub.sokolowski@firma.pl', 0),
(17, 'Maja', 'Dudek', 'Dział Sprzedaży i baru', 'Kierownik sprzedaży', '500111238', 'maja.dudek@firma.pl', 1),
(18, 'Filip', 'Maj', 'Dział Sprzedaży i baru', 'Pracownik baru 1', '500111239', 'filip.maj@firma.pl', 0),
(19, 'Karolina', 'Stępień', 'Dział Sprzedaży i baru', 'Pracownik baru 2', '500111240', 'karolina.stepien@firma.pl', 1),
(20, 'Dominika', 'Malinowska', 'Dział Sprzedaży i baru', 'Pracownik baru 3', '500111241', 'dominika.malinowska@firma.pl', 0),
(21, 'Wiktoria', 'Zawadzka', 'Dział Sprzedaży i baru', 'Pracownik baru 4', '500111242', 'wiktoria.zawadzka@firma.pl', 0),
(22, 'Adrian', 'Hajek', 'Dział Finansowy', 'Główny księgowy', '500111243', 'adrian.hajek@firma.pl', 0),
(23, 'Szymon', 'Kubiak', 'Dział Finansowy', 'Księgowy', '500111244', 'szymon.kubiak@firma.pl', 0),
(24, 'Julia', 'Wrona', 'Dział Finansowy', 'Asystent księgowego', '500111245', 'julia.wrona@firma.pl', 1),
(25, 'Jan', 'Kowalski', 'Zarząd', 'Prezes kina', '500111246', 'jan.kowalski@firma.pl', 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `urlopy`
--

CREATE TABLE `urlopy` (
  `id` int(11) NOT NULL,
  `pracownik_id` int(11) NOT NULL,
  `data_rozpoczecia` date NOT NULL,
  `data_zakonczenia` date NOT NULL,
  `powod` varchar(255) DEFAULT NULL,
  `status` enum('oczekujący','zatwierdzony','odrzucony') DEFAULT 'oczekujący'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `urlopy`
--

INSERT INTO `urlopy` (`id`, `pracownik_id`, `data_rozpoczecia`, `data_zakonczenia`, `powod`, `status`) VALUES
(1, 1, '2025-05-10', '2025-05-15', 'Wyjazd rodzinny', 'zatwierdzony'),
(2, 3, '2025-05-20', '2025-05-25', 'Sprawy osobiste', 'zatwierdzony'),
(3, 4, '2025-05-12', '2025-05-13', 'Krótkie wolne', 'odrzucony'),
(4, 14, '2025-05-18', '2025-05-22', 'Wypalenie zawodowe', 'odrzucony'),
(5, 17, '2025-05-14', '2025-05-17', 'Wyjazd służbowy', 'zatwierdzony');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'hr'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'hr_admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'hr');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `grafik_pracy`
--
ALTER TABLE `grafik_pracy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pracownik_id` (`pracownik_id`);

--
-- Indeksy dla tabeli `pracownicy`
--
ALTER TABLE `pracownicy`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `urlopy`
--
ALTER TABLE `urlopy`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `grafik_pracy`
--
ALTER TABLE `grafik_pracy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `pracownicy`
--
ALTER TABLE `pracownicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `urlopy`
--
ALTER TABLE `urlopy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grafik_pracy`
--
ALTER TABLE `grafik_pracy`
  ADD CONSTRAINT `grafik_pracy_ibfk_1` FOREIGN KEY (`pracownik_id`) REFERENCES `pracownicy` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
