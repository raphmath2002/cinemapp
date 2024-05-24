CREATE TABLE IF NOT EXISTS auth_failures (
    `id` bigint(20) UNSIGNED AUTO_INCREMENT NOT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `client_ip` varchar(255) NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    INDEX `auth_failures_user_id_foreign` (`user_id`)
) ENGINE=InnoDB;