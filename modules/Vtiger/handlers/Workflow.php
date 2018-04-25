<?php

Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/VTWorkflowManager.php');

/**
 * Workflow handler.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Workflow_Handler
{
	private $workflows;

	/**
	 * EntityChangeState handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityChangeState(App\EventHandler $eventHandler)
	{
		$this->entityAfterSave($eventHandler);
	}

	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$moduleName = $eventHandler->getModuleName();
		$recordId = $recordModel->getId();
		$isNew = $recordModel->isNew();
		if (!isset($this->workflows[$moduleName])) {
			$wfs = new VTWorkflowManager();
			$this->workflows[$moduleName] = $wfs->getWorkflowsForModule($moduleName);
		}
		foreach ($this->workflows[$moduleName] as &$workflow) {
			switch ($workflow->executionCondition) {
				case VTWorkflowManager::$ON_FIRST_SAVE:
					if ($isNew) {
						$doEvaluate = true;
					} else {
						$doEvaluate = false;
					}
					break;
				case VTWorkflowManager::$ONCE:
					if ($workflow->isCompletedForRecord($recordId)) {
						$doEvaluate = false;
					} else {
						$doEvaluate = true;
					}
					break;
				case VTWorkflowManager::$ON_EVERY_SAVE:
					$doEvaluate = true;
					break;
				case VTWorkflowManager::$ON_MODIFY:
					$doEvaluate = !$isNew && !empty($recordModel->getPreviousValue());
					break;
				case VTWorkflowManager::$MANUAL:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$ON_SCHEDULE:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$ON_DELETE:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$TRIGGER:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$BLOCK_EDIT:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$ON_RELATED:
					$doEvaluate = false;
					break;
				default:
					throw new Exception('Should never come here! Execution Condition:' . $workflow->executionCondition);
			}
			if ($doEvaluate && $workflow->evaluate($recordModel, $recordId)) {
				if (VTWorkflowManager::$ONCE == $workflow->executionCondition) {
					$workflow->markAsCompletedForRecord($recordId);
				}
				$workflow->performTasks($recordModel);
			}
		}
	}
}
