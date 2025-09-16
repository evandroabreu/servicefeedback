<?php
/**
 * Plugin ServiceFeedback
 * Setup file
 */

define('PLUGIN_SERVICEFEEDBACK_VERSION', '1.0.0');
define('PLUGIN_SERVICEFEEDBACK_MIN_GLPI', '10.0.0');

/**
 * Init the hooks of the plugins - Needed
 */
function plugin_init_servicefeedback()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['servicefeedback'] = true;
   
    // Hook para detectar mudança de status do chamado
    $PLUGIN_HOOKS['item_update']['servicefeedback'] = [
       'Ticket' => 'plugin_servicefeedback_ticket_update'
    ];

    // Menu principal
    $PLUGIN_HOOKS['menu_toadd']['servicefeedback'] = [
       'tools' => 'PluginServicefeedbackMenu'
    ];

    // Adicionar aba no chamado
    $PLUGIN_HOOKS['display_tab']['servicefeedback'] = [
       'Ticket' => 'plugin_servicefeedback_display_tab'
    ];

    // Conteúdo da aba
    $PLUGIN_HOOKS['display_tab_content']['servicefeedback'] = [
       'Ticket' => 'plugin_servicefeedback_display_tab_content'
    ];

    // CSS
    $PLUGIN_HOOKS['add_css']['servicefeedback'] = 'css/servicefeedback.css';

    // $PLUGIN_HOOKS['perms']['servicefeedback'] = [
    //     'plugin_servicefeedback' => [
    //         'rights' => [READ, UPDATE, DELETE],
    //         'short'  => __('Service Feedback', 'servicefeedback'),
    //         'long'   => [
    //                     READ   => __('View feedback reports', 'servicefeedback'),
    //                     UPDATE => __('Change feedback configuration', 'servicefeedback'),
    //                     DELETE => __('Delete feedback entries', 'servicefeedback')
    //         ]
    //     ]
    // ];

    Plugin::registerClass('PluginServicefeedbackProfile', ['addtabon' => 'Profile']);
}

/**
 * Get the name and the version of the plugin - Needed
 */
function plugin_version_servicefeedback()
{
    return [
       'name'           => 'Service Feedback',
       'version'        => PLUGIN_SERVICEFEEDBACK_VERSION,
       'author'         => 'Evandro Abreu',
       'license'        => 'GPLv3+',
       'homepage'       => '',
       'requirements'   => [
          'glpi' => [
             'min' => PLUGIN_SERVICEFEEDBACK_MIN_GLPI
          ]
       ]
    ];
}

/**
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 */
function plugin_servicefeedback_check_prerequisites()
{
    if (version_compare(GLPI_VERSION, PLUGIN_SERVICEFEEDBACK_MIN_GLPI, 'lt')) {
        echo "Este plugin requer GLPI >= " . PLUGIN_SERVICEFEEDBACK_MIN_GLPI;
        return false;
    }
    return true;
}

/**
 * Check configuration process for plugin : need to return true if succeeded
 * Can display a message only if failure and $verbose is true
 */
function plugin_servicefeedback_check_config($verbose = false)
{
    if (true) { // Your configuration check
        return true;
    }
    if ($verbose) {
        echo "Installed, but not configured";
    }
    return false;
}

/**
 * Hook para detectar mudança de status do ticket
 */
function plugin_servicefeedback_ticket_update($item)
{
    if ($item instanceof Ticket) {
        $input = $item->input;
      
        // Verifica se o status mudou para fechado (5)
        if (isset($input['status']) && $input['status'] == Ticket::CLOSED) {
            PluginServicefeedbackFeedback::handleTicketClosed($item->getID());
        }
    }
}

/**
 * Adiciona aba no ticket
 */
function plugin_servicefeedback_display_tab($item, $withtemplate = 0)
{
    if ($item instanceof Ticket) {
        return [1 => __('Feedback', 'servicefeedback')];
    }
    return [];
}

/**
 * Conteúdo da aba
 */
function plugin_servicefeedback_display_tab_content($item, $tabnum = 1, $withtemplate = 0)
{
    if ($item instanceof Ticket && $tabnum == 1) {
        PluginServicefeedbackFeedback::showForTicket($item->getID());
        return true;
    }
    return false;
}
