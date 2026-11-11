CREATE DATABASE IF NOT EXISTS cpk_messaging
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE cpk_messaging;

DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id`            VARCHAR(32)  NOT NULL,
    `name`          VARCHAR(100) NOT NULL,
    `phone`         VARCHAR(20)  NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `photo`         VARCHAR(255) NULL,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_phone` (`phone`)
) ENGINE=InnoDB;

CREATE TABLE `contacts` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id`        VARCHAR(32)  NOT NULL,
    `contact_user_id` VARCHAR(32)  NOT NULL,
    `alias_name`      VARCHAR(100) NOT NULL,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_owner_contact` (`owner_id`, `contact_user_id`),
    CONSTRAINT `fk_contacts_owner`
        FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_contacts_user`
        FOREIGN KEY (`contact_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `messages` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sender_id`   VARCHAR(32)  NOT NULL,
    `receiver_id` VARCHAR(32)  NOT NULL,
    `body`        TEXT         NULL,
    `image`       VARCHAR(255) NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_conversation` (`sender_id`, `receiver_id`, `created_at`),
    CONSTRAINT `fk_messages_sender`
        FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_messages_receiver`
        FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;