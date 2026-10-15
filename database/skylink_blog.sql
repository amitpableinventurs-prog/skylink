-- Skylink Media database schema (blog, admin logins, leads, newsletter subscribers).
-- Import this in phpMyAdmin (select your database -> Import -> choose this file -> Go).
-- Then open /admin/setup.php once to create the first admin login.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `excerpt` TEXT NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `cover_image` VARCHAR(255) NOT NULL DEFAULT '',
  `category` VARCHAR(80) NOT NULL DEFAULT '',
  `author` VARCHAR(120) NOT NULL DEFAULT '',
  `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `meta_title` VARCHAR(200) NOT NULL DEFAULT '',
  `meta_description` VARCHAR(320) NOT NULL DEFAULT '',
  `published_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_slug` (`slug`),
  KEY `posts_status_published` (`status`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(60) NOT NULL,
  `name` VARCHAR(120) NOT NULL DEFAULT '',
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `attempted_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `login_attempts_ip` (`ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Leads: contact-form enquiries (managed in /admin -> Leads).
CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `phone` VARCHAR(30) NOT NULL DEFAULT '',
  `service` VARCHAR(80) NOT NULL DEFAULT '',
  `message` TEXT NOT NULL,
  `source_page` VARCHAR(255) NOT NULL DEFAULT '',
  `status` ENUM('new','contacted','converted','closed') NOT NULL DEFAULT 'new',
  `notes` TEXT NOT NULL,
  `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `leads_status_created` (`status`, `created_at`),
  KEY `leads_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter sign-ups from the footer form (admin -> Subscribers).
CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(160) NOT NULL,
  `source_page` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscribers_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
