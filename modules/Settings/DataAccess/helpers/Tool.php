<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */
include_once 'include/main/WebUI.php';
include_once 'modules/com_vtiger_workflow/include.php';
include_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.php';
include_once 'modules/com_vtiger_workflow/VTEntityMethodManager.php';

class Oss_Tool
{

	/**
	 * Dodaje pola Created Time i Modified Time do modułu.
	 *
	 * @param string $moduleName - nazwa modułu
	 * @param string $blockLabel - etykieta bloku
	 */
	public static function addCreatedtimeAndModifiedtimeField($moduleName, $blockLabel)
	{
		if (self::checkArg(func_get_args(), 2)) {
			vglobal('Vtiger_Utils_Log', true);

			self::addUitype70Field($moduleName, $blockLabel, 'createdtime', 'Created Time');
			self::addUitype70Field($moduleName, $blockLabel, 'modifiedtime', 'Modified Time');
		}
	}

	/**
	 * Dodaje od modułu pole uitype 70 odpowieadające za 
	 * wyświetlanie czasu utworzenia i modyfikacji rekordu.
	 *
	 * @param string $moduleName - nazwa modułu
	 * @param string $blockLabel - etykieta bloku
	 * @param string $fieldName - nazwa pola
	 * @param string $fieldLabel - etykieta pola
	 */
	public static function addUitype70Field($moduleName, $blockLabel, $fieldName, $fieldLabel)
	{
		if (self::checkArg(func_get_args(), 4)) {
			vglobal('Vtiger_Utils_Log', true);

			$tabid = vtlib\Functions::getModuleId($moduleName);
			$blockId = \vtlib\Deprecated::getBlockId($tabid, $blockLabel);

			$moduleInstance = vtlib\Module::getInstance($moduleName);
			$blockInstance = vtlib\Block::getInstance($blockId, $moduleInstance);

			$fieldInstance = new vtlib\Field();
			$fieldInstance->name = $fieldName;
			$fieldInstance->table = 'vtiger_crmentity';
			$fieldInstance->label = $fieldLabel;
			$fieldInstance->column = 'createdtime';
			$fieldInstance->columntype = 'int(19)';
			$fieldInstance->uitype = 70;
			$fieldInstance->typeofdata = 'T~O';
			$fieldInstance->displaytype = 2;
			$blockInstance->addField($fieldInstance);
		}
	}

	/**
	 * Dodaje od modułu pole uitype 56 (checkbox)
	 *
	 * @param string $moduleName nazwa modułu
	 * @param string $blockLabel etykieta bloku
	 * @param string $fieldName nazwa pola 
	 * @param string $fieldLabel etykieta pola, jeśli nie jest podany etykieta jest taka jak nazwa pola
	 */
	public static function addUitype56Field($moduleName, $blockLabel, $fieldName = NULL, $fieldLabel = NULL)
	{
		if (self::checkArg(func_get_args(), 2)) {
			vglobal('Vtiger_Utils_Log', true);

			$tabid = vtlib\Functions::getModuleId($moduleName);
			$blockId = \vtlib\Deprecated::getBlockId($tabid, $blockLabel);

			$moduleInstance = vtlib\Module::getInstance($moduleName);
			$blockInstance = vtlib\Block::getInstance($blockId, $moduleInstance);

			$field = new vtlib\Field();

			if ($fieldName) {
				$field->name = strtolower($fieldName);
			} else {
				$field->name = Oss_Tool::generateFieldName();
			}

			if (!$fieldName) {
				if (isset($moduleInstance->customFieldTable)) {
					$field->table = $moduleInstance->customFieldTable[0];
				} else {
					$field->table = 'vtiger_' . strtolower($moduleName) . 'cf';
				}
			} else {
				$field->table = $moduleInstance->table_name;
			}
			if ($fieldLabel) {
				$field->label = $fieldLabel;
			} else {
				$field->label = $fieldName;
			}

			$field->column = strtolower($fieldName);
			$field->columntype = 'int(5)';
			$field->uitype = 56;
			$field->typeofdata = 'C~O';
			$blockInstance->addField($field);
		}
	}

	/**
	 * Dodaje od modułu pole uitype 15 (select / picklist)
	 *
	 * @param string $moduleName - nazwa modułu
	 * @param string $blockLabel - etykieta bloku
	 * @param array $pickValue - tablica zawierająca listę opcji do wyboru
	 * @param string $fieldName - nazwa pola 
	 * @param string $defaultvalue - domyślnie zaznaczona wartośc
	 * @param bool $mandatory - czy pole ma być obowiązkowe
	 * @param string $fieldLabel - etykieta pola, jeśli nie jest podany etykieta jest taka jak nazwa pola
	 */
	public static function addUitype15Field($moduleName, $blockLabel, $pickValue, $fieldName = NULL, $defaultvalue = NULL, $mandatory = false, $fieldLabel = NULL)
	{
		if (self::checkArg(func_get_args(), 3)) {

			$tabid = vtlib\Functions::getModuleId($moduleName);
			$blockId = \vtlib\Deprecated::getBlockId($tabid, $blockLabel);

			$moduleInstance = vtlib\Module::getInstance($moduleName);
			$blockInstance = vtlib\Block::getInstance($blockId, $moduleInstance);

			$field = new vtlib\Field();

			if ($fieldName) {
				$field->name = strtolower($fieldName);
			} else {
				$field->name = Oss_Tool::generateFieldName();
			}

			if ($fieldLabel) {
				$field->label = $fieldLabel;
			} else {
				$field->label = $fieldName;
			}

			if (!$fieldName) {
				if (isset($moduleInstance->customFieldTable)) {
					$field->table = $moduleInstance->customFieldTable[0];
				} else {
					$field->table = 'vtiger_' . strtolower($moduleName) . 'cf';
				}
			} else {
				$field->table = $moduleInstance->table_name;
			}

			$field->uitype = 15;

			if ($mandatory) {
				$field->typeofdata = 'V~M';
			} else {
				$field->typeofdata = 'V~O';
			}

			$field->readonly = 1;
			$field->displaytype = 1;
			$field->masseditable = 1;
			$field->quickcreate = 1;
			$field->columntype = 'VARCHAR(255)';

			if ($defaultvalue) {
				$field->defaultvalue = $defaultvalue;
			}

			$blockInstance->addField($field);
			$field->setPicklistValues($pickValue);
		}
	}

	/**
	 * Dodaje od modułu pole uitype 10
	 *
	 * @param string $moduleName nazwa modułu
	 * @param string $blockLabel etykieta bloku
	 * @param array $relModuleList tablica zawierająca listę modułów powiązanych
	 * @param string $fieldName nazwa pola 
	 * @param bool $mandatory czy pole ma być obowiązkowe
	 * @param string $fieldLabel etykieta pola, jeśli nie jest podany etykieta jest taka jak nazwa pola
	 */
	public static function addUitype10Field($moduleName, $blockLabel, $relModuleList, $fieldName, $mandatory = false, $fieldLabel = NULL)
	{
		if (self::checkArg(func_get_args(), 4)) {
			vglobal('Vtiger_Utils_Log', true);

			$tabid = vtlib\Functions::getModuleId($moduleName);
			$blockId = \vtlib\Deprecated::getBlockId($tabid, $blockLabel);

			$moduleInstance = vtlib\Module::getInstance($moduleName);
			$blockInstance = vtlib\Block::getInstance($blockId, $moduleInstance);

			$fieldInstance = new vtlib\Field();
			$fieldInstance->name = strtolower($fieldName);

			if ($moduleInstance->table_name) {
				$fieldInstance->table = $moduleInstance->table_name;
			} else {
				$fieldInstance->table = 'vtiger_' . strtolower($moduleName);
			}

			if ($fieldLabel) {
				$fieldInstance->label = $fieldLabel;
			} else {
				$fieldInstance->label = $fieldName;
			}

			$fieldInstance->column = $fieldName;
			$fieldInstance->columntype = 'int(19)';
			$fieldInstance->uitype = 10;

			if ($mandatory) {
				$fieldInstance->typeofdata = 'V~M';
			} else {
				$fieldInstance->typeofdata = 'V~O';
			}

			$blockInstance->addField($fieldInstance);
			$fieldInstance->setRelatedModules($relModuleList);
		}
	}

	/**
	 * Funkcja ustawia numerację modułu
	 *
	 * @param string $moduleName nazwa modułu
	 * @param string $methodName nazwa metody
	 * @param string $functionPath ścieżka do metody
	 */
	public static function addFunctionToWorkflow($moduleName, $methodName, $functionPath)
	{
		if (self::checkArg(func_get_args(), 3)) {
			vglobal('Vtiger_Utils_Log', true);

			$db = PearDatabase::getInstance();
			$vtemm = new VTEntityMethodManager($db);
			$vtemm->addEntityMethod($moduleName, $methodName, $functionPath, $methodName);
		}
	}

	/**
	 * Funkcja ustawia dodaje moduł powiązany (zakładka modułu po prawej stronie w widoku detail)
	 *
	 * @param string $baseModule moduł w jakim ma być wyświetlana zakładka
	 * @param string $relatedModule moduł, który ma być powiązany
	 * @param string $relatedFunction nazwa funkcji odpowiadającej za relacje
	 * @param array $action dostępne akcje
	 */
	public static function addRelatedModule($baseModule, $relatedModule, $relatedFunction, $action)
	{
		if (self::checkArg(func_get_args(), 4)) {
			vglobal('Vtiger_Utils_Log', true);

			$relModuleObj = vtlib\Module::getInstance($relatedModule);
			$baseModuleObj = vtlib\Module::getInstance($baseModule);
			$baseModuleObj->setRelatedList($relModuleObj, $relatedModule, $action, $relatedFunction);
		}
	}

	/**
	 * Funkcja dodaje widget do dashboardu modułu
	 *
	 * @param string $moduleName nazwa modułu
	 * @param string $widgetName nazwa widgetu
	 * @param string $widgetLink link do widoku
	 */
	public static function addDashboardWidget($moduleName, $widgetName, $widgetLink)
	{
		if (self::checkArg(func_get_args(), 3)) {
			self::addLink('DASHBOARDWIDGET', $moduleName, $widgetName, $widgetLink);
		}
	}

	/**
	 * Funkcja dodaje widget w widoku detail w kolumnie po lewej stronie
	 *
	 * @param string $moduleName nazwa modułu
	 * @param string $widgetName nazwa widgetu
	 * @param string $widgetLink link do widoku
	 */
	public static function addDetailViewSidebarWidget($moduleName, $widgetName, $widgetLink)
	{
		if (self::checkArg(func_get_args(), 3)) {
			self::addLink('DETAILVIEWSIDEBARWIDGET', $moduleName, $widgetName, $widgetLink);
		}
	}

	/**
	 * Funkcja dodaje widget w widoku listy w kolumnie po lewej stronie
	 *
	 * @param string $moduleName nazwa modułu
	 * @param string $widgetName nazwa widgetu
	 * @param string $widgetLink link do widoku
	 */
	public static function addListViewSidebarWidget($moduleName, $widgetName, $widgetLink)
	{
		if (self::checkArg(func_get_args(), 3)) {
			self::addLink('LISTVIEWSIDEBARWIDGET', $moduleName, $widgetName, $widgetLink);
		}
	}

	/**
	 * Funkcja plik widoczy w całym module
	 *
	 * @param string $moduleName nazwa modułu
	 * @param string $widgetName nazwa widgetu
	 * @param string $filePath link do widoku
	 */
	public static function addHeaderScript($moduleName, $widgetName, $filePath)
	{
		if (self::checkArg(func_get_args(), 3)) {
			self::addLink('HEADERSCRIPT', $moduleName, $widgetName, $filePath);
		}
	}

	/**
	 * Funkcja link do tablicy vtiger_links
	 *
	 * @param string $type typ linku
	 * @param string $moduleName nazwa modułu
	 * @param string $widgetName nazwa widgetu
	 * @param string $link link
	 */
	private static function addLink($type, $moduleName, $widgetName, $link)
	{
		vglobal('Vtiger_Utils_Log', true);
		$tabId = vtlib\Functions::getModuleId($moduleName);
		if ($tabId) {
			vtlib\Link::addLink($tabId, $type, $widgetName, $link);
		} else {
			vtlib\Utils::Log('tabid module not found - check if module name is correct');
		}
	}

	/**
	 * Funkcja sprawdza czy wszystkie wymagane parametry funkcji zostały podane. 
	 * W razie braku wszystkich parametrów dopisuje do logu odpowiednią informację
	 *
	 * @param array $parameterList lista parametrów
	 * @param int $numMandatoryArg liczba wymaganych paramtrów przez funkcję
	 * @return bool W zależności od tego czy wszystkie paramtry zostały podane (true) czy nie (false)
	 */
	private static function checkArg($parameterList, $numMandatoryArg)
	{
		vglobal('Vtiger_Utils_Log', true);
		for ($i = 0; $i < $numMandatoryArg; $i++) {
			if (empty($parameterList[$i])) {
				$i++;
				vtlib\Utils::Log($i . ' function parameter is empty');
				return false;
			}
		}

		return true;
	}

	/**
	 * Funkcja służy do generowania kolejnych nazw pól
	 *
	 * @return string Nazwa pola
	 */
	private static function generateFieldName()
	{
		$db = PearDatabase::getInstance();
		$id = $db->getUniqueID("vtiger_field");

		return 'cf_' . $id;
	}
}
