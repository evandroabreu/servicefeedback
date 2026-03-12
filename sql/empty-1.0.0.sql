-- Plugin ServiceFeedback for GLPI
-- Database structure (matches hook.php install function)

CREATE TABLE IF NOT EXISTS `glpi_plugin_servicefeedback_feedbacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tickets_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `rating` int(1) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `date_creation` datetime DEFAULT NULL,
  `date_completion` datetime DEFAULT NULL,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `tickets_id` (`tickets_id`),
  KEY `users_id` (`users_id`),
  KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_servicefeedback_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Default configuration
INSERT IGNORE INTO `glpi_plugin_servicefeedback_configs` (`name`, `value`) VALUES
('email_subject', '[Chamado {ticket_id}] Avalie o atendimento do chamado {ticket_title}'),
('email_body', 'Olá {requester_firstname} {requester_lastname},<br><br>Seu chamado #{ticket_id} foi finalizado com sucesso.<br><br>Para nos ajudar a melhorar nossos serviços, por favor avalie nosso atendimento clicando no botão abaixo.<br><br>{rating_stars}<br><br>Obrigado pela sua colaboração!<br><br>Equipe COSUT'),
('enable_feedback', '1');
