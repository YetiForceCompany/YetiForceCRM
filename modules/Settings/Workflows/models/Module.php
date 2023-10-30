<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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

	/** {@inheritdoc} */
	public function getListFields(): array
	{
		if (!property_exists($this, 'listFieldModels')) {
			$fields = $this->listFields;
			$fieldObjects = [];
			$fieldsNoSort = ['module_name', 'execution_condition', 'all_tasks', 'active_tasks'];
			foreach ($fields as $fieldName => $fieldLabel) {
				if (\in_array($fieldName, $fieldsNoSort)) {
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
		if (!empty($data['workflow_methods'])) {
			foreach ($data['workflow_methods'] as $method) {
				$this->importTaskMethod($method, $messages);
			}
		}
		if ($data['workflow_tasks']) {
			foreach ($data['workflow_tasks'] as $task) {
				include_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.php';
				include_once 'modules/com_vtiger_workflow/tasks/VTEmailTemplateTask.php';
				$taskManager = new VTTaskManager();
				$taskObject = $taskManager->unserializeTask(base64_decode($task['task']));
				if (!empty($taskObject)) {
					$dbCommand->insert('com_vtiger_workflowtasks', ['workflow_id' => $workflowId, 'summary' => $task['summary']])->execute();
					$taskId = $db->getLastInsertID('com_vtiger_workflowtasks_task_id_seq');
					$taskObject->workflowId = (int) $workflowId;
					$taskObject->id = (int) $taskId;
					$dbCommand->update('com_vtiger_workflowtasks', ['task' => serialize($taskObject)], ['task_id' => $taskId])->execute();
				}
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
		$scriptData = base64_decode($method['script_content']);
		$functionPath = $method['function_path'];
		if (!$this->checkPathForImportMethod($functionPath)) {
			throw new \App\Exceptions\Security('ERR_NOT_ALLOWED_VALUE||function_path', 406);
		}
		if (!\preg_match('/^<\\?php/', $scriptData)) {
			throw new \App\Exceptions\Security('ERR_NOT_ALLOWED_VALUE||script_content', 406);
		}
		if (!file_exists($functionPath)) {
			$workflowsExists = file_exists(\dirname($functionPath));
			if ($workflowsExists && is_file(\dirname($functionPath))) {
				throw new \App\Exceptions\Security('ERR_DIRECTORY_CANNOT_BE_CREATED||function_path', 406);
			}
			if (!$workflowsExists) {
				mkdir(\dirname($functionPath));
			}
			if (false === file_put_contents($functionPath, $scriptData)) {
				throw new \App\Exceptions\IllegalValue('ERR_FAILED_TO_SAVE_SCRIPT||function_path', 406);
			}
		} else {
			require_once $functionPath;
			if (!method_exists($method['function_name'], $method['method_name'])) {
				throw new \App\Exceptions\IllegalValue('ERR_SCRIPT_EXISTS_FUNCTION_NOT||function_path', 406);
			}
		}
		$num = (new \App\Db\Query())
			->from('com_vtiger_workflowtasks_entitymethod')
			->where([
				'module_name' => $method['module_name'],
				'method_name' => $method['method_name'],
				'function_path' => $functionPath,
				'function_name' => $method['function_name'],
			])->exists();
		if (!$num) {
			require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.php';
			$emm = new VTEntityMethodManager();
			$emm->addEntityMethod($method['module_name'], $method['method_name'], $functionPath, $method['function_name']);
		}
	}

	/**
	 * Check the path for importing the method.
	 *
	 * @param string $path
	 *
	 * @return bool Returns true if success
	 */
	public function checkPathForImportMethod(string $path): bool
	{
		if ($returnVal = \preg_match('/^modules[\\\\|\\/]([A-Z][a-z,A-Z]+)[\\\\|\\/]workflows[\\\\|\\/][A-Z][a-z,A-Z]+\\.php$/', $path, $match)) {
			//Check if the module exists
			$returnVal = false !== \vtlib\Module::getInstance($match[1]);
		}
		return $returnVal;
	}

	/**
	 * Update actions sequence.
	 *
	 * @param int    $wfIdToMove
	 * @param int    $workflowBeforeId
	 * @param string $moduleName
	 *
	 * @return void
	 */
	public static function updateActionsSequence(int $wfIdToMove, int $workflowBeforeId, string $moduleName): void
	{
		if ($workflowBeforeId !== $wfIdToMove) {
			$db = \App\Db::getInstance();
			$caseSequence = 'CASE';
			$sequence = 0;

			$moduleWorkflows = array_keys(self::getWorkflowActionsForModule($moduleName));
			foreach ($moduleWorkflows as $wfId) {
				if ($wfIdToMove === $wfId) {
					continue;
				}
				if ($wfId === $workflowBeforeId) {
					$caseSequence .= " WHEN workflow_id = {$db->quoteValue($wfIdToMove)} THEN {$db->quoteValue($sequence)}";
					++$sequence;
				}
				$caseSequence .= " WHEN workflow_id = {$db->quoteValue($wfId)} THEN {$db->quoteValue($sequence)}";
				++$sequence;
			}
			$caseSequence .= ' END';

			$db->createCommand()->update('com_vtiger_workflows', [
				'sequence' => new yii\db\Expression($caseSequence),
			], ['workflow_id' => $moduleWorkflows])->execute();
		}
	}

	/**
	 * Update tasks sequence.
	 *
	 * @param array $tasks
	 *
	 * @return void
	 */
	public static function updateTasksSequence(array $tasks): void
	{
		$createCommand = \App\Db::getInstance()->createCommand();
		foreach ($tasks as $sequence => $id) {
			$createCommand->update('com_vtiger_workflowtasks', ['sequence' => $sequence], ['task_id' => $id])->execute();
		}
	}

	/**
	 * Get workflow actions for module.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getWorkflowActionsForModule(string $moduleName): array
	{
		return (new \App\Db\Query())->select(['workflow_id', 'summary'])
			->from('com_vtiger_workflows')
			->where(['module_name' => $moduleName])
			->orderBy(['sequence' => SORT_ASC])
			->createCommand()->queryAllByGroup(1);
	}
}
