-- ====================================================================
-- NagaraLive Database Schema & Realistic Sri Lankan Neighborhood Seeds
-- District Focus: Gampaha District (Gampaha, Yakkala, Miriswatta, Ganemulla, Ja-Ela)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `nagaralive` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nagaralive`;

-- Drop table if exists to allow clean re-import
DROP TABLE IF EXISTS `posts`;

CREATE TABLE `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `area` VARCHAR(100) NOT NULL,
  `category` ENUM('EVENT', 'NOTICE', 'WEATHER_EMERGENCY', 'POWER_WATER') NOT NULL,
  `target_group` ENUM('ALL', 'ELDERLY', 'CHILDREN', 'YOUTH') DEFAULT 'ALL',
  `raw_message` TEXT NOT NULL,
  `translated_message` TEXT,
  `is_eyewitness` TINYINT(1) DEFAULT 1,
  `confirmations` INT DEFAULT 1,
  `status` ENUM('ACTIVE', 'RESOLVED') DEFAULT 'ACTIVE',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category`),
  INDEX `idx_area` (`area`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- Seed Data: 6 Realistic Sri Lankan Neighborhood Reports
-- --------------------------------------------------------------------

INSERT INTO `posts` (`area`, `category`, `target_group`, `raw_message`, `translated_message`, `is_eyewitness`, `confirmations`, `status`, `created_at`) VALUES
(
  'Yakkala',
  'POWER_WATER',
  'ALL',
  'Yakkala junction eke main transformer eka pipuruwa, Radawana road ekama current na. CEB breakdown team eka enakal balan innawa.',
  'Major transformer exploded near Yakkala junction; entire Radawana road electricity is down. Residents are waiting for the CEB emergency breakdown crew.',
  1,
  4,
  'ACTIVE',
  DATE_SUB(NOW(), INTERVAL 25 MINUTE)
),
(
  'Miriswatta',
  'WEATHER_EMERGENCY',
  'ALL',
  'මිරිස්වත්ත නුවර පාරේ මහ ගසක් කඩා වැටී වාහන ගමනාගමනය සම්පූර්ණයෙන්ම ඇනහිට ඇත. අධික වර්ෂාව නිසා ජල ගැලීම් අවදානමක් පවතී.',
  'A large tree has collapsed across the Colombo-Kandy road at Miriswatta; vehicular traffic is completely halted. Severe downpour is creating localized flash flood risks.',
  1,
  7,
  'ACTIVE',
  DATE_SUB(NOW(), INTERVAL 45 MINUTE)
),
(
  'Gampaha Town',
  'NOTICE',
  'ALL',
  'MOH Gampaha announces urgent Dengue prevention fogging campaign across Ward 4, 5, and Bandaranayake Mawatha today from 4:00 PM to 6:30 PM. Please keep doors open and cover water storage tanks.',
  'MOH Gampaha announces an urgent Dengue prevention fogging campaign across Ward 4, 5, and Bandaranayake Mawatha today from 4:00 PM to 6:30 PM. Please keep doors open and cover water storage containers.',
  0,
  5,
  'ACTIVE',
  DATE_SUB(NOW(), INTERVAL 2 HOUR)
),
(
  'Ganemulla',
  'EVENT',
  'ELDERLY',
  'ගණේමුල්ල ප්‍රජා ශාලාවේදී ජ්‍යෙෂ්ඨ පුරවැසියන් සඳහා නොමිලේ ඇස් පරීක්ෂාව සහ ඇස් කණ්ණාඩි බෙදාදීමේ විශේෂ සායනය ලබන ඉරිදා පෙ.ව. 8.30 සිට පැවැත්වේ.',
  'Free comprehensive eye screening clinic and reading glasses distribution for senior citizens at Ganemulla Community Hall this Sunday starting at 8:30 AM.',
  1,
  3,
  'ACTIVE',
  DATE_SUB(NOW(), INTERVAL 4 HOUR)
),
(
  'Ja-Ela',
  'EVENT',
  'CHILDREN',
  'Ja-Ela Town Hall eke podi lamai wenuwen Art & Drawing workshop ekak saha Puppet show ekak thiyanawa Saturday hawasa 3:00 ta. Free admission for all kids!',
  'Art & Drawing workshop and interactive Puppet Show for kids taking place at Ja-Ela Town Hall this Saturday at 3:00 PM. Free admission for all neighborhood children.',
  1,
  2,
  'ACTIVE',
  DATE_SUB(NOW(), INTERVAL 6 HOUR)
),
(
  'Gampaha Town',
  'EVENT',
  'YOUTH',
  'Youth 3x3 Street Basketball Tournament and Open Coding Meetup at Gampaha Urban Council Ground. Registrations open for ages 16-28. Food stalls & live acoustic session tonight.',
  'Youth 3x3 Street Basketball Tournament and Open Coding Meetup at Gampaha Urban Council Grounds. Open registration for ages 16-28, featuring live acoustic sessions and local food stalls.',
  1,
  6,
  'ACTIVE',
  DATE_SUB(NOW(), INTERVAL 8 HOUR)
);
