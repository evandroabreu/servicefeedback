Plugin ServiceFeedback para GLPI
Descrição

O plugin ServiceFeedback permite enviar automaticamente emails de avaliação para os solicitantes quando um chamado é fechado no GLPI. Os usuários podem avaliar o atendimento diretamente no email através de um sistema de 5 estrelas, sem necessidade de acessar páginas externas.

Funcionalidades

Envio automático de emails: Quando um chamado é fechado, um email é enviado automaticamente para o solicitante

Avaliação por estrelas: Sistema de avaliação de 1 a 5 estrelas com campo opcional para comentários

Tokens únicos: Cada email possui um token único para evitar avaliações duplicadas

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

O plugin é integrado ao plugin Dashboard(https://github.com/serviceticst/glpi-plugin-dashboard/releases) o que permite utilizar
todos os relatórios fornecidos por este.

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

Licença

Este plugin é distribuído sob a licença GPLv2+.

Changelog
Versão 1.0.0

Versão inicial

Envio automático de emails de feedback

Sistema de avaliação por estrelas