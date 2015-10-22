<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/expression_engine/VTExpressionsManager.inc';

class Settings_Workflows_Module_Model extends Settings_Vtiger_Module_Model
{

	var $baseTable = 'com_vtiger_workflows';
	var $baseIndex = 'workflow_id';
	var $listFields = array('summary' => 'Summary', 'module_name' => 'Module', 'execution_condition' => 'Execution Condition', 'all_tasks' => 'LBL_ALL_TASKS', 'active_tasks' => 'LBL_ACTIVE_TASKS');
	var $name = 'Workflows';
	static $metaVariables = array(
		'Current Date' => '(general : (__VtigerMeta__) date) ($_DATE_FORMAT_)',
		'Current Time' => '(general : (__VtigerMeta__) time)',
		'System Timezone' => '(general : (__VtigerMeta__) dbtimezone)',
		'User Timezone' => '(general : (__VtigerMeta__) usertimezone)',
		'CRM Detail View URL' => '(general : (__VtigerMeta__) crmdetailviewurl)',
		'Portal Detail View URL' => '(general : (__VtigerMeta__) portaldetailviewurl)',
		'Site Url' => '(general : (__VtigerMeta__) siteurl)',
		'Portal Url' => '(general : (__VtigerMeta__) portalurl)',
		'Record Id' => '(general : (__VtigerMeta__) recordId)',
		'LBL_HELPDESK_SUPPORT_NAME' => '(general : (__VtigerMeta__) supportName)',
		'LBL_HELPDESK_SUPPORT_EMAILID' => '(general : (__VtigerMeta__) supportEmailid)',
	);
	static $triggerTypes = array(
		1 => 'ON_FIRST_SAVE',
		2 => 'ONCE',
		3 => 'ON_EVERY_SAVE',
		4 => 'ON_MODIFY',
		5 => 'ON_DELETE',
		6 => 'ON_SCHEDULE',
		7 => 'MANUAL',
		8 => 'TRIGGER',
		9 => 'BLOCK_EDIT',
		//10 => 'ON_RELATED',
	);

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public static function getDefaultUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module
	 * @return <string> - url
	 */
	public static function getCreateViewUrl()
	{
		return "javascript:Settings_Workflows_List_Js.triggerCreate('index.php?module=Workflows&parent=Settings&view=Edit')";
	}

	public static function getCreateRecordUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&view=Edit';
	}

	public static function getSupportedModules()
	{
		$moduleModels = Vtiger_Module_Model::getAll(array(0, 2));
		$supportedModuleModels = array();
		foreach ($moduleModels as $tabId => $moduleModel) {
			if ($moduleModel->isWorkflowSupported()) {
				$supportedModuleModels[$tabId] = $moduleModel;
			}
		}
		return $supportedModuleModels;
	}

	public static function getTriggerTypes()
	{
		return self::$triggerTypes;
	}

	public static function getExpressions()
	{
		$db = PearDatabase::getInstance();

		$mem = new VTExpressionsManager($db);
		return $mem->expressionFunctions();
	}

	public static function getMetaVariables()
	{
		return self::$metaVariables;
	}

	public function getListFields()
	{
		if (!$this->listFieldModels) {
			$fields = $this->listFields;
			$fieldObjects = array();
			$fieldsNoSort = array('module_name', 'execution_condition', 'all_tasks', 'active_tasks');
			foreach ($fields as $fieldName => $fieldLabel) {
				if (in_array($fieldName, $fieldsNoSort)) {
					$fieldObjects[$fieldName] = new Vtiger_Base_Model(array('name' => $fieldName, 'label' => $fieldLabel, 'sort' => false));
				} else {
					$fieldObjects[$fieldName] = new Vtiger_Base_Model(array('name' => $fieldName, 'label' => $fieldLabel));
				}
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}
	
	/**
	 * Delete all worklflows associated with module
	 * @param Vtiger_Module Instnace of module to use
	 */
	static function deleteForModule($moduleInstance)
	{
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE com_vtiger_workflows,com_vtiger_workflowtasks FROM `com_vtiger_workflows` 
			LEFT JOIN `com_vtiger_workflowtasks` ON com_vtiger_workflowtasks.workflow_id = com_vtiger_workflows.workflow_id
			WHERE `module_name` =?', [$moduleInstance->name]);
	}
}
