<?php
/**
 * Plugin ServiceFeedback
 * Configuration page
 */

include('../../../inc/includes.php');

Session::checkRight('plugin_servicefeedback', UPDATE);

if (isset($_POST['update_config'])) {
    PluginServicefeedbackConfig::updateConfig($_POST);
    Session::addMessageAfterRedirect(__('Configuration updated successfully', 'servicefeedback'));
    Html::redirect($_SERVER['PHP_SELF']);
}

Html::header(__('Service Feedback Configuration', 'servicefeedback'), $_SERVER['PHP_SELF'], 'tools', 'PluginServicefeedbackMenu');

PluginServicefeedbackConfig::showConfigForm();

Html::footer();
