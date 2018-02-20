<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'modules/com_vtiger_workflow/include.php';
require_once 'modules/com_vtiger_workflow/expression_engine/VTExpressionsManager.php';

/**
 * Class settings workflows module model.
 */
class Settings_Workflows_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Base table name.
	 *
	 * @var string
	 */
	public $baseTable = 'com_vtiger_workflows';

	/**
	 * Base table index column name.
	 *
	 * @var string
	 */
	public $baseIndex = 'workflow_id';

	/**
	 * Fields visible on list view array.
	 *
	 * @var array
	 */
	public $listFields = ['summary' => 'Summary', 'module_name' => 'Module', 'execution_condition' => 'Execution Condition', 'all_tasks' => 'LBL_ALL_TASKS', 'active_tasks' => 'LBL_ACTIVE_TASKS'];

	/**
	 * All fields list.
	 *
	 * @var array
	 */
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
		'nexttrigger_time',
	];

	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $name = 'Workflows';

	/**
	 * Workflow triggers list.
	 *
	 * @var array
	 */
	public static $triggerTypes = [
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
	 * Function to get the url for default view of the module.
	 *
	 * @return string - url
	 */
	public static function getDefaultUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module.
	 *
	 * @return string - url
	 */
	public static function getCreateViewUrl()
	{
		return "javascript:Settings_Workflows_List_Js.triggerCreate('index.php?module=Workflows&parent=Settings&view=Edit')";
	}

	/**
	 * Get create new record url.
	 *
	 * @return string
	 */
	public static function getCreateRecordUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&view=Edit';
	}

	/**
	 * Returns url for import view.
	 *
	 * @return string url
	 */
	public static function getImportViewUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&view=Import';
	}

	/**
	 * Get supported modules list.
	 *
	 * @return array
	 */
	public static function getSupportedModules()
	{
		$moduleModels = Vtiger_Module_Model::getAll([0, 2]);
		$supportedModuleModels = [];
		foreach ($moduleModels as $tabId => $moduleModel) {
			if ($moduleModel->isWorkflowSupported()) {
				$supportedModuleModels[$tabId] = $moduleModel;
			}
		}

		return $supportedModuleModels;
	}

	/**
	 * Get supported triggers list.
	 *
	 * @return array
	 */
	public static function getTriggerTypes()
	{
		return self::$triggerTypes;
	}

	/**
	 * Get expressions list.
	 *
	 * @return array
	 */
	public static function getExpressions()
	{
		$mem = new VTExpressionsManager();

		return $mem->expressionFunctions();
	}

	/**
	 * Get fields list.
	 *
	 * @return array
	 */
	public function getListFields()
	{
		if (!property_exists($this, 'listFieldModels')) {
			$fields = $this->listFields;
			$fieldObjects = [];
			$fieldsNoSort = ['module_name', 'execution_condition', 'all_tasks', 'active_tasks'];
			foreach ($fields as $fieldName => $fieldLabel) {
				if (in_array($fieldName, $fieldsNoSort)) {
					$fieldObjects[$fieldName] = new \App\Base(['name' => $fieldName, 'label' => $fieldLabel, 'sort' => false]);
				} else {
					$fieldObjects[$fieldName] = new \App\Base(['name' => $fieldName, 'label' => $fieldLabel]);
				}
			}
			$this->listFieldModels = $fieldObjects;
		}

		return $this->listFieldModels;
	}

	/**
	 * Delete all worklflows associated with module.
	 *
	 * @param vtlib\ModuleBasic $moduleInstance
	 */
	public static function deleteForModule(vtlib\ModuleBasic $moduleInstance)
	{
		\App\Db::getInstance()->createCommand()->delete('com_vtiger_workflows', ['module_name' => $moduleInstance->name])->execute();
	}

	/**
	 * Imports workflow template xml file.
	 *
	 * @param array $data
	 *
	 * @return int workflow id
	 */
	public function importWorkflow(array $data)
	{
		$db = App\Db::getInstance();
		$dbCommand = App\Db::getInstance()->createCommand();
		$dbCommand->insert($this->getBaseTable(), $data['fields'])->execute();
		$workflowId = $db->getLastInsertID('com_vtiger_workflows_workflow_id_seq');
		$messages = ['id' => $workflowId];
		if ($data['workflow_methods']) {
			foreach ($data['workflow_methods'] as $method) {
				$this->importTaskMethod($method, $messages);
			}
		}
		if ($data['workflow_tasks']) {
			foreach ($data['workflow_tasks'] as $task) {
				$dbCommand->insert('com_vtiger_workflowtasks', ['workflow_id' => $workflowId, 'summary' => $task['summary']])->execute();
				$taskId = $db->getLastInsertID('com_vtiger_workflowtasks_task_id_seq');
				include_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.php';
				$taskObject = unserialize($task['task']);
				$taskObject->workflowId = (int) $workflowId;
				$taskObject->id = (int) $taskId;
				$dbCommand->update('com_vtiger_workflowtasks', ['task' => serialize($taskObject)], ['task_id' => $taskId])->execute();
				$dbCommand->update('com_vtiger_workflowtasks_seq', ['id' => $taskId])->execute();
			}
		}

		return $messages;
	}

	/**
	 * Returns infor for exporting of task method.
	 *
	 * @param int $methodName name of method
	 *
	 * @return array task method data
	 */
	public static function exportTaskMethod($methodName)
	{
		$method = (new \App\Db\Query())->select(['workflowtasks_entitymethod_id', 'module_name', 'method_name', 'function_path', 'function_name'])->from('com_vtiger_workflowtasks_entitymethod')->where(['method_name' => $methodName])->one();
		$method['script_content'] = base64_encode(file_get_contents($method['function_path']));

		return $method;
	}

	/**
	 * Function that creates task method.
	 *
	 * @param array $method   array containing method data
	 * @param array $messages array containing returned error messages
	 */
	public function importTaskMethod(array &$method, array &$messages)
	{
		if (!file_exists($method['function_path'])) {
			$scriptData = base64_decode($method['script_content']);
			if (file_put_contents($method['function_path'], $scriptData) === false) {
				$messages['error'][] = \App\Language::translate('LBL_FAILED_TO_SAVE_SCRIPT', $this->getName(true), basename($method['function_path']), $method['function_path']);
			}
		} else {
			require_once $method['function_path'];
			if (!function_exists($method['function_name'])) {
				$messages['error'][] = \App\Language::translate('LBL_SCRIPT_EXISTS_FUNCTION_NOT', $this->getName(true), $method['function_name'], $method['function_path']);
			}
		}
		$num = (new \App\Db\Query())->from('com_vtiger_workflowtasks_entitymethod')->where(['module_name' => $method['module_name'], 'method_name' => $method['method_name'], 'function_path' => $method['function_path'], 'function_name' => $method['function_name']])->count();
		if (!$num) {
			require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.php';
			$emm = new VTEntityMethodManager();
			$emm->addEntityMethod($method['module_name'], $method['method_name'], $method['function_path'], $method['function_name']);
		}
	}
}
