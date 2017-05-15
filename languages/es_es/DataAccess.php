<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com, Itop.es
 * *********************************************************************************************************************************** */
$languageStrings = [
	'DataAccess' => 'Acceso a los datos',
	'LBL_NONE' => '--Nada--',
	'Message' => 'Mensaje',
	'Action_unique_value' => 'El valor del campo es único.',
	'Action_Desc_unique_value' => 'Comprobar si el valor del campo introducido es único.',
	'Select the fields to be verified' => 'Seleccionar los campos a verificar',
	'Select a field from which the value is to be checked' => 'Seleccionar el campo cuyo valor será verificado',
	'Field value is not unique' => 'El valor del campo no es único',
	'Please enter a unique value in the' => 'Por favor introduzca un valor único en el:',
	'LBL_LOCKS_SAVE' => 'Grabar bloqueo',
	'LBL_LOCKS_SAVE_LABEL1' => 'No',
	'LBL_LOCKS_SAVE_LABEL2' => 'Sí - Se cumple una condición',
	'LBL_LOCKS_SAVE_LABEL3' => 'Sí - Se cumplen dos condiciones',
	'LBL_LOCKS_SAVE_LABEL4' => 'Si - Ventana modal',
	'LBL_VALIDATION_TWO_FIELDS' => 'Validación para dos campos',
	'LBL_MESSAGE_LOCK0' => 'El mensaje cuando escribir no está bloqueado',
	'LBL_MESSAGE_LOCK1' => 'El mensaje cuando se cumpla una condición',
	'LBL_MESSAGE_LOCK2' => 'El mensaje cuando se cumpla dos condiciones',
	//
	'Action_check_task' => 'Comprobar tarea',
	'Action_Desc_check_task' => 'Esta acción comprueba si la tarea está relacionada a tema y estado apropiado. Si no, el registro se bloquea.',
	'Select status' => 'Seleccionar estado',
	'Subject tasks' => 'Asunto de la Tarea',
	'Message if the task does not exist' => 'Mensaje si la tarea no existe (el mensaje está siendo traducido)',
	//
	'Action_check_alltask' => 'Comprobar todas las Tareas',
	'Action_Desc_check_alltask' => 'Esta acción comprueba si todas las Tareas con el mismo estado están relacionadas. El registro se bloque si lo están.',
	'Message if the task exist' => 'Mensaje si la Tarea existe (el mensaje está siendo traducido)',
	//
	'Action_show_quick_create' => 'Creación rápida',
	'Action_Desc_show_quick_create' => 'Esta acción muestra la creación rápida.',
	'LBL_SELECT_OPTION' => 'Seleccionar una Opción',
	//
	'Action_blockEditView' => 'Bloquear la vista edición',
	'Action_Desc_blockEditView' => 'Esta acción bloquea la vista edición (la edición rápida y completa).',
	//
	'Action_check_taskdate' => 'Comprueba fecha planificada para completar la Tarea de Proyecto',
	'Action_Desc_check_taskdate' => 'Valida fecha planificada para completar la Tarea de Proyecto.',
	'Date can not be greater' => 'La fecha planificada no puede ser mayor que la de comienzo',
	//
	'Action_unique_modules_value' => 'El valor del campo es único en los módulos',
	'Action_Desc_unique_modules_value' => 'Comprobar que el valor del campo es único en los módulos',
	'Check the value in the module' => 'Comprobar el valor en el módulo',
	//
	'Action_check_taskstatus' => 'Comprobar todas las tareas dependientes',
	'Action_Desc_check_taskstatus' => 'Comprobar si todas las tareas dependientes han finalizado.',
	'Subordinate tasks have not been completed yet' => 'Las tareas dependientes todavía no se han completado.',
	//
	'Action_validate_mandatory' => 'Comprobar campos obligatorios',
	'Action_Desc_validate_mandatory' => 'Comprobar si están completos todos los campos obligatorios en la edición rápida.',
	//
	'Action_check_assigneduser' => 'Restricción de cambio de la propiedad del registro',
	'Action_Desc_check_assigneduser' => 'La acción limita la posibilidad de cambiar el propietario a un lista específica.',
	'LBL_SELECT_USER_OR_GROUP' => 'Seleccionar usuarios o grupos permitidos',
	'LBL_CURRENT_USER' => 'Usuario actual',
	//
	'Action_colorList' => 'Colorear las líneas de la lista de registros',
	'Action_Desc_colorList' => 'Esta herramienta permite colorear las líneas que cumplan unas condiciones. Gracias a esto, se pueden diferenciar registros prioritarios.',
	'LBL_BACKGROUND_COLOR' => 'Color de fondo',
	'LBL_TEXT_COLOR' => 'Color de texto',
	'This name already exists' => 'Este nombre ya existe',
	'LBL_RECORD_DELETED' => 'Registro eliminado',
	'Action_test' => 'Prueba de acción',
	'Action_Desc_test' => 'Descripción de la prueba de acción.',
	//
	'Action_check_day_tasks' => 'Comprobar el número de eventos para el dia grabado',
	'Action_Desc_check_day_tasks' => 'La acción comprueba el número de eventos para la fecha de comienzo del evento y, en el caso de que se supere el límite, el sistema bloqueo/informa al usuario. ',
	'LBL_MAXIMUM_NUMBER_EVENTS_PER_DAY' => 'Número máximo de eventos por día',
	'LBL_SELECT_OPTION_TO_SEARCH' => 'Seleccionar opciones para bucar por eventos',
	'LBL_SET_CUSTOM_CONDITIONS' => 'Establecer las condiciones personalizadas',
	'LBL_CURRENT_EVENTS' => 'Eventos actuales',
	'LBL_PAST_EVENTS' => 'Eventos pasados',
	//
	'Action_unique_account' => 'Comprobar Empresas duplicadas',
	'Action_Desc_unique_account' => 'Comprobar si la Empresa es única en el módulo.',
	'LBL_DUPLICATED_FOUND' => 'Encontrados duplicados',
	'LBL_DUPLICTAE_CREATION_CONFIRMATION' => 'Duplicado encontrado. ¿Seguro que lo quieres grabar?',
	'LBL_DUPLICTAE_QUICK_EDIT_CONFIRMATION' => 'Ha habido un intento de modificación de valores en un registro en el modo edición rápida.<br />Seleccinar el campo checkbox y aceptar para poder aplicar los cambios en esta vista.<br />¡Atención!<br />Reintroducir las modificaciones en el registro. ',
	'LBL_DONT_ASK_AGAIN' => 'No preguntar de nuevo para este registro.',
	'LBL_SEARCH_TRASH' => 'Buscar en la papelera',
	'LBL_BLOCK_EDITVIEW' => 'La opción de edición del registro ha sido bloqueada',
	'LBL_CONTENT_MESSAGE' => 'Contenido del mensaje',
];
$jsLanguageStrings = [
	'DataAccess' => 'Acceso a los datos',
];
