SET time_zone = "+01:00";

SET default_storage_engine = InnoDB;

DROP DATABASE IF EXISTS `ocp6`;

CREATE DATABASE IF NOT EXISTS `ocp6` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `ocp6`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255),
  `email` VARCHAR(255),
  `accountVerificationToken` CHAR(21) DEFAULT NULL,
  `accountVerified` TINYINT (1) DEFAULT 0,
  `password` VARCHAR(255),
  `passwordResetToken` CHAR(21) DEFAULT NULL,
  `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE (`email`),
  KEY (`email`, `password`),
  KEY (`id`, `username`)
);

CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50),
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `media` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `trickId` INT NOT NULL,
  `url` VARCHAR(2048),
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `tricks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255),
  `description` TEXT,
  `category` INT,
  `author` INT,
  `mainMedia` INT,
  `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_tricks_author` FOREIGN KEY (`author`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_tricks_category` FOREIGN KEY (`category`) REFERENCES `categories` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_tricks_mainMedia` FOREIGN KEY (`mainMedia`) REFERENCES `media` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  KEY (`name`)
);

ALTER TABLE `media` ADD CONSTRAINT `fk_media_trickId` FOREIGN KEY (`trickId`) REFERENCES `tricks` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `media_tricks` (
  `trickId` INT NOT NULL,
  `mediaId` INT NOT NULL,
  PRIMARY KEY (`trickId`, `mediaId`),
  CONSTRAINT `fk_media_tricks_trickId` FOREIGN KEY (`trickId`) REFERENCES `tricks` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_media_tricks_mediaId` FOREIGN KEY (`mediaId`) REFERENCES `media` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `trickId` INT NOT NULL,
  `body` TEXT,
  `author` INT,
  `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `deleteAt` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_comments_trickId` FOREIGN KEY (`trickId`) REFERENCES `tricks` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_comments_author` FOREIGN KEY (`author`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  KEY (`createdAt`)
);
