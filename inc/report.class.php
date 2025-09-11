<?php
/**
 * Plugin ServiceFeedback
 * Report class
 */

class PluginServicefeedbackReport extends CommonGLPI
{

    public static $rightname = 'plugin_servicefeedback';

    /**
     * Exibe relatórios
     */
    public static function showReports()
    {
        echo "<div class='spaced'>";
      
        // Formulário de filtros
        self::showFilterForm();
      
        echo "<br>";
      
        // Estatísticas gerais
        self::showGeneralStats();
      
        echo "<br>";
      
        // Relatório por técnico
        self::showTechnicianReport();
      
        echo "<br>";
      
        // Relatório por grupo
        self::showGroupReport();
      
        echo "<br>";
      
        // Relatório por categoria
        self::showCategoryReport();
      
        echo "</div>";
    }

    /**
     * Formulário de filtros
     */
    public static function showFilterForm()
    {
        global $CFG_GLPI;

        $date_start = $_GET['date_start'] ?? date('Y-m-01');
        $date_end = $_GET['date_end'] ?? date('Y-m-t');
        $entity = $_GET['entity'] ?? 0;

        echo "<form method='get' action='" . $CFG_GLPI['root_doc'] . "/plugins/servicefeedback/front/report.php'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='4'>" . __('Filters', 'servicefeedback') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Start date') . "</td>";
        echo "<td><input type='date' name='date_start' value='$date_start'></td>";
        echo "<td>" . __('End date') . "</td>";
        echo "<td><input type='date' name='date_end' value='$date_end'></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Entity') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entity', 'value' => $entity]);
        echo "</td>";
        echo "<td colspan='2'>";
        echo "<input type='submit' value='" . __('Apply') . "' class='submit'>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        echo "</form>";
    }

    /**
     * Estatísticas gerais
     */
    public static function showGeneralStats()
    {
        global $DB;

        $where = self::buildWhereClause();

        // Total de feedbacks
        $query = "SELECT 
                  COUNT(*) as total,
                  COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                  AVG(CASE WHEN rating IS NOT NULL THEN rating END) as avg_rating
                FROM glpi_plugin_servicefeedback_feedbacks f
                LEFT JOIN glpi_tickets t ON f.tickets_id = t.id
                WHERE $where";

        $result = $DB->query($query);
        $stats = $DB->fetchAssoc($result);

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='4'>" . __('General Statistics', 'servicefeedback') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='center'><strong>" . __('Total Feedbacks', 'servicefeedback') . "</strong><br>" . $stats['total'] . "</td>";
        echo "<td class='center'><strong>" . __('Completed', 'servicefeedback') . "</strong><br>" . $stats['completed'] . "</td>";
        echo "<td class='center'><strong>" . __('Response Rate', 'servicefeedback') . "</strong><br>" .
             ($stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100, 1) . '%' : '0%') . "</td>";
        echo "<td class='center'><strong>" . __('Average Rating', 'servicefeedback') . "</strong><br>" .
             ($stats['avg_rating'] ? round($stats['avg_rating'], 2) . '/5' : '-') . "</td>";
        echo "</tr>";

        echo "</table>";
    }

    /**
     * Relatório por técnico
     */
    public static function showTechnicianReport()
    {
        global $DB;

        $where = self::buildWhereClause();

        $query = "SELECT 
                  u.name as tech_name,
                  u.realname as tech_realname,
                  COUNT(f.id) as total_feedbacks,
                  COUNT(CASE WHEN f.status = 'completed' THEN 1 END) as completed_feedbacks,
                  AVG(CASE WHEN f.rating IS NOT NULL THEN f.rating END) as avg_rating
                FROM glpi_plugin_servicefeedback_feedbacks f
                LEFT JOIN glpi_tickets t ON f.tickets_id = t.id
                LEFT JOIN glpi_users u ON t.users_id_assign = u.id
                WHERE $where AND u.id IS NOT NULL
                GROUP BY u.id
                ORDER BY avg_rating DESC, completed_feedbacks DESC";

        $result = $DB->query($query);

        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='5'>" . __('Report by Technician', 'servicefeedback') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<th>" . __('Technician') . "</th>";
        echo "<th>" . __('Total Feedbacks') . "</th>";
        echo "<th>" . __('Completed') . "</th>";
        echo "<th>" . __('Response Rate') . "</th>";
        echo "<th>" . __('Average Rating') . "</th>";
        echo "</tr>";

        while ($row = $DB->fetchAssoc($result)) {
            $response_rate = $row['total_feedbacks'] > 0 ?
                            round(($row['completed_feedbacks'] / $row['total_feedbacks']) * 100, 1) : 0;

            echo "<tr class='tab_bg_1'>";
            echo "<td>" . ($row['tech_realname'] ?: $row['tech_name']) . "</td>";
            echo "<td class='center'>" . $row['total_feedbacks'] . "</td>";
            echo "<td class='center'>" . $row['completed_feedbacks'] . "</td>";
            echo "<td class='center'>" . $response_rate . "%</td>";
            echo "<td class='center'>";
            if ($row['avg_rating']) {
                echo round($row['avg_rating'], 2) . "/5 ";
                self::showStars($row['avg_rating']);
            } else {
                echo "-";
            }
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }

    /**
     * Relatório por grupo
     */
    public static function showGroupReport()
    {
        global $DB;

        $where = self::buildWhereClause();

        $query = "SELECT 
                  g.name as group_name,
                  COUNT(f.id) as total_feedbacks,
                  COUNT(CASE WHEN f.status = 'completed' THEN 1 END) as completed_feedbacks,
                  AVG(CASE WHEN f.rating IS NOT NULL THEN f.rating END) as avg_rating
                FROM glpi_plugin_servicefeedback_feedbacks f
                LEFT JOIN glpi_tickets t ON f.tickets_id = t.id
                LEFT JOIN glpi_groups g ON t.groups_id_assign = g.id
                WHERE $where AND g.id IS NOT NULL
                GROUP BY g.id
                ORDER BY avg_rating DESC, completed_feedbacks DESC";

        $result = $DB->query($query);

        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='5'>" . __('Report by Group', 'servicefeedback') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<th>" . __('Group') . "</th>";
        echo "<th>" . __('Total Feedbacks') . "</th>";
        echo "<th>" . __('Completed') . "</th>";
        echo "<th>" . __('Response Rate') . "</th>";
        echo "<th>" . __('Average Rating') . "</th>";
        echo "</tr>";

        while ($row = $DB->fetchAssoc($result)) {
            $response_rate = $row['total_feedbacks'] > 0 ?
                            round(($row['completed_feedbacks'] / $row['total_feedbacks']) * 100, 1) : 0;

            echo "<tr class='tab_bg_1'>";
            echo "<td>" . $row['group_name'] . "</td>";
            echo "<td class='center'>" . $row['total_feedbacks'] . "</td>";
            echo "<td class='center'>" . $row['completed_feedbacks'] . "</td>";
            echo "<td class='center'>" . $response_rate . "%</td>";
            echo "<td class='center'>";
            if ($row['avg_rating']) {
                echo round($row['avg_rating'], 2) . "/5 ";
                self::showStars($row['avg_rating']);
            } else {
                echo "-";
            }
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }

    /**
     * Relatório por categoria
     */
    public static function showCategoryReport()
    {
        global $DB;

        $where = self::buildWhereClause();

        $query = "SELECT 
                  c.name as category_name,
                  COUNT(f.id) as total_feedbacks,
                  COUNT(CASE WHEN f.status = 'completed' THEN 1 END) as completed_feedbacks,
                  AVG(CASE WHEN f.rating IS NOT NULL THEN f.rating END) as avg_rating
                FROM glpi_plugin_servicefeedback_feedbacks f
                LEFT JOIN glpi_tickets t ON f.tickets_id = t.id
                LEFT JOIN glpi_itilcategories c ON t.itilcategories_id = c.id
                WHERE $where AND c.id IS NOT NULL
                GROUP BY c.id
                ORDER BY avg_rating DESC, completed_feedbacks DESC";

        $result = $DB->query($query);

        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='5'>" . __('Report by Category', 'servicefeedback') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<th>" . __('Category') . "</th>";
        echo "<th>" . __('Total Feedbacks') . "</th>";
        echo "<th>" . __('Completed') . "</th>";
        echo "<th>" . __('Response Rate') . "</th>";
        echo "<th>" . __('Average Rating') . "</th>";
        echo "</tr>";

        while ($row = $DB->fetchAssoc($result)) {
            $response_rate = $row['total_feedbacks'] > 0 ?
                            round(($row['completed_feedbacks'] / $row['total_feedbacks']) * 100, 1) : 0;

            echo "<tr class='tab_bg_1'>";
            echo "<td>" . $row['category_name'] . "</td>";
            echo "<td class='center'>" . $row['total_feedbacks'] . "</td>";
            echo "<td class='center'>" . $row['completed_feedbacks'] . "</td>";
            echo "<td class='center'>" . $response_rate . "%</td>";
            echo "<td class='center'>";
            if ($row['avg_rating']) {
                echo round($row['avg_rating'], 2) . "/5 ";
                self::showStars($row['avg_rating']);
            } else {
                echo "-";
            }
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }

    /**
     * Constrói cláusula WHERE para filtros
     */
    public static function buildWhereClause()
    {
        global $DB;

        $where = "1=1";

        if (isset($_GET['date_start']) && !empty($_GET['date_start'])) {
            $where .= " AND f.date_creation >= '" . $DB->escape($_GET['date_start']) . " 00:00:00'";
        }

        if (isset($_GET['date_end']) && !empty($_GET['date_end'])) {
            $where .= " AND f.date_creation <= '" . $DB->escape($_GET['date_end']) . " 23:59:59'";
        }

        if (isset($_GET['entity']) && $_GET['entity'] > 0) {
            $where .= " AND f.entities_id = " . intval($_GET['entity']);
        }

        return $where;
    }

    /**
     * Exibe estrelas visuais
     */
    public static function showStars($rating)
    {
        $rating = round($rating);
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                echo "<span style='color: #ffd700;'>★</span>";
            } else {
                echo "<span style='color: #ccc;'>★</span>";
            }
        }
    }
}
