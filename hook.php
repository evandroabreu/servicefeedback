<?php
/**
 * Plugin ServiceFeedback
 * Hook file
 */

/**
 * Plugin install process
 */
function plugin_servicefeedback_install()
{
    global $DB;

    // Criar tabela de feedbacks
    $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_servicefeedback_feedbacks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `tickets_id` int(11) NOT NULL,
      `users_id` int(11) NOT NULL,
      `token` varchar(255) NOT NULL,
      `rating` int(1) DEFAULT NULL,
      `status` enum('pending','completed') DEFAULT 'pending',
      `date_creation` datetime DEFAULT NULL,
      `date_completion` datetime DEFAULT NULL,
      `entities_id` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      UNIQUE KEY `token` (`token`),
      KEY `tickets_id` (`tickets_id`),
      KEY `users_id` (`users_id`),
      KEY `entities_id` (`entities_id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

    $DB->queryOrDie($query, $DB->error());

    // Criar tabela de configurações
    $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_servicefeedback_configs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `value` text,
      PRIMARY KEY (`id`),
      UNIQUE KEY `name` (`name`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

    $DB->queryOrDie($query, $DB->error());

    // Inserir configurações padrão
    $default_configs = [
       'email_subject' => '[Chamado {ticket_id}] Avalie o atendimento do chamado {ticket_title}',
       'email_body' => 'Olá {requester_firstname} {requester_lastname},<br><br>

Seu chamado #{ticket_id} foi finalizado com sucesso.<br><br>

Para nos ajudar a melhorar nossos serviços, por favor avalie nosso atendimento clicando no botão abaixo.

{rating_stars}

Obrigado pela sua colaboração!

Equipe de Suporte',
       'enable_feedback' => '1'
    ];

    foreach ($default_configs as $name => $value) {
        $query = "INSERT IGNORE INTO `glpi_plugin_servicefeedback_configs` 
                (`name`, `value`) VALUES ('$name', '$value')";
        $DB->queryOrDie($query, $DB->error());
    }

    // Criar diretório de logs
    $log_dir = GLPI_LOG_DIR . '/servicefeedback';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    return true;
}

/**
 * Plugin uninstall process
 */
function plugin_servicefeedback_uninstall()
{
    global $DB;

    $tables = [
       'glpi_plugin_servicefeedback_feedbacks',
       'glpi_plugin_servicefeedback_configs'
    ];

    foreach ($tables as $table) {
        $query = "DROP TABLE IF EXISTS `$table`";
        $DB->queryOrDie($query, $DB->error());
    }

    return true;
}
