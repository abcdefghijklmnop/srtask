-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2021. Okt 12. 16:46
-- Kiszolgáló verziója: 10.4.17-MariaDB
-- PHP verzió: 8.0.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `srtask`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `secret`
--

CREATE TABLE `secret` (
  `hash` varbinary(255) NOT NULL,
  `secretText` blob DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `expiresAt` datetime DEFAULT NULL,
  `remainingViews` int(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `secret`
--
ALTER TABLE `secret`
  ADD PRIMARY KEY (`hash`),
  ADD UNIQUE KEY `hash_UNIQUE` (`hash`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
