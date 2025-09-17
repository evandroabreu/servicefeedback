<?php
include('../../../inc/includes.php');


$token = $_REQUEST['token'] ?? null;
// echo "<pre>TOKEN DEBUG: " . htmlspecialchars($token) . "</pre>";
error_log("ServiceFeedback DEBUG: TOKEN = $token");
if (!$token) {
    Html::displayErrorAndDie(__('Invalid parameters', 'servicefeedback'));
}

// Submissão do formulário (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $rating   = intval($_POST['rating']);
    $comment  = trim($_POST['comment'] ?? '');

    $success = PluginServicefeedbackFeedback::processRating($token, $rating, $comment);

    Html::header(__('Service Feedback', 'servicefeedback'), $_SERVER['PHP_SELF']);
    echo "<div class='center'>";
    echo "<div style='margin: 50px auto; max-width: 600px; padding: 30px; border: 1px solid #ddd; border-radius: 10px; background: #f9f9f9;'>";
    if ($success) {
        echo "<h2 style='color: #28a745; text-align: center;'>";
        echo "<i class='fas fa-check-circle' style='font-size: 48px; margin-bottom: 20px;'></i><br>";
        echo __('Obrigado pelo seu feedback!', 'servicefeedback');
        echo "</h2>";
        echo "<p style='text-align: center; font-size: 18px; margin: 20px 0;'>Seu nível de satisfação foi registrado com sucesso!</p>";
        echo "<div style='text-align: center; font-size:20px;'>";
        for ($i = 1; $i <= 5; $i++) {
            echo $i <= $rating ? "<span style='color:#ffd700;font-size:30px;'>★</span>" : "<span style='color:#ccc;font-size:30px;'>★</span>";
        }
        echo " ($rating/5)";
        echo "</div>";
        if ($comment) {
            echo "<p style='margin-top:20px;'><b>Comentário:</b> ".Html::entities_deep($comment)."</p>";
        }
        echo "<p style='margin-top:20px; text-align:center;'>Agora você pode fechar esta janela.</p>";
    } else {
        echo "<h2 style='color: #dc3545; text-align: center;'>Erro ao salvar feedback</h2>";
    }
    echo "</div></div>";
    Html::footer();
    exit;
}

// Se for acesso GET com token → mostra formulário
Html::header(__('Service Feedback', 'servicefeedback'), $_SERVER['PHP_SELF']);

?>

<style>
.rating {
  display: inline-flex;
  flex-direction: row-reverse; /* inverte visualmente, mas mantém valores 1–5 */
  font-size: 40px;
}

.rating input {
  display: none;
}

.rating label {
  color: #ccc;
  cursor: pointer;
  transition: color 0.2s;
}

/* Hover: estrela atual e todas as anteriores ficam douradas */
.rating label:hover,
.rating label:hover ~ label {
  color: #ffd700;
}

/* Checked: estrela escolhida e todas as anteriores ficam douradas */
.rating input:checked ~ label {
  color: #ffd700;
}
</style>

<div class='center'>
   <form method="post" style="max-width:600px;margin:30px auto; padding:20px; border:1px solid #ddd; border-radius:10px; background:#f9f9f9;">
      <input type="hidden" name="token" value="<?php echo Html::entities_deep($token); ?>">
      <h2><?php echo __('Avalie nosso atendimento', 'servicefeedback'); ?></h2>

      
      <div class="rating">
  <?php for ($i = 5; $i >= 1; $i--): ?>
     <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
     <label for="star<?php echo $i; ?>">★</label>
  <?php endfor; ?>
</div>
      <div style="margin:20px 0;">
         <label>Comentário (opcional):</label><br>
         <textarea name="comment" rows="4" style="width:100%;resize:vertical;"></textarea>
      </div>

      <div>
         <button type="submit" class="submit">Enviar</button>
      </div>
      <?php Html::closeForm(); ?>
   </form>
</div>
<?php
Html::footer();
