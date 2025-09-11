<?php
/**
 * Plugin ServiceFeedback
 * Menu class
 */

class PluginServicefeedbackMenu extends CommonGLPI
{

    public static $rightname = 'plugin_servicefeedback';

    public static function getMenuName()
    {
        return __('Service Feedback', 'servicefeedback');
    }

    public static function getMenuContent()
    {
        $menu = [];
      
        $menu['title'] = self::getMenuName();
        $menu['page'] = '/plugins/servicefeedback/front/report.php';
        $menu['icon'] = 'fas fa-star';
      
        $menu['options']['report'] = [
           'title' => __('Reports', 'servicefeedback'),
           'page' => '/plugins/servicefeedback/front/report.php',
           'icon' => 'fas fa-chart-bar'
        ];
      
        $menu['options']['config'] = [
           'title' => __('Configuration', 'servicefeedback'),
           'page' => '/plugins/servicefeedback/front/config.php',
           'icon' => 'fas fa-cog'
        ];

        return $menu;
    }
}
