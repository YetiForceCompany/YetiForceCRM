<?php
/**
 * WorkflowTrigger model class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
 /**
  * Vtiger_WorkflowTrigger_Model class.
  */
 class Vtiger_WorkflowTrigger_Model
 {
 	/**
 	 * Function executes workflow tasks.
 	 *
 	 * @param string $moduleName
 	 * @param int    $record
 	 * @param int    $userId
 	 * @param array  $actions
 	 */
 	public static function execute(string $moduleName, int $record, $userId, array $actions = [])
 	{
 		\Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
 		$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
 		if ($userId) {
 			$recordModel->executeUser = $userId;
 		}
 		$wfs = new VTWorkflowManager();
 		foreach ($actions as $id => $tasks) {
 			$workflow = $wfs->retrieve($id);
 			if ($workflow->evaluate($recordModel)) {
 				if (!$workflow->params || !($params = \App\Json::decode($workflow->params)) || empty($params['showTasks']) || empty($params['enableTasks'])) {
 					$tasks = null;
 				}
 				$workflow->performTasks($recordModel, $tasks);
 			}
 		}
 	}

 	/**
 	 * Gets workflow action tree.
 	 *
 	 * @param string $moduleName
 	 * @param int    $recordId
 	 *
 	 * @return array
 	 */
 	public static function getTreeWorkflows(string $moduleName, int $recordId): array
 	{
 		\Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
 		$tree = [];
 		$workflowModuleName = 'Settings:Workflows';
 		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
 		$workflows = (new VTWorkflowManager())->getWorkflowsForModule($moduleName, VTWorkflowManager::$TRIGGER);
 		$index = max(array_column($workflows, 'id'));
 		foreach ($workflows as $workflow) {
 			if (!$workflow->evaluate($recordModel)) {
 				continue;
 			}
 			$tree[] = [
 				'id' => $workflow->id,
 				'type' => 'category',
 				'attr' => 'record',
 				'record_id' => $workflow->id,
 				'parent' => '#',
 				'text' => '&nbsp;' . \App\Language::translate($workflow->description, $workflowModuleName),
 				'state' => ['selected' => false, 'disabled' => false, 'loaded' => true, 'opened' => false],
 				'category' => ['checked' => false]
 			];
 			$params = $workflow->params ? \App\Json::decode($workflow->params) : [];
 			if (empty($params['showTasks'])) {
 				continue;
 			}
 			$workflowRecord = Settings_Workflows_Record_Model::getInstanceFromWorkflowObject($workflow);
 			foreach ($workflowRecord->getTasks() as $task) {
 				$tree[] = [
 					'id' => ++$index,
 					'type' => 'category',
 					'attr' => 'task',
 					'record_id' => $task->getId(),
 					'parent' => $workflowRecord->getId(),
 					'text' => '&nbsp;' . \App\Language::translate($task->getTaskObject()->summary, $workflowModuleName),
 					'state' => ['selected' => false, 'disabled' => empty($params['enableTasks'])],
 					'category' => ['checked' => false]
 				];
 			}
 		}
 		return $tree;
 	}
 }
