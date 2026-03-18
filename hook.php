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
        'email_subject' => 'Pesquisa de satisfação do chamado nº {ticket_id}',
        'email_body' => '<p>Prezado(a) Sr.(a) {requester_firstname} {requester_lastname},</p>

<p>Informamos que o seu chamado foi concluído. Seguem os detalhes para sua conferência:</p>

<p>
<strong>Número do chamado:</strong> {ticket_id}<br>
<strong>Título:</strong> {ticket_title}<br>
<strong>Data de fechamento:</strong> {close_date}
</p>

<p>Para nós, a sua opinião é muito importante. Convidamos você a avaliar o atendimento prestado por meio da pesquisa de satisfação disponível no botão abaixo:</p>

{rating_stars}

<p>Seu feedback contribui diretamente para a melhoria contínua dos nossos serviços.</p>
',
        'enable_feedback' => '1'
    ];

    foreach ($default_configs as $name => $value) {
        $query = "INSERT IGNORE INTO `glpi_plugin_servicefeedback_configs`
                (`name`, `value`) VALUES ('$name', '$value')";
        $DB->queryOrDie($query, $DB->error());
    }

    // Atualizar templates existentes com os novos valores
    foreach (['email_subject', 'email_body'] as $name) {
        $value = $DB->escape($default_configs[$name]);
        $DB->query("UPDATE `glpi_plugin_servicefeedback_configs` SET `value` = '$value' WHERE `name` = '$name'");
    }

    // Adicionar coluna comment caso a tabela já exista sem ela (migration)
    if ($DB->tableExists('glpi_plugin_servicefeedback_feedbacks')) {
        if (!$DB->fieldExists('glpi_plugin_servicefeedback_feedbacks', 'comment')) {
            $DB->queryOrDie(
                "ALTER TABLE `glpi_plugin_servicefeedback_feedbacks`
                 ADD COLUMN `comment` text DEFAULT NULL AFTER `rating`",
                $DB->error()
            );
        }
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
