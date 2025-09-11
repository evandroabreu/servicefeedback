<?php
/**
 * Plugin ServiceFeedback
 * Rating endpoint
 */

include('../../../inc/includes.php');

// Verificar parâmetros
if (!isset($_GET['token']) || !isset($_GET['rating'])) {
    Html::displayErrorAndDie(__('Invalid parameters', 'servicefeedback'));
}

$token = $_GET['token'];
$rating = intval($_GET['rating']);

// Processar avaliação
$success = PluginServicefeedbackFeedback::processRating($token, $rating);

// Página de resposta
Html::header(__('Service Feedback', 'servicefeedback'), $_SERVER['PHP_SELF']);

echo "<div class='center'>";
echo "<div style='margin: 50px auto; max-width: 600px; padding: 30px; border: 1px solid #ddd; border-radius: 10px; background: #f9f9f9;'>";

if ($success) {
    echo "<h2 style='color: #28a745; text-align: center;'>";
    echo "<i class='fas fa-check-circle' style='font-size: 48px; margin-bottom: 20px;'></i><br>";
    echo __('Obrigado pelo seu feedback!', 'servicefeedback');
    echo "</h2>";
   
    echo "<p style='text-align: center; font-size: 18px; margin: 20px 0;'>";
    echo __('Seu nível de satisfação foi registrado com sucesso!', 'servicefeedback');
    echo "</p>";
   
    echo "<div style='text-align: center; margin: 20px 0;'>";
    echo "<span style='font-size: 24px;'>" . __('Seu nível de satisfação:', 'servicefeedback') . " </span>";
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            echo "<span style='color: #ffd700; font-size: 30px;'>★</span>";
        } else {
            echo "<span style='color: #ccc; font-size: 30px;'>★</span>";
        }
    }
    echo " ($rating/5)";
    echo "</div>";

} else {
    echo "<h2 style='color: #dc3545; text-align: center;'>";
    echo "<i class='fas fa-exclamation-triangle' style='font-size: 48px; margin-bottom: 20px;'></i><br>";
    echo __('Erro ao processar feedback', 'servicefeedback');
    echo "</h2>";
   
    echo "<p style='text-align: center; font-size: 18px; margin: 20px 0;'>";
    echo __('Este link de feedback pode já ter sido usado ou é inválido.', 'servicefeedback');
    echo "</p>";
}

echo "<p style='text-align: center; margin-top: 30px;'>";
echo __('Agora você pode fechar esta janela.', 'servicefeedback');
echo "</p>";

echo "</div>";
echo "</div>";

Html::footer();
