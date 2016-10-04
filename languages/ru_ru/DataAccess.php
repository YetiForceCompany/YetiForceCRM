<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com.
 * All Rights Reserved.
 * Contributor(s):
 * *********************************************************************************************************************************** */
$languageStrings = [
	'DataAccess' => 'Редактор условий',
	'LBL_NONE' => '--Нет--',
	'Message' => 'Сообщение',
	//
	'Action_unique_value' => 'Уникальное значение поля',
	'Action_Desc_unique_value' => 'Данная опция позволяет проверить поля на уникальность.',
	'Select the fields to be verified' => 'Выберите поля, значение которых будут сравниваться',
	'Select a field from which the value is to be checked' => 'Выберите поле, значение которого будет проверятся',
	'Field value is not unique' => 'Значение поля не является уникальным.',
	'Please enter a unique value in the' => 'Пожалуйста, введите уникальное значение в:',
	'LBL_LOCKS_SAVE' => 'Блокировать создание записи?',
	'LBL_LOCKS_SAVE_LABEL1' => 'Нет',
	'LBL_LOCKS_SAVE_LABEL2' => 'Да - Если одно из условий выполнено',
	'LBL_LOCKS_SAVE_LABEL3' => 'Да - Если оба условия выполнены',
	'LBL_LOCKS_SAVE_LABEL4' => 'Да - Модальное окно',
	'LBL_VALIDATION_TWO_FIELDS' => 'Проверка двух полей',
	'LBL_MESSAGE_LOCK0' => 'Сообщение отображаемое при сохранении записи.',
	'LBL_MESSAGE_LOCK1' => 'Сообщение при выполнении первого условия',
	'LBL_MESSAGE_LOCK2' => 'Сообщение при выполнении второго условия',
	//
	'Action_check_task' => 'Проверка Задач',
	'Action_Desc_check_task' => 'Данная опция позволяет проверить заданное соответствие полей Тема задачи и Статус задачи, пока данные в этих полях отличаются от указанных, внести изменения в запись нельзя',
	'Select status' => 'Выберите статус',
	'Subject tasks' => 'Тема задачи',
	'Message if the task does not exist' => 'Задача связанная с данной записью, не удовлетворяет требованиям к полям Статус и Тема задачи',
	//
	'Action_check_alltask' => 'Проверка статуса Задач у модуля',
	'Action_Desc_check_alltask' => 'Данная опция проверяет, существуют ли, у данного модуля, задачи с указанным статусом. Если у данного модуля существуют задачи с указанным статусом, то изменить запись будет невозможно.',
	'Message if the task exist' => 'Введите сообщение о существующих задачах с указанным статусом',
	// Не работает
	'Action_show_quick_create' => 'Запустить быстрое создание',
	'Action_Desc_show_quick_create' => 'Данная опция позволяет запустить быстрое создание для выбранного модуля.',
	'LBL_SELECT_OPTION' => 'Выберите опцию',
	// Не работает
	'Action_blockEditView' => 'Запретить редактировать блок',
	'Action_Desc_blockEditView' => 'Данная опция позволяет заблокировать возможность редактирования данных в блоке в кратком или полном виде.',
	//
	'Action_check_taskdate' => 'Проверка Планируемой даты завершения Проектной задачи',
	'Action_Desc_check_taskdate' => 'Данная опция проверяет Планируемую дату завершения Проектной задачи и дату Контрольной точки. Планируемая дата завершения Проектной задачи не может быть больше даты Контрольной точки.',
	'Date can not be greater' => 'Планируемая дата завершения Проектной задачи не может быть больше даты Контрольной точки.',
	//
	'Action_unique_modules_value' => 'Уникальное значение поля в модулях',
	'Action_Desc_unique_modules_value' => 'Данная опция проверяет, является ли входное значение уникальным в модулях.',
	'Check the value in the module' => 'Проверьте значение в модулях',
	//
	'Action_check_taskstatus' => 'Проверка дочерних Проектных задач',
	'Action_Desc_check_taskstatus' => 'Данная опция позволяет проверить статус у дочерних Проектных задач, пока статус дочерних Проектных задач соответствует заданному, Вы не сможете внести изменения в родительскую Проектную задачу.',
	'Subordinate tasks have not been completed yet' => 'Не все дочерние Проектные задачи имеют соответствующий статус',
	//
	'Action_validate_mandatory' => 'Проверка обязательный полей',
	'Action_Desc_validate_mandatory' => 'Данная опция проверяет, все ли обязательные поля, заполнены в форме быстрого создания или редактирования.',
	//
	'Action_check_assigneduser' => 'Ограничение доступа на изменение записи',
	'Action_Desc_check_assigneduser' => 'Данная опция позволяет запретить изменять запись или сохранять запись с указанными изменениями Группам или отдельным пользователям.',
	'LBL_SELECT_USER_OR_GROUP' => 'Выберите пользователей или группы, которым будет ограничен доступ',
	'LBL_CURRENT_USER' => 'Текущий пользователь',
	//
	'Action_colorList' => 'Выделить цветом',
	'Action_Desc_colorList' => 'Данная опция позволяет выделить записи цветом, по определенным условиям.',
	'LBL_BACKGROUND_COLOR' => 'Цвет фона',
	'LBL_TEXT_COLOR' => 'Цвет текста',
	'This name already exists' => 'Такое название уже существует',
	'LBL_RECORD_DELETED' => 'Запись удалена',
	'Action_test' => 'Тест',
	'Action_Desc_test' => 'Нажмите Тест для просмотра результата',
	//
	'Action_check_day_tasks' => 'Проверьте количество событий для сохраненного дня',
	'Action_Desc_check_day_tasks' => 'Действие проверяет количество событий для даты начала мероприятия, и в случае, если она превышает установленный предел, информирует пользователя.',
	'LBL_MAXIMUM_NUMBER_EVENTS_PER_DAY' => 'Максимальное число событий в день',
	'LBL_SELECT_OPTION_TO_SEARCH' => 'Выберите опцию для поиска событий',
	'LBL_SET_CUSTOM_CONDITIONS' => 'Установка пользовательских условий',
	'LBL_CURRENT_EVENTS' => 'Текущие события',
	'LBL_PAST_EVENTS' => 'Прошедшие события',
	//
	'Action_unique_account' => 'Проверка дубликатов контрагентов',
	'Action_Desc_unique_account' => 'Проверьте, что учетная запись является уникальной в модуле.',
	'LBL_DUPLICATED_FOUND' => 'Найдены дубликаты',
	'LBL_DUPLICTAE_CREATION_CONFIRMATION' => 'Найден дубликат. Уверенны что хотите сохранить?',
	'LBL_DUPLICTAE_QUICK_EDIT_CONFIRMATION' => 'Была сделана попытка изменить значения в записи в режиме Быстрого редактирования. <br> Выберите флажок ниже, для того, чтобы изменения вступили в силу. <br> Внимание! <br> Повторные изменения в записи.',
	'LBL_DONT_ASK_AGAIN' => 'Не спрашивать бльше для этой записи.',
	'LBL_SEARCH_TRASH' => 'Поиск мусора',
];
$jsLanguageStrings = [
	'DataAccess' => 'Редактор условий',
];
