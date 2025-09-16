<?php
/**
 * Plugin ServiceFeedback
 * Profile rights definition
 */

class PluginServicefeedbackProfile extends Profile
{

    public static $rightname = 'plugin_servicefeedback';

    /**
     * Nome da aba no formulário de Perfil
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item instanceof Profile) {
            return __('Service Feedback', 'servicefeedback');
        }
        return '';
    }

    /**
     * Conteúdo da aba: exibe checkboxes de direitos
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof Profile) {
            self::showForProfile($item);
        }
        return true;
    }

    /**
     * Direitos do plugin
     */
    public function getRights($interface = 'central')
    {
        return [
           READ   => __('View feedback reports', 'servicefeedback'),
           UPDATE => __('Change feedback configuration', 'servicefeedback'),
           DELETE => __('Delete feedback entries', 'servicefeedback')
        ];
    }

    /**
     * Exibição dos direitos na aba
     */
    public static function showForProfile(Profile $profile)
    {
        $rights = [
           READ   => __('View feedback reports', 'servicefeedback'),
           UPDATE => __('Change feedback configuration', 'servicefeedback'),
           DELETE => __('Delete feedback entries', 'servicefeedback')
        ];

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='2'>" . __('Service Feedback rights', 'servicefeedback') . "</th></tr>";

        foreach ($rights as $right => $label) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>$label</td>";
            echo "<td class='center'>";
            // Aqui é o método atual do GLPI 10 para exibir a checkbox de direito
            ProfileRight::showForRight(
                $profile,              // o perfil atual
                self::$rightname,      // o "domínio" do plugin
                $right,                // READ / UPDATE / DELETE
                $profile->getID()      // id do perfil
            );
            echo "</td></tr>";
        }

        echo "</table>";
    }
}
