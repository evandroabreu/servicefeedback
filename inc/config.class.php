<?php
/**
 * Plugin ServiceFeedback
 * Config class
 */

class PluginServicefeedbackConfig extends CommonDBTM
{

    public static $rightname = 'plugin_servicefeedback';

    public static function getTypeName($nb = 0)
    {
        return __('Configuration', 'servicefeedback');
    }

    /**
     * Exibe formulário de configuração
     */
    public static function showConfigForm()
    {
        global $CFG_GLPI;

        $config = PluginServicefeedbackFeedback::getConfig();

        echo "<form method='post' action='" . $CFG_GLPI['root_doc'] . "/plugins/servicefeedback/front/config.php'>";
        echo "<div class='spaced' id='tabsbody'>";
        echo "<table class='tab_cadre_fixe'>";
      
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2'>" . __('Email Configuration', 'servicefeedback') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='30%'>" . __('Enable Feedback', 'servicefeedback') . "</td>";
        echo "<td>";
        Dropdown::showYesNo('enable_feedback', $config['enable_feedback'] ?? 1);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Email Subject', 'servicefeedback') . "</td>";
        echo "<td>";
        echo "<input type='text' name='email_subject' value='" . Html::cleanInputText($config['email_subject'] ?? '') . "' size='80'>";
        echo "<br><small>" . __('Available variables: {ticket_id}, {requester_name}', 'servicefeedback') . "</small>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Email Body', 'servicefeedback') . "</td>";
        echo "<td>";
        echo "<textarea name='email_body' rows='10' cols='80'>" . Html::cleanInputText($config['email_body'] ?? '') . "</textarea>";
        echo "<br><small>" . __('Available variables: {ticket_id}, {requester_name}, {rating_stars}', 'servicefeedback') . "</small>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2' class='center'>";
        echo "<input type='submit' name='update_config' value='" . __('Save') . "' class='submit'>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        echo "</div>";
        Html::closeForm();
    }

    /**
     * Atualiza configuração
     */
    public static function updateConfig($input)
    {
        global $DB;

        $configs = [
           'enable_feedback' => $input['enable_feedback'] ?? 1,
           'email_subject' => $input['email_subject'] ?? '',
           'email_body' => $input['email_body'] ?? ''
        ];

        foreach ($configs as $name => $value) {
            $query = "INSERT INTO glpi_plugin_servicefeedback_configs (name, value) 
                   VALUES ('" . $DB->escape($name) . "', '" . $DB->escape($value) . "')
                   ON DUPLICATE KEY UPDATE value = '" . $DB->escape($value) . "'";
            $DB->queryOrDie($query, $DB->error());
        }

        PluginServicefeedbackFeedback::writeLog("Configuração atualizada");
        return true;
    }
}
