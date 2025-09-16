<?php
// include('../../../inc/based_config.php');
/**
 * Plugin ServiceFeedback
 * Feedback class
 */

class PluginServicefeedbackFeedback extends CommonDBTM
{

    public static $rightname = 'plugin_servicefeedback';

    public static function getTypeName($nb = 0)
    {
        return _n('Feedback', 'Feedbacks', $nb, 'servicefeedback');
    }

    /**
     * Manipula o fechamento do chamado
     */
    public static function handleTicketClosed($tickets_id)
    {
        global $DB;

        self::writeLog("Processando fechamento do chamado ID: $tickets_id");

        // Buscar informações do chamado
        $ticket = new Ticket();
        if (!$ticket->getFromDB($tickets_id)) {
            self::writeLog("Erro: Chamado $tickets_id não encontrado");
            return false;
        }

        // Verificar se já existe feedback pendente para este chamado
        $query = "SELECT id FROM glpi_plugin_servicefeedback_feedbacks 
                WHERE tickets_id = $tickets_id AND status = 'pending'";
        $result = $DB->query($query);
      
        if ($DB->numrows($result) > 0) {
            self::writeLog("Feedback já existe para o chamado $tickets_id");
            return false;
        }

        // Buscar o usuário solicitante
        $users_id = $ticket->fields['users_id_recipient'];
        if (empty($users_id)) {
            self::writeLog("Erro: Usuário solicitante não encontrado para o chamado $tickets_id");
            return false;
        }

        // Gerar token único
        $token = self::generateToken();

        // Criar registro de feedback
        $feedback = new self();
        $input = [
           'tickets_id' => $tickets_id,
           'users_id' => $users_id,
           'token' => $token,
           'status' => 'pending',
           'date_creation' => date('Y-m-d H:i:s'),
           'entities_id' => $ticket->fields['entities_id']
        ];

        $feedback_id = $feedback->add($input);
      
        if ($feedback_id) {
            self::writeLog("Feedback criado com ID: $feedback_id para chamado $tickets_id");
         
            // Enviar email
            self::sendFeedbackEmail($feedback_id);
            return true;
        } else {
            self::writeLog("Erro ao criar feedback para chamado $tickets_id");
            return false;
        }
    }

    /**
     * Gera token único
     */
    public static function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Envia email de feedback
     */
    public static function sendFeedbackEmail($feedback_id)
    {
        global $CFG_GLPI;

        $feedback = new self();
        if (!$feedback->getFromDB($feedback_id)) {
            self::writeLog("Erro: Feedback $feedback_id não encontrado");
            return false;
        }

        // Buscar dados do chamado
        $ticket = new Ticket();
        $ticket->getFromDB($feedback->fields['tickets_id']);

        // Buscar dados do usuário
        $user = new User();
        $user->getFromDB($feedback->fields['users_id']);

        // Buscar configurações
        $config = self::getConfig();

        // Preparar variáveis do template
        $variables = [
           '{ticket_id}' => $ticket->fields['id'],
           '{ticket_title}' => $ticket->fields['name'],
           '{requester_name}' => $user->fields['realname'] ?: $user->fields['name'],
           '{requester_firstname}' => $user->fields['firstname'] ?? '',
           '{requester_lastname}'  => $user->fields['realname'] ?? ($user->fields['name'] ?? ''),
           '{rating_stars}' => self::generateRatingLink($feedback->fields['token'])
        ];

        // Substituir variáveis no assunto e corpo
        $subject = str_replace(array_keys($variables), array_values($variables), $config['email_subject']);
        $body = str_replace(array_keys($variables), array_values($variables), $config['email_body']);

        // Configurar email
        $mail = new GLPIMailer();
        $mail->AddAddress($user->getDefaultEmail());
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML(true);

        if ($mail->Send()) {
            self::writeLog("Email de feedback enviado para: " . $user->getDefaultEmail());
            return true;
        } else {
            self::writeLog("Erro ao enviar email de feedback: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Gera HTML das estrelas de avaliação
     */
    public static function generateRatingStars($token)
    {
        global $CFG_GLPI;

        $base_url = $CFG_GLPI['url_base'] . '/plugins/servicefeedback/front/rate.php';
      
        $html = '<div style="text-align: center; margin: 20px 0;">';
        $html .= '<p style="margin-bottom: 15px; font-weight: bold;">Clique nas estrelas para avaliar:</p>';
      
        for ($i = 1; $i <= 5; $i++) {
            $url = $base_url . '?token=' . $token . '&rating=' . $i;
            $html .= '<a href="' . $url . '" style="text-decoration: none; margin: 0 5px;">';
            $html .= '<span style="font-size: 30px; color: #ffd700;">★</span>';
            $html .= '</a>';
        }
      
        $html .= '</div>';
      
        return $html;
    }

    public static function generateRatingLink($token)
    {
        global $CFG_GLPI;

        // Monta URL absoluta
        $url_base = $CFG_GLPI['url_base'] . "/plugins/servicefeedback/front/rate.php";

        return '<p><a href="'.$url_base.'?token='.$token.'" 
                style="display:inline-block;padding:10px 20px;background:#007bff;color:#fff;
                       text-decoration:none;border-radius:5px;">'
               .__('Avaliar atendimento', 'servicefeedback').'</a></p>';
    }

    /**
    * Processa a avaliação (nota + comentário)
    */
    public static function processRating($token, $rating, $comment = null)
    {
        global $DB;

        self::writeLog("Processando avaliação - Token: $token, Rating: $rating, Comentário: $comment");

        // Validar rating
        if (!in_array($rating, [1, 2, 3, 4, 5])) {
            self::writeLog("Erro: Rating inválido: $rating");
            return false;
        }

        // Buscar feedback pelo token
        $query = "SELECT * FROM glpi_plugin_servicefeedback_feedbacks 
                WHERE token = '" . $DB->escape($token) . "' AND status = 'pending'";
        $result = $DB->query($query);

        if ($DB->numrows($result) == 0) {
            self::writeLog("Erro: Token não encontrado ou já utilizado: $token");
            return false;
        }

        $feedback_data = $DB->fetchAssoc($result);

        // Atualizar feedback na tabela do plugin
        $feedback = new self();
        $input = [
           'id'              => $feedback_data['id'],
           'rating'          => $rating,
           'status'          => 'completed',
           'date_completion' => date('Y-m-d H:i:s'),
           'comment'         => $comment
        ];

        $ok_plugin = $feedback->update($input);

        // ==================================================================
        // Também gravar na tabela oficial do GLPI: glpi_ticketsatisfactions
        // ==================================================================
        $tickets_id = (int)$feedback_data['tickets_id'];
        $now        = date('Y-m-d H:i:s');

        $check = $DB->query("SELECT id FROM glpi_ticketsatisfactions 
                           WHERE tickets_id = $tickets_id");
        if ($DB->numrows($check) > 0) {
            $row = $DB->fetchAssoc($check);
            $DB->updateOrDie(
                'glpi_ticketsatisfactions',
                [
                  'satisfaction'   => $rating,
                  'date_answered'  => $now,
                  'comment'        => $comment
            ],
                ['id' => $row['id']],
                "Falha ao atualizar glpi_ticketsatisfactions"
            );
            self::writeLog("Atualizada satisfação em glpi_ticketsatisfactions para chamado $tickets_id");
        } else {
            $DB->insertOrDie(
                'glpi_ticketsatisfactions',
                [
                  'tickets_id'     => $tickets_id,
                  'type'           => 1,
                  'date_begin'     => $now,
                  'date_answered'  => $now,
                  'satisfaction'   => $rating,
                  'comment'        => $comment
            ],
                "Falha ao inserir em glpi_ticketsatisfactions"
            );
            self::writeLog("Inserida satisfação em glpi_ticketsatisfactions para chamado $tickets_id");
        }

        return $ok_plugin;
    }

    /**
     * Exibe feedbacks para um chamado
     */
    public static function showForTicket($tickets_id)
    {
        global $DB;

        $query = "SELECT f.*, u.name as user_name, u.realname as user_realname
                FROM glpi_plugin_servicefeedback_feedbacks f
                LEFT JOIN glpi_users u ON f.users_id = u.id
                WHERE f.tickets_id = $tickets_id
                ORDER BY f.date_creation DESC";

        $result = $DB->query($query);

        echo "<div class='spaced'>";
        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __('User', 'servicefeedback') . "</th>";
        echo "<th>" . __('Rating', 'servicefeedback') . "</th>";
        echo "<th>" . __('Status', 'servicefeedback') . "</th>";
        echo "<th>" . __('Date creation', 'servicefeedback') . "</th>";
        echo "<th>" . __('Date completion', 'servicefeedback') . "</th>";
        echo "</tr>";

        if ($DB->numrows($result) == 0) {
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='5' class='center'>" . __('No feedback found', 'servicefeedback') . "</td>";
            echo "</tr>";
        } else {
            while ($row = $DB->fetchAssoc($result)) {
                echo "<tr class='tab_bg_1'>";
                echo "<td>" . ($row['user_realname'] ?: $row['user_name']) . "</td>";
                echo "<td class='center'>";
                if ($row['rating']) {
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $row['rating']) {
                            echo "<span style='color: #ffd700;'>★</span>";
                        } else {
                            echo "<span style='color: #ccc;'>★</span>";
                        }
                    }
                    echo " (" . $row['rating'] . "/5)";
                } else {
                    echo "-";
                }
                echo "</td>";
                echo "<td class='center'>";
                if ($row['status'] == 'pending') {
                    echo "<span style='color: orange;'>" . __('Pending', 'servicefeedback') . "</span>";
                } else {
                    echo "<span style='color: green;'>" . __('Completed', 'servicefeedback') . "</span>";
                }
                echo "</td>";
                echo "<td class='center'>" . Html::convDateTime($row['date_creation']) . "</td>";
                echo "<td class='center'>" . ($row['date_completion'] ? Html::convDateTime($row['date_completion']) : '-') . "</td>";
                echo "</tr>";
            }
        }

        echo "</table>";
        echo "</div>";
    }

    /**
     * Busca configurações
     */
    public static function getConfig()
    {
        global $DB;

        $config = [];
        $query = "SELECT name, value FROM glpi_plugin_servicefeedback_configs";
        $result = $DB->query($query);

        while ($row = $DB->fetchAssoc($result)) {
            $config[$row['name']] = $row['value'];
        }

        return $config;
    }

    /**
     * Escreve log
     */
    public static function writeLog($message)
    {
        $log_dir = GLPI_LOG_DIR . '/servicefeedback';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }

        $log_file = $log_dir . '/feedback.log';

        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message" . PHP_EOL;

        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    }

}
