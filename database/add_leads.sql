-- Only for a server where skylink_blog.sql was ALREADY imported before the Leads feature existed.
-- Import this once in phpMyAdmin to add the leads + subscribers tables. (Fresh installs: just import skylink_blog.sql.)
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
