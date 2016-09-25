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
	public static $allFields = [
		'module_name',
		'summary',
		'conditions',
		'execution_condition',
		'filtersavedinnew',
		'defaultworkflow',
		'type',
		'schtypeid',
		'schdayofmonth',
		'schdayofweek',
		'schannualdates',
		'schtime',
		'nexttrigger_time'
	];
	var $name = 'Workflows';
	static $metaVariables = [
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
	];
	static $triggerTypes = [
		1 => 'ON_FIRST_SAVE',
		4 => 'ON_MODIFY',
		3 => 'ON_EVERY_SAVE',
		2 => 'ONCE',
		5 => 'ON_DELETE',
		6 => 'ON_SCHEDULE',
		7 => 'MANUAL',
		8 => 'TRIGGER',
		9 => 'BLOCK_EDIT',
		//10 => 'ON_RELATED',
	];

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

	/**
	 * Returns url for import view
	 * @return string url
	 */
	public static function getImportViewUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&view=Import';
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
	 * @param vtlib\Module Instnace of module to use
	 */
	static function deleteForModule($moduleInstance)
	{
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE com_vtiger_workflows,com_vtiger_workflowtasks FROM `com_vtiger_workflows` 
			LEFT JOIN `com_vtiger_workflowtasks` ON com_vtiger_workflowtasks.workflow_id = com_vtiger_workflows.workflow_id
			WHERE `module_name` =?', [$moduleInstance->name]);
	}

	/**
	 * Imports workflow template xml file
	 * @param array $data
	 * @return int workflow id
	 */
	public function importWorkflow(array $data)
	{
		$db = PearDatabase::getInstance();

		$db->insert($this->getBaseTable(), $data['fields']);

		$workflowId = $db->getLastInsertID();
		$db->update($this->getBaseTable() . '_seq', ['id' => $workflowId]);

		$messages = ['id' => $workflowId];
		if ($data['workflow_methods']) {
			foreach ($data['workflow_methods'] as $method) {
				$this->importTaskMethod($method, $messages);
			}
		}

		if ($data['workflow_tasks']) {
			foreach ($data['workflow_tasks'] as $task) {
				$db->insert('com_vtiger_workflowtasks', ['workflow_id' => $workflowId, 'summary' => $task['summary']]);
				$taskId = $db->getLastInsertID();

				include_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
				$taskObject = unserialize($task['task']);
				$taskObject->workflowId = intval($workflowId);
				$taskObject->id = intval($taskId);

				$db->update('com_vtiger_workflowtasks', ['task' => serialize($taskObject)], 'task_id = ?', [$taskId]);
				$db->update('com_vtiger_workflowtasks_seq', ['id' => $taskId]);
			}
		}

		return $messages;
	}

	/**
	 * Returns infor for exporting of task method
	 * @param int $methodName name of method
	 * @return array task method data
	 */
	public static function exportTaskMethod($methodName)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT workflowtasks_entitymethod_id, module_name, method_name, function_path, function_name FROM com_vtiger_workflowtasks_entitymethod WHERE method_name = ?;';
		$result = $db->pquery($query, [$methodName]);
		$method = $db->getRow($result);

		$method['script_content'] = base64_encode(file_get_contents($method['function_path']));

		return $method;
	}

	/**
	 * Function that creates task method
	 * @param array $method array containing method data
	 * @param array $messages array containing returned error messages
	 */
	public function importTaskMethod(array &$method, array &$messages)
	{
		$db = PearDatabase::getInstance();

		if (!file_exists($method['function_path'])) {
			$scriptData = base64_decode($method['script_content']);
			if (file_put_contents($method['function_path'], $scriptData) === false) {
				$messages['error'][] = vtranslate('LBL_FAILED_TO_SAVE_SCRIPT', $this->getName(true), basename($method['function_path']), $method['function_path']);
			}
		} else {
			require_once $method['function_path'];
			if (!function_exists($method['function_name'])) {
				$messages['error'][] = vtranslate('LBL_SCRIPT_EXISTS_FUNCTION_NOT', $this->getName(true), $method['function_name'], $method['function_path']);
			}
		}

		$query = 'SELECT COUNT(1) AS num FROM com_vtiger_workflowtasks_entitymethod WHERE module_name = ? && method_name = ? && function_path = ? && function_name = ?;';
		$params = [$method['module_name'], $method['method_name'], $method['function_path'], $method['function_name']];
		$result = $db->pquery($query, $params);
		$num = $db->getSingleValue($result);

		if ($num == 0) {
			require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
			$emm = new VTEntityMethodManager($db);
			$emm->addEntityMethod($method['module_name'], $method['method_name'], $method['function_path'], $method['function_name']);
		}
	}
}
