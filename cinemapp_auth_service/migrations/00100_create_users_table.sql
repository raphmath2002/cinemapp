CREATE TABLE IF NOT EXISTS users (
    `id` bigint(20) UNSIGNED AUTO_INCREMENT NOT NULL,
    `uuid` varchar(255) NOT NULL,
    `first_name` varchar(255) NOT NULL,
    `last_name` varchar(255) NOT NULL,
    `email` text NOT NULL,
    `password` varchar(255) NOT NULL,
    `roles` varchar(255),
    `status` BOOLEAN NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_uuid_unique` (`uuid`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB;