/**
 * Plugin ServiceFeedback for GLPI
 * JavaScript functions
 */

$(document).ready(function() {
   
   // Inicializar tooltips para estrelas de rating
   $('.servicefeedback-stars').each(function() {
      var rating = $(this).data('rating');
      var tooltip = '';
      
      switch(rating) {
         case 1: tooltip = 'Muito insatisfeito'; break;
         case 2: tooltip = 'Insatisfeito'; break;
         case 3: tooltip = 'Neutro'; break;
         case 4: tooltip = 'Satisfeito'; break;
         case 5: tooltip = 'Muito satisfeito'; break;
         default: tooltip = 'Sem avaliação';
      }
      
      $(this).attr('title', tooltip);
   });
   
   // Inicializar tooltips para NPS
   $('.servicefeedback-nps-badge').each(function() {
      var nps = parseInt($(this).text());
      var tooltip = '';
      
      if (nps >= 9) {
         tooltip = 'Promotor (9-10)';
      } else if (nps >= 7) {
         tooltip = 'Neutro (7-8)';
      } else {
         tooltip = 'Detrator (0-6)';
      }
      
      $(this).attr('title', tooltip);
   });
   
   // Função para atualizar gráficos (se houver)
   function updateCharts() {
      // Implementar gráficos com Chart.js ou similar
      // Esta função pode ser expandida conforme necessário
   }
   
   // Função para formatar números
   function formatNumber(num, decimals = 2) {
      return parseFloat(num).toFixed(decimals);
   }
   
   // Função para animar contadores
   function animateCounter(element, target) {
      var current = 0;
      var increment = target / 100;
      var timer = setInterval(function() {
         current += increment;
         if (current >= target) {
            current = target;
            clearInterval(timer);
         }
         $(element).text(formatNumber(current));
      }, 20);
   }
   
   // Animar métricas na página de relatórios
   $('.servicefeedback-metric-value').each(function() {
      var target = parseFloat($(this).text());
      if (!isNaN(target)) {
         $(this).text('0');
         animateCounter(this, target);
      }
   });
   
   // Função para exportar dados
   window.exportFeedbackData = function(format) {
      var params = new URLSearchParams(window.location.search);
      params.set('export', format);
      window.location.href = 'export.php?' + params.toString();
   };
   
   // Função para filtrar relatórios
   window.filterReports = function() {
      $('#filter-form').submit();
   };
   
   // Auto-submit do formulário de filtros quando campos mudam
   $('#filter-form select, #filter-form input[type="date"]').on('change', function() {
      setTimeout(function() {
         $('#filter-form').submit();
      }, 500);
   });
   
   // Função para mostrar/ocultar comentários longos
   $('.servicefeedback-comment-box').each(function() {
      var text = $(this).text();
      if (text.length > 200) {
         var shortText = text.substring(0, 200) + '...';
         var fullText = text;
         
         $(this).html(shortText + ' <a href="#" class="show-more">Mostrar mais</a>');
         
         $(this).find('.show-more').click(function(e) {
            e.preventDefault();
            var parent = $(this).parent();
            if ($(this).text() === 'Mostrar mais') {
               parent.html(fullText + ' <a href="#" class="show-less">Mostrar menos</a>');
            } else {
               parent.html(shortText + ' <a href="#" class="show-more">Mostrar mais</a>');
            }
         });
      }
   });
   
   // Função para validar formulário de configuração
   $('#config-form').on('submit', function(e) {
      var expirationDays = parseInt($('#expiration_days').val());
      
      if (expirationDays < 1 || expirationDays > 365) {
         alert('O prazo de expiração deve estar entre 1 e 365 dias.');
         e.preventDefault();
         return false;
      }
   });
   
   // Função para confirmar exclusão
   $('.delete-config').on('click', function(e) {
      if (!confirm('Tem certeza que deseja excluir esta configuração?')) {
         e.preventDefault();
         return false;
      }
   });
   
   // Inicializar tooltips do Bootstrap se disponível
   if (typeof $().tooltip === 'function') {
      $('[data-toggle="tooltip"]').tooltip();
   }
   
   // Função para atualizar estatísticas em tempo real (AJAX)
   function refreshStats() {
      // Implementar chamadas AJAX para atualizar estatísticas
      // Esta função pode ser expandida conforme necessário
   }
   
   // Atualizar estatísticas a cada 5 minutos
   setInterval(refreshStats, 300000);
   
});

// Função global para mostrar detalhes do feedback
function showFeedbackDetails(feedbackId) {
   // Implementar modal ou página de detalhes
   window.open('feedback.form.php?id=' + feedbackId, '_blank', 'width=800,height=600');
}

// Função global para gerar relatório personalizado
function generateCustomReport() {
   var filters = {
      entity_id: $('#entity_id').val(),
      date_from: $('#date_from').val(),
      date_to: $('#date_to').val(),
      technician_id: $('#technician_id').val(),
      category_id: $('#category_id').val()
   };
   
   var queryString = $.param(filters);
   window.open('report.php?' + queryString, '_blank');
}