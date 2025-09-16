Plugin ServiceFeedback para GLPI
Descrição

O plugin ServiceFeedback permite enviar automaticamente emails de avaliação para os solicitantes quando um chamado é fechado no GLPI. Os usuários podem avaliar o atendimento diretamente no email através de um sistema de 5 estrelas, sem necessidade de acessar páginas externas.

Funcionalidades

Envio automático de emails: Quando um chamado é fechado, um email é enviado automaticamente para o solicitante

Avaliação por estrelas: Sistema de avaliação de 1 a 5 estrelas diretamente no email

Tokens únicos: Cada email possui um token único para evitar avaliações duplicadas

Aba no chamado: Visualização das avaliações recebidas diretamente na aba "Feedback" do chamado

Relatórios completos: Relatórios agregados por técnico, grupo, categoria, entidade e período

Configuração flexível: Personalização do assunto e corpo do email

Logs detalhados: Sistema de logs para depuração e monitoramento

Compatibilidade

GLPI 10.x+

PHP 7.4+

MySQL/MariaDB

Instalação

Extraia o plugin no diretório plugins/ do GLPI

Acesse a interface web do GLPI como administrador

Vá em Configurar > Plugins

Localize o plugin "Service Feedback" e clique em Instalar

Após a instalação, clique em Ativar

Fluxo de Funcionamento

Fechamento do Chamado: Quando um chamado muda para status "Fechado"

Detecção: O hook detecta a mudança de status

Criação do Token: Um token único é gerado e vinculado ao chamado

Registro Pendente: Um registro "pendente" é criado na tabela de avaliações

Envio do Email: Email com estrelas clicáveis é enviado para o solicitante

Avaliação: Usuário clica, no próprio cliente de email, na quantidade de estrelas que representa seu nível de satisfação

Processamento: Token é marcado como usado e avaliação é salva

Visualização: Dados ficam disponíveis na aba "Feedback" do chamado

Relatórios

O plugin oferece relatórios detalhados acessíveis em Ferramentas > Service Feedback > Relatórios:

Estatísticas Gerais

Total de feedbacks enviados

Total de feedbacks respondidos

Taxa de resposta

Avaliação média geral

Relatórios Específicos

Por Técnico: Performance individual dos técnicos

Por Grupo: Performance dos grupos de atendimento

Por Categoria: Avaliação por categoria de chamado

Por Entidade: Dados segmentados por entidade

Por Período: Filtros de data personalizáveis

Logs

Os logs são armazenados em files/_log/servicefeedback/feedback.log e incluem:

Processamento de fechamento de chamados

Criação de tokens

Envio de emails

Processamento de avaliações

Erros e exceções

Personalização
Template de Email

O template padrão pode ser personalizado na configuração:

Olá {requester_name},

Seu chamado #{ticket_id} foi finalizado com sucesso.

Para nos ajudar a melhorar nossos serviços, por favor avalie nosso atendimento clicando em uma das estrelas abaixo:

{rating_stars}

Obrigado pela sua colaboração!

Equipe de Suporte

Variáveis Disponíveis

{ticket_id}: ID do chamado

{requester_name}: Nome do solicitante

{rating_stars}: HTML das estrelas clicáveis

Banco de Dados
Tabelas Criadas
glpi_plugin_servicefeedback_feedbacks

Armazena os feedbacks e avaliações:

id: ID único

tickets_id: ID do chamado

users_id: ID do usuário solicitante

token: Token único para avaliação

rating: Avaliação (1-5 estrelas)

status: Status (pending/completed)

date_creation: Data de criação

date_completion: Data de conclusão

entities_id: ID da entidade

glpi_plugin_servicefeedback_configs

Armazena as configurações:

id: ID único

name: Nome da configuração

value: Valor da configuração

Segurança

Tokens únicos e não reutilizáveis

Validação de parâmetros

Proteção contra CSRF

Logs de auditoria

Suporte

Para suporte e relatório de bugs, consulte a documentação do GLPI ou entre em contato com o administrador do sistema.

Licença

Este plugin é distribuído sob a licença GPLv2+.

Changelog
Versão 1.0.0

Versão inicial

Envio automático de emails de feedback

Sistema de avaliação por estrelas

Relatórios completos

Interface de configuração

Suporte a múltiplos idiomas