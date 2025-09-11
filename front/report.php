<?php
/**
 * Plugin ServiceFeedback
 * Reports page
 */

include('../../../inc/includes.php');

Session::checkRight('plugin_servicefeedback', READ);

Html::header(__('Service Feedback Reports', 'servicefeedback'), $_SERVER['PHP_SELF'], 'tools', 'PluginServicefeedbackMenu');

PluginServicefeedbackReport::showReports();

Html::footer();
