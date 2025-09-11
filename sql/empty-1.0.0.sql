-- Plugin ServiceFeedback for GLPI
-- Initial database structure

CREATE TABLE IF NOT EXISTS `glpi_plugin_servicefeedback_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tickets_id` int unsigned NOT NULL DEFAULT '0',
  `users_id` int unsigned NOT NULL DEFAULT '0',
  `token` varchar(255) NOT NULL,
  `expiration_date` timestamp NULL DEFAULT NULL,
  `is_used` tinyint NOT NULL DEFAULT '0',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_mod` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `tickets_id` (`tickets_id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_servicefeedback_feedbacks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tickets_id` int unsigned NOT NULL DEFAULT '0',
  `users_id` int unsigned NOT NULL DEFAULT '0',
  `tokens_id` int unsigned NOT NULL DEFAULT '0',
  `rating` int NOT NULL DEFAULT '0',
  `nps` int NOT NULL DEFAULT '0',
  `comment_good` text,
  `comment_bad` text,
  `custom_fields` text,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_mod` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tickets_id` (`tickets_id`),
  KEY `users_id` (`users_id`),
  KEY `tokens_id` (`tokens_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_servicefeedback_configs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entities_id` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint NOT NULL DEFAULT '1',
  `expiration_days` int NOT NULL DEFAULT '30',
  `send_notification` tinyint NOT NULL DEFAULT '1',
  `notify_requester` tinyint NOT NULL DEFAULT '1',
  `notify_observers` tinyint NOT NULL DEFAULT '0',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_mod` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default configuration for root entity
INSERT INTO `glpi_plugin_servicefeedback_configs` 
(`entities_id`, `is_active`, `expiration_days`, `send_notification`, `notify_requester`, `notify_observers`) 
VALUES (0, 1, 30, 1, 1, 0);