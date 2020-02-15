-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 15 Şub 2020, 16:28:41
-- Sunucu sürümü: 10.4.11-MariaDB
-- PHP Sürümü: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `server`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `chat_permissions`
--

CREATE TABLE `chat_permissions` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Tablo döküm verisi `chat_permissions`
--

INSERT INTO `chat_permissions` (`id`, `userId`, `type`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `log_event_jpb`
--

CREATE TABLE `log_event_jpb` (
  `id` int(11) NOT NULL,
  `players` text COLLATE utf8_bin NOT NULL,
  `finalists` text COLLATE utf8_bin NOT NULL,
  `winner_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `log_player_kills`
--

CREATE TABLE `log_player_kills` (
  `id` int(11) NOT NULL,
  `killer_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `pushing` tinyint(1) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `player_accounts`
--

CREATE TABLE `player_accounts` (
  `userId` bigint(20) NOT NULL,
  `sessionId` varchar(32) COLLATE utf8_bin NOT NULL,
  `data` text COLLATE utf8_bin NOT NULL DEFAULT '{"uridium":0,"credits":0,"honor":0,"experience":0,"jackpot":0}',
  `bootyKeys` text COLLATE utf8_bin NOT NULL DEFAULT '{"greenKeys": 0, "redKeys": 0, "blueKeys": 0}',
  `info` text COLLATE utf8_bin NOT NULL,
  `destructions` text COLLATE utf8_bin NOT NULL DEFAULT '{"fpd":0,"dbrz":0}',
  `username` varchar(20) COLLATE utf8_bin NOT NULL,
  `pilotName` varchar(20) COLLATE utf8_bin NOT NULL,
  `petName` varchar(20) COLLATE utf8_bin NOT NULL DEFAULT 'P.E.T 15',
  `password` varchar(255) COLLATE utf8_bin NOT NULL,
  `email` varchar(260) COLLATE utf8_bin NOT NULL,
  `shipId` int(11) NOT NULL DEFAULT 10,
  `premium` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(128) COLLATE utf8_bin NOT NULL,
  `factionId` int(1) NOT NULL DEFAULT 0,
  `clanId` int(11) NOT NULL DEFAULT 0,
  `rankId` int(2) NOT NULL DEFAULT 1,
  `rankPoints` bigint(20) NOT NULL DEFAULT 0,
  `rank` int(11) NOT NULL DEFAULT 0,
  `warPoints` bigint(20) NOT NULL,
  `warRank` int(11) NOT NULL,
  `extraEnergy` int(11) NOT NULL,
  `nanohull` int(11) NOT NULL,
  `verification` text COLLATE utf8_bin NOT NULL,
  `oldPilotNames` text COLLATE utf8_bin NOT NULL DEFAULT '[]',
  `version` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `player_equipment`
--

CREATE TABLE `player_equipment` (
  `userId` int(11) NOT NULL,
  `config1_lasers` text COLLATE utf8_bin NOT NULL DEFAULT '[]',
  `config1_generators` text COLLATE utf8_bin NOT NULL DEFAULT '[]',
  `config1_drones` text COLLATE utf8_bin NOT NULL DEFAULT '[{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]}]',
  `config2_lasers` text COLLATE utf8_bin NOT NULL DEFAULT '[]',
  `config2_generators` text COLLATE utf8_bin NOT NULL DEFAULT '[]',
  `config2_drones` text COLLATE utf8_bin NOT NULL DEFAULT '[{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]},{"items":[],"designs":[]}]',
  `items` text COLLATE utf8_bin NOT NULL DEFAULT '{"lf4Count":0,"havocCount":0,"herculesCount":0,"apis":false,"zeus":false,"pet":false,"petModules":[],"ships":[],"designs":{},"skillTree":{"logdisks":0,"researchPoints":0,"resetCount":0}}',
  `skill_points` text COLLATE utf8_bin NOT NULL DEFAULT '{"engineering":0,"shieldEngineering":0,"detonation1":0,"detonation2":0,"heatseekingMissiles":0,"rocketFusion":0,"cruelty1":0,"cruelty2":0,"explosives":0,"luck1":0,"luck2":0}',
  `modules` longtext COLLATE utf8_bin NOT NULL DEFAULT '[]',
  `boosters` longtext COLLATE utf8_bin NOT NULL DEFAULT '{}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `player_galaxygates`
--

CREATE TABLE `player_galaxygates` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `gateId` int(11) NOT NULL,
  `parts` longtext COLLATE utf8_bin NOT NULL DEFAULT '[]',
  `multiplier` int(11) NOT NULL DEFAULT 0,
  `lives` int(11) NOT NULL DEFAULT -1,
  `prepared` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `player_settings`
--

CREATE TABLE `player_settings` (
  `userId` int(11) NOT NULL,
  `audio` text COLLATE utf8_bin NOT NULL,
  `quality` text COLLATE utf8_bin NOT NULL,
  `classY2T` text COLLATE utf8_bin NOT NULL,
  `display` text COLLATE utf8_bin NOT NULL,
  `gameplay` text COLLATE utf8_bin NOT NULL,
  `window` text COLLATE utf8_bin NOT NULL,
  `boundKeys` text COLLATE utf8_bin NOT NULL,
  `inGameSettings` text COLLATE utf8_bin NOT NULL,
  `cooldowns` text COLLATE utf8_bin NOT NULL,
  `slotbarItems` text COLLATE utf8_bin NOT NULL,
  `premiumSlotbarItems` text COLLATE utf8_bin NOT NULL,
  `proActionBarItems` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `player_titles`
--

CREATE TABLE `player_titles` (
  `userID` int(11) NOT NULL,
  `titles` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '[]'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `server_bans`
--

CREATE TABLE `server_bans` (
  `id` bigint(20) NOT NULL,
  `userId` int(11) NOT NULL,
  `modId` int(11) NOT NULL,
  `reason` text COLLATE utf8_bin NOT NULL,
  `typeId` tinyint(4) NOT NULL,
  `ended` tinyint(1) NOT NULL,
  `end_date` datetime NOT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `server_battlestations`
--

CREATE TABLE `server_battlestations` (
  `id` int(11) NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `mapId` int(11) NOT NULL,
  `clanId` int(11) NOT NULL,
  `positionX` int(11) NOT NULL,
  `positionY` int(11) NOT NULL,
  `inBuildingState` tinyint(4) NOT NULL,
  `buildTimeInMinutes` int(11) NOT NULL,
  `buildTime` datetime NOT NULL,
  `deflectorActive` tinyint(4) NOT NULL,
  `deflectorSecondsLeft` int(11) NOT NULL,
  `deflectorTime` datetime NOT NULL,
  `visualModifiers` text COLLATE utf8_bin NOT NULL,
  `modules` longtext COLLATE utf8_bin NOT NULL,
  `active` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Tablo döküm verisi `server_battlestations`
--

INSERT INTO `server_battlestations` (`id`, `name`, `mapId`, `clanId`, `positionX`, `positionY`, `inBuildingState`, `buildTimeInMinutes`, `buildTime`, `deflectorActive`, `deflectorSecondsLeft`, `deflectorTime`, `visualModifiers`, `modules`, `active`) VALUES
(1, 'Julius', 15, 0, 16400, 11400, 0, 0, '0000-00-00 00:00:00', 0, 0, '0001-01-01 00:00:00', '[]', '[]', 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `server_clans`
--

CREATE TABLE `server_clans` (
  `id` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `tag` varchar(4) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `description` varchar(16000) COLLATE utf8_bin NOT NULL DEFAULT '',
  `factionId` tinyint(4) NOT NULL DEFAULT 0,
  `recruiting` tinyint(4) NOT NULL DEFAULT 1,
  `leaderId` int(11) NOT NULL DEFAULT 0,
  `news` text COLLATE utf8_bin NOT NULL DEFAULT '[]',
  `join_dates` text COLLATE utf8_bin NOT NULL DEFAULT '{}',
  `rankPoints` bigint(20) NOT NULL,
  `rank` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `profile` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `server_clan_applications`
--

CREATE TABLE `server_clan_applications` (
  `id` int(11) NOT NULL,
  `clanId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `text` varchar(255) COLLATE utf8_bin NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `server_clan_diplomacy`
--

CREATE TABLE `server_clan_diplomacy` (
  `id` bigint(20) NOT NULL,
  `senderClanId` int(11) NOT NULL,
  `toClanId` int(11) NOT NULL,
  `diplomacyType` tinyint(4) NOT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `server_clan_diplomacy_applications`
--

CREATE TABLE `server_clan_diplomacy_applications` (
  `id` bigint(20) NOT NULL,
  `senderClanId` int(11) NOT NULL,
  `toClanId` int(11) NOT NULL,
  `diplomacyType` tinyint(4) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `server_maps`
--

CREATE TABLE `server_maps` (
  `mapID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `npcs` longtext COLLATE utf8_bin NOT NULL,
  `stations` longtext COLLATE utf8_bin NOT NULL,
  `portals` longtext COLLATE utf8_bin NOT NULL,
  `collectables` longtext COLLATE utf8_bin NOT NULL,
  `options` varchar(512) COLLATE utf8_bin NOT NULL DEFAULT '{"StarterMap":false,"PvpMap":false,"RangeDisabled":false,"CloakBlocked":false,"LogoutBlocked":false,"DeathLocationRepair":true}',
  `factionID` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Tablo döküm verisi `server_maps`
--

INSERT INTO `server_maps` (`mapID`, `name`, `npcs`, `stations`, `portals`, `collectables`, `options`, `factionID`) VALUES
(1, '1-1', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(2, '1-2', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(3, '1-3', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(4, '1-4', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(5, '2-1', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(6, '2-2', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(7, '2-3', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(8, '2-4', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(9, '3-1', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(10, '3-2', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(11, '3-3', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(12, '3-4', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(13, '4-1', '[{   \"ShipId\": 79,   \"Amount\": 7},{   \"ShipId\": 78,   \"Amount\":12},{   \"ShipId\": 35,   \"Amount\":2},{   \"ShipId\": 29,   \"Amount\":4}]', '[{   \"TypeId\": 46,   \"FactionId\": 1,   \"Position\": [1600,1600] }]', '[{   \"TargetSpaceMapId\": 16,   \"Position\": [10000, 6200],   \"TargetPosition\": [19200,13400],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true },{   \"TargetSpaceMapId\": 14,   \"Position\": [18900, 1900],   \"TargetPosition\": [2500,10900],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true },{   \"TargetSpaceMapId\": 15,   \"Position\": [18900, 11300],   \"TargetPosition\": [2000,11200],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true }]', '[{   \"TypeId\": 2,   \"Amount\": 100,   \"TopLeft\": [18300,1100], \"BottomRight\": [18300,1100], \"Respawnable\":true }]', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(14, '4-2', '[{   \"ShipId\": 79,   \"Amount\": 7},{   \"ShipId\": 78,   \"Amount\":12},{   \"ShipId\": 35,   \"Amount\":2},{   \"ShipId\": 29,   \"Amount\":4}]', '[{   \"TypeId\": 46,   \"FactionId\": 2,   \"Position\": [19500,1500] }]', '[{   \"TargetSpaceMapId\": 16,   \"Position\": [10400, 6300],   \"TargetPosition\": [21900,11900],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true }, {   \"TargetSpaceMapId\": 13,   \"Position\": [2500, 10900],   \"TargetPosition\": [18900,1900],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true }, {   \"TargetSpaceMapId\": 15,   \"Position\": [18900, 10900],   \"TargetPosition\": [2000,1900],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true }]', '[{   \"TypeId\": 2,   \"Amount\": 100,   \"TopLeft\": [18300,1100], \"BottomRight\": [18300,1100], \"Respawnable\":true }]', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(15, '4-3', '[{   \"ShipId\": 79,   \"Amount\": 7},{   \"ShipId\": 78,   \"Amount\":12},{   \"ShipId\": 35,   \"Amount\":2},{   \"ShipId\": 29,   \"Amount\":4}]', '[{   \"TypeId\": 46,   \"FactionId\": 3,   \"Position\": [19500,11600] }]', '[{   \"TargetSpaceMapId\": 16,   \"Position\": [10300, 6600],   \"TargetPosition\": [21900,14500],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true },  {   \"TargetSpaceMapId\": 13,   \"Position\": [2000,11200],   \"TargetPosition\": [18900,11300],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true },  {   \"TargetSpaceMapId\": 14,   \"Position\": [2000,1900],   \"TargetPosition\": [18700,10900],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true }]', '[{   \"TypeId\": 2,   \"Amount\": 100,   \"TopLeft\": [18300,1100], \"BottomRight\": [18300,1100], \"Respawnable\":true }]', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(16, '4-4', '', '', '[{   \"TargetSpaceMapId\": 13,   \"Position\": [19200,13400],   \"TargetPosition\": [10000,6200],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true },  {   \"TargetSpaceMapId\": 14,   \"Position\": [21900,11900],   \"TargetPosition\": [10400,6300],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true },  {   \"TargetSpaceMapId\": 15,   \"Position\": [21900,14500],   \"TargetPosition\": [10300,6600],   \"GraphicId\": 1,   \"FactionId\": 1,   \"Visible\": true,   \"Working\": true }]', '', '{\"StarterMap\":false,\"PvpMap\":true,\"RangeDisabled\":true,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(17, '1-5', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(18, '1-6', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(19, '1-7', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(20, '1-8', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(21, '2-5', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(22, '2-6', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(23, '2-7', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(24, '2-8', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(25, '3-5', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(26, '3-6', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(27, '3-7', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(28, '3-8', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(42, '???', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":true,\"CloakBlocked\":true,\"LogoutBlocked\":true,\"DeathLocationRepair\":false}', 0),
(71, 'GG Zeta', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(74, 'GG Kappa', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(101, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":true,\"CloakBlocked\":true,\"LogoutBlocked\":true,\"DeathLocationRepair\":false}', 0),
(102, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(103, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(104, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(105, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(106, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(107, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(108, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(109, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(110, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(111, 'JP', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(121, 'TA', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":true,\"CloakBlocked\":true,\"LogoutBlocked\":true,\"DeathLocationRepair\":false}', 0),
(200, 'LoW', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0),
(224, 'Custom Tournament', '', '', '', '', '{\"StarterMap\":false,\"PvpMap\":false,\"RangeDisabled\":false,\"CloakBlocked\":false,\"LogoutBlocked\":false,\"DeathLocationRepair\":true}', 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `server_ships`
--

CREATE TABLE `server_ships` (
  `id` int(11) NOT NULL,
  `shipID` int(11) NOT NULL,
  `baseShipId` int(11) NOT NULL,
  `lootID` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `health` int(11) NOT NULL DEFAULT 0,
  `shield` int(11) NOT NULL DEFAULT 0,
  `speed` int(11) NOT NULL DEFAULT 300,
  `lasers` int(11) NOT NULL DEFAULT 1,
  `generators` int(11) NOT NULL DEFAULT 1,
  `cargo` int(11) NOT NULL DEFAULT 100,
  `aggressive` tinyint(1) NOT NULL DEFAULT 0,
  `damage` int(11) NOT NULL DEFAULT 20,
  `respawnable` tinyint(1) NOT NULL,
  `reward` varchar(2048) COLLATE utf8_bin NOT NULL DEFAULT '{"Experience":0,"Honor":0,"Credits":0,"Uridium":0}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Tablo döküm verisi `server_ships`
--

INSERT INTO `server_ships` (`id`, `shipID`, `baseShipId`, `lootID`, `name`, `health`, `shield`, `speed`, `lasers`, `generators`, `cargo`, `aggressive`, `damage`, `respawnable`, `reward`) VALUES
(18, 1, 0, 'ship_phoenix', 'Phoenix', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(19, 2, 0, 'ship_yamato', 'Yamato', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(20, 3, 0, 'ship_leonov', 'Leonov', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(21, 4, 0, 'ship_defcom', 'Defcom', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(22, 5, 0, 'ship_liberator', 'Liberator', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(23, 6, 0, 'ship_piranha', 'Piranha', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(24, 7, 0, 'ship_nostromo', 'Nostromo', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(25, 8, 8, 'ship_vengeance', 'Vengeance', 180000, 0, 380, 10, 10, 0, 0, 0, 0, '{\"Experience\":25600,\"Honor\":256,\"Credits\":0,\"Uridium\":256}'),
(26, 9, 0, 'ship_bigboy', 'Bigboy', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(27, 10, 10, 'ship_goliath', 'Goliath', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(28, 12, 0, 'pet', 'P.E.T. Level 1-3', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":25000,\"Honor\":150,\"Credits\":0,\"Uridium\":0}'),
(29, 13, 0, 'pet', 'P.E.T. Red', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":25000,\"Honor\":150,\"Credits\":0,\"Uridium\":0}'),
(30, 15, 0, 'pet', 'P.E.T. Frozen', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":25000,\"Honor\":150,\"Credits\":0,\"Uridium\":0}'),
(31, 16, 8, 'ship_vengeance_design_adept', 'Adept', 180000, 0, 380, 10, 10, 0, 0, 0, 0, '{\"Experience\":25600,\"Honor\":256,\"Credits\":0,\"Uridium\":256}'),
(32, 17, 8, 'ship_vengeance_design_corsair', 'Corsair', 180000, 0, 380, 10, 10, 0, 0, 0, 0, '{\"Experience\":25600,\"Honor\":256,\"Credits\":0,\"Uridium\":256}'),
(33, 18, 8, 'ship_vengeance_design_lightning', 'Lightning', 180000, 0, 380, 10, 10, 0, 0, 0, 0, '{\"Experience\":25600,\"Honor\":256,\"Credits\":0,\"Uridium\":256}'),
(34, 19, 10, 'ship_goliath_design_jade', 'Jade', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(35, 20, 0, 'ship_admin', 'Ufo', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(36, 22, 0, 'pet', 'P.E.T. Normal', 0, 50000, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":25000,\"Honor\":150,\"Credits\":0,\"Uridium\":0}'),
(37, 49, 49, 'ship_aegis', 'Aegis', 275000, 0, 300, 10, 15, 0, 0, 0, 0, '{\"Experience\":25000,\"Honor\":250,\"Credits\":0,\"Uridium\":250}'),
(38, 52, 10, 'ship_goliath_design_amber', 'Amber', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(39, 53, 10, 'ship_goliath_design_crimson', 'Crimson', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(40, 54, 10, 'ship_goliath_design_sapphire', 'Sapphire', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(41, 56, 10, 'ship_goliath_design_enforcer', 'Enforcer', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(42, 57, 10, 'ship_goliath_design_independence', 'Independence', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(43, 58, 8, 'ship_vengeance_design_revenge', 'Revenge', 180000, 0, 380, 10, 10, 0, 0, 0, 0, '{\"Experience\":25600,\"Honor\":256,\"Credits\":0,\"Uridium\":256}'),
(44, 59, 10, 'ship_goliath_design_bastion', 'Bastion', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(45, 60, 8, 'ship_vengeance_design_avenger', 'Avenger', 180000, 0, 380, 10, 10, 0, 0, 0, 0, '{\"Experience\":25600,\"Honor\":256,\"Credits\":0,\"Uridium\":256}'),
(46, 14, 0, 'pet', 'P.E.T. Green', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":25000,\"Honor\":150,\"Credits\":0,\"Uridium\":0}'),
(47, 62, 10, 'ship_goliath_design_exalted', 'Exalted', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(48, 63, 10, 'ship_goliath_design_solace', 'Solace', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(49, 64, 10, 'ship_goliath_design_diminisher', 'Diminisher', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(50, 65, 10, 'ship_goliath_design_spectrum', 'Spectrum', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(51, 66, 10, 'ship_goliath_design_sentinel', 'Sentinel', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(52, 67, 10, 'ship_goliath_design_venom', 'Venom', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(53, 68, 10, 'ship_goliath_design_ignite', 'Ignite', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(54, 69, 69, 'ship_citadel', 'Citadel', 550000, 0, 240, 7, 20, 0, 0, 0, 0, '{\"Experience\":120000,\"Honor\":1200,\"Credits\":0,\"Uridium\":1200}'),
(55, 70, 70, 'ship_spearhead', 'Spearhead', 100000, 0, 370, 5, 12, 0, 0, 0, 0, '{\"Experience\":7500,\"Honor\":75,\"Credits\":0,\"Uridium\":75}'),
(56, 81, 81, 'ship_vengeance_design_pusat', 'Pusat', 125000, 0, 370, 16, 12, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(57, 86, 10, 'ship_goliath_design_kick', 'Kick', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(58, 87, 10, 'ship_goliath_design_referee', 'Referee', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(59, 88, 10, 'ship_goliath_design_goal', 'Goal', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(60, 98, 98, 'ship_police', 'PoliceShip', 1000000, 0, 300, 35, 35, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(61, 109, 10, 'ship_goliath_design_saturn', 'Saturn', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(62, 110, 10, 'ship_goliath_design_centaur', 'Centaur', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(63, 61, 10, 'ship_goliath_design_veteran', 'Veteran', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(64, 140, 10, 'ship_goliath_design_vanquisher', 'Vanquisher', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(65, 141, 10, 'ship_goliath_design_sovereign', 'Sovereign', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(66, 142, 10, 'ship_goliath_design_peacemaker', 'Peacemaker', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(67, 150, 0, 'ship_nostromo_design_diplomat', 'Nostromo Diplomat', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(68, 151, 0, 'ship_nostromo_design_envoy', 'Nostromo Envoy', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(69, 152, 0, 'ship_nostromo_design_ambassador', 'Nostromo Ambassador', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(70, 153, 10, 'ship_goliath_design_goliath-razer', 'Razer', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(71, 154, 0, 'ship_nostromo_design_nostromo-razer', 'Nostromo Razer', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(72, 155, 10, 'ship_goliath_design_turkish', 'Hezarfen', 256000, 0, 300, 15, 15, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(73, 156, 156, 'ship_g-surgeon', 'Surgeon', 256000, 0, 300, 15, 16, 0, 0, 0, 0, '{\"Experience\":51200,\"Honor\":512,\"Credits\":0,\"Uridium\":512}'),
(74, 157, 49, 'ship_aegis_design_aegis-elite', 'Aegis Veteran', 275000, 0, 300, 10, 15, 0, 0, 0, 0, '{\"Experience\":25000,\"Honor\":250,\"Credits\":0,\"Uridium\":250}'),
(75, 158, 49, 'ship_aegis_design_aegis-superelite', 'Aegis Super Elite', 275000, 0, 300, 10, 15, 0, 0, 0, 0, '{\"Experience\":25000,\"Honor\":250,\"Credits\":0,\"Uridium\":250}'),
(76, 159, 69, 'ship_citadel_design_citadel-elite', 'Citadel Veteran', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(77, 160, 69, 'ship_citadel_design_citadel-superelite', 'Citadel Super Elite', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(78, 161, 70, 'ship_aegis_design_aegis-elite', 'Spearhead Veteran', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(79, 162, 70, 'ship_aegis_design_aegis-superelite', 'Spearhead Super Elite', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(80, 442, 0, 'spaceball_summer', '..::{Spaceball}::..', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(81, 443, 0, 'spaceball_winter', '..::{Spaceball}::..', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(82, 444, 0, 'spaceball_soccer', '..::{Spaceball}::..', 0, 0, 0, 0, 0, 0, 0, 0, 0, '{\"Experience\":0,\"Honor\":0,\"Credits\":0,\"Uridium\":0}'),
(83, 79, 0, 'ship79', '-=[ Kristallon ]=-', 400000, 300000, 250, 1, 1, 100, 0, 4500, 1, '{\"Experience\":51200,\"Honor\":256,\"Credits\":409600,\"Uridium\":128}'),
(84, 78, 0, 'ship78', '-=[ Kristallin ]=-', 50000, 40000, 320, 1, 1, 100, 1, 1100, 1, '{\"Experience\":6400,\"Honor\":32,\"Credits\":12800,\"Uridium\":16}'),
(85, 35, 0, 'ship35', '..::{ Boss Kristallon }::..', 1600000, 1200000, 250, 1, 1, 100, 0, 18000, 1, '{\"Experience\":204800,\"Honor\":1024,\"Credits\":1638400,\"Uridium\":512}'),
(86, 29, 0, 'ship29', '..::{ Boss Kristallin }::..', 200000, 160000, 320, 1, 1, 100, 1, 4000, 1, '{\"Experience\":25600,\"Honor\":128,\"Credits\":51200,\"Uridium\":64}');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `chat_permissions`
--
ALTER TABLE `chat_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `log_event_jpb`
--
ALTER TABLE `log_event_jpb`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `log_player_kills`
--
ALTER TABLE `log_player_kills`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `player_accounts`
--
ALTER TABLE `player_accounts`
  ADD PRIMARY KEY (`userId`);

--
-- Tablo için indeksler `player_equipment`
--
ALTER TABLE `player_equipment`
  ADD PRIMARY KEY (`userId`);

--
-- Tablo için indeksler `player_galaxygates`
--
ALTER TABLE `player_galaxygates`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `player_settings`
--
ALTER TABLE `player_settings`
  ADD PRIMARY KEY (`userId`);

--
-- Tablo için indeksler `player_titles`
--
ALTER TABLE `player_titles`
  ADD PRIMARY KEY (`userID`);

--
-- Tablo için indeksler `server_bans`
--
ALTER TABLE `server_bans`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `server_battlestations`
--
ALTER TABLE `server_battlestations`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `server_clans`
--
ALTER TABLE `server_clans`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `server_clan_applications`
--
ALTER TABLE `server_clan_applications`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `server_clan_diplomacy`
--
ALTER TABLE `server_clan_diplomacy`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `server_clan_diplomacy_applications`
--
ALTER TABLE `server_clan_diplomacy_applications`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `server_maps`
--
ALTER TABLE `server_maps`
  ADD PRIMARY KEY (`mapID`);

--
-- Tablo için indeksler `server_ships`
--
ALTER TABLE `server_ships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipID` (`shipID`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `chat_permissions`
--
ALTER TABLE `chat_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `log_event_jpb`
--
ALTER TABLE `log_event_jpb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `log_player_kills`
--
ALTER TABLE `log_player_kills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `player_accounts`
--
ALTER TABLE `player_accounts`
  MODIFY `userId` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `player_equipment`
--
ALTER TABLE `player_equipment`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `player_galaxygates`
--
ALTER TABLE `player_galaxygates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `server_bans`
--
ALTER TABLE `server_bans`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `server_battlestations`
--
ALTER TABLE `server_battlestations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `server_clans`
--
ALTER TABLE `server_clans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `server_clan_applications`
--
ALTER TABLE `server_clan_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `server_clan_diplomacy`
--
ALTER TABLE `server_clan_diplomacy`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `server_clan_diplomacy_applications`
--
ALTER TABLE `server_clan_diplomacy_applications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `server_maps`
--
ALTER TABLE `server_maps`
  MODIFY `mapID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- Tablo için AUTO_INCREMENT değeri `server_ships`
--
ALTER TABLE `server_ships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
