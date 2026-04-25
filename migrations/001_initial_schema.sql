-- Migration: 001_initial_schema
-- Created: 2024-01-01
-- Description: Initial database schema for mathex.cz

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── Users (admin accounts) ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(100)     NOT NULL,
    `password`   VARCHAR(255)     NOT NULL COMMENT 'bcrypt hash',
    `email`      VARCHAR(255)     NOT NULL,
    `role`       ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Projects (portfolio) ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `projects` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`        VARCHAR(255)     NOT NULL,
    `slug`         VARCHAR(255)     NOT NULL,
    `description`  TEXT             DEFAULT NULL COMMENT 'Short teaser shown in cards',
    `content`      LONGTEXT         DEFAULT NULL COMMENT 'Full detail HTML content',
    `technologies` VARCHAR(500)     DEFAULT NULL COMMENT 'Comma-separated list',
    `url`          VARCHAR(255)     DEFAULT NULL,
    `github_url`   VARCHAR(255)     DEFAULT NULL,
    `image`        VARCHAR(255)     DEFAULT NULL COMMENT 'Relative path or external URL',
    `featured`     TINYINT(1)       NOT NULL DEFAULT 0,
    `position`     SMALLINT         NOT NULL DEFAULT 0,
    `published`    TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug`       (`slug`),
    KEY `idx_published`        (`published`),
    KEY `idx_position`         (`position`),
    KEY `idx_featured`         (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Contact messages ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `messages` (
    `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(255)     NOT NULL,
    `email`      VARCHAR(255)     NOT NULL,
    `subject`    VARCHAR(500)     DEFAULT NULL,
    `message`    TEXT             NOT NULL,
    `ip_address` VARCHAR(45)      DEFAULT NULL,
    `is_read`    TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_is_read` (`is_read`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
