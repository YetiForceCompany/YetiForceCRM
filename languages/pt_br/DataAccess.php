<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com.
 * All Rights Reserved.
 * Contributor(s): Valmir C. Trindade - Brazilian Portuguese Translation - 08/12/2014 - www.ttcasolucoes.com.br
 * *********************************************************************************************************************************** */
$languageStrings = [
	'DataAccess' => 'Validação de acesso',
	'LBL_NONE' => '--Nada--',
	'Message' => 'Mensagem',
	'Action_unique_value' => 'O valor do campo é único.',
	'Action_Desc_unique_value' => 'Verificar se o valor do campo especificado é único.',
	'Select the fields to be verified' => 'Selecionar os campos para serem verificados',
	'Select a field from which the value is to be checked' => 'Selecionar um campo a partir do qual o valor será verificado',
	'Field value is not unique' => 'O valor do campo não é único',
	'Please enter a unique value in the' => 'Por favor, digite um valor único no:',
	'LBL_LOCKS_SAVE' => 'Salvar bloqueio',
	'LBL_LOCKS_SAVE_LABEL1' => 'Não',
	'LBL_LOCKS_SAVE_LABEL2' => 'Sim - Uma condição satisfeita',
	'LBL_LOCKS_SAVE_LABEL3' => 'Sim - Duas condições satisfeitas',
	'LBL_LOCKS_SAVE_LABEL4' => 'Yes - Modal window',
	'LBL_VALIDATION_TWO_FIELDS' => 'Validação para dois campos',
	'LBL_MESSAGE_LOCK0' => 'Mensagem quando escrita não é bloqueada',
	'LBL_MESSAGE_LOCK1' => 'Mensagem para uma condição satisfeita',
	'LBL_MESSAGE_LOCK2' => 'Mensagem para uma condição satisfeita',
	//
	'Action_check_task' => 'Verificar tarefa',
	'Action_Desc_check_task' => 'Esta ação verifica se a tarefa está relacionada a um assunto e status apropriados. Se não estiver, o valor digitado é bloqueado.',
	'Select status' => 'Selecionar status',
	'Subject tasks' => 'Assunto da Tarefa',
	'Message if the task does not exist' => 'Mensagem se a tarefa não existir(mensagem está sendo traduzida)',
	//
	'Action_check_alltask' => 'Verificar todas as tarefas',
	'Action_Desc_check_alltask' => 'Esta ação verifica se todas as tarefas, com o mesmo status, estão relacionadas. Se estiverem relacionadas, o valor digitado será bloqueado.',
	'Message if the task exist' => 'Mensagem se a tarefa existir (mensagem está sendo traduzida)',
	//
	'Action_show_quick_create' => 'Criar rápido',
	'Action_Desc_show_quick_create' => 'Esta ação exibe Criar Rápido.',
	'LBL_SELECT_OPTION' => 'Selecione Opção',
	//
	'Action_blockEditView' => 'Bloco Visualização',
	'Action_Desc_blockEditView' => 'Esta ação bloqueia a edição (edição rápida e completa).',
	//
	'Action_check_taskdate' => 'Verificar data prevista para conclusão da tarefa do Projeto',
	'Action_Desc_check_taskdate' => 'Validar data prevista para conclusão da tarefa do Projeto.',
	'Date can not be greater' => 'A data prevista não pode ser maior do que a data do estágio',
	//
	'Action_unique_modules_value' => 'O valor do campos é único nos módulos',
	'Action_Desc_unique_modules_value' => 'Verificar se o valor dos campos é único nos módulos',
	'Check the value in the module' => 'Verificar o valor no módulo',
	//
	'Action_check_taskstatus' => 'Verificar todas as tarefas subordinadas',
	'Action_Desc_check_taskstatus' => 'Verificar se todas as tarefas subordinadas foram concluídas.',
	'Subordinate tasks have not been completed yet' => 'As tarefas subordinadas ainda não estão concluídas.',
	//
	'Action_validate_mandatory' => 'Verificar campos obrigatórios',
	'Action_Desc_validate_mandatory' => 'Verificar se todos os campos obrigatórios no Editar Rápido estão preenchidos.',
	//
	'Action_check_assigneduser' => 'Mudança na restrição para proprietário do registro',
	'Action_Desc_check_assigneduser' => 'A ação restringe a capacidade para alterar o proprietário de uma lista específica.',
	'LBL_SELECT_USER_OR_GROUP' => 'Selecionar os Usuários ou Grupos permitidos',
	'LBL_CURRENT_USER' => 'Usuário atual',
	//
	'Action_colorList' => 'Colorindo linhas na lista de registros',
	'Action_Desc_colorList' => 'Esta ferramenta permite colorir linhas que satisfaçam as condições correspondentes. Graças a esta funcionalidade, os registros com prioridade mais elevada podem ser diferenciados.',
	'LBL_BACKGROUND_COLOR' => 'Cor de fundo',
	'LBL_TEXT_COLOR' => 'Cor do texto',
	'This name already exists' => 'este nome já existe',
	'LBL_RECORD_DELETED' => 'Registros apagados',
	'Action_test' => 'Teste ação',
	'Action_Desc_test' => 'Descrição teste ação.',
	//
	'Action_check_day_tasks' => 'Check the number of events for the saved day',
	'Action_Desc_check_day_tasks' => 'The action checks the number of events for the event\'s start date, and in case it exceeds the limit, the system blocks/informs the user.',
	'LBL_MAXIMUM_NUMBER_EVENTS_PER_DAY' => 'Maximum number of events per day',
	'LBL_SELECT_OPTION_TO_SEARCH' => 'Select option to search for events',
	'LBL_SET_CUSTOM_CONDITIONS' => 'Set custom conditions',
	'LBL_CURRENT_EVENTS' => 'Current events',
	'LBL_PAST_EVENTS' => 'Past events',
	//
	'Action_unique_account' => 'Check for account duplicates',
	'Action_Desc_unique_account' => 'Check if account is unique in the module.',
	'LBL_DUPLICATED_FOUND' => 'Duplicates found',
	'LBL_DUPLICTAE_CREATION_CONFIRMATION' => 'Duplicate found. Are you sure you want to save?',
	'LBL_DUPLICTAE_QUICK_EDIT_CONFIRMATION' => 'An attempt was made to change values in a record in Quick Edition mode.<br>Select the checkbox field below and accept in order to apply changes in this view.<br>Attention!<br>Re-enter the modifications in the record.',
	'LBL_DONT_ASK_AGAIN' => 'Don\'t ask again for this record.',
	'LBL_SEARCH_TRASH' => 'Search trash',
];
$jsLanguageStrings = [
	'DataAccess' => 'Acesso Dados',
];
