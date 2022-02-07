<?php

Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/VTWorkflowManager.php');

/**
 * Workflow handler.
 *
 * @package		Handler
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		if ('Trash' === $eventHandler->getRecordModel()->get('deleted')) {
			$this->performTasks($eventHandler, [
				VTWorkflowManager::$ON_DELETE
			]);
		} else {
			$this->performTasks($eventHandler, [
				VTWorkflowManager::$ON_EVERY_SAVE
			]);
		}
	}

	/**
	 * EntityAfterDelete handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		$this->performTasks($eventHandler, [
			VTWorkflowManager::$ON_DELETE
		]);
	}

	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$this->performTasks($eventHandler, [
			VTWorkflowManager::$ON_FIRST_SAVE,
			VTWorkflowManager::$ONCE,
			VTWorkflowManager::$ON_EVERY_SAVE,
			VTWorkflowManager::$ON_MODIFY
		]);
	}

	/**
	 * UserAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function userAfterSave(App\EventHandler $eventHandler)
	{
		$this->entityAfterSave($eventHandler);
	}

	/**
	 * Perform workflow tasks.
	 *
	 * @param \App\EventHandler $eventHandler
	 * @param int[]             $condition
	 *
	 * @throws \Exception
	 */
	private function performTasks(App\EventHandler $eventHandler, $condition = [])
	{
		$recordModel = $eventHandler->getRecordModel();
		$moduleName = $eventHandler->getModuleName();
		if (!isset($this->workflows[$moduleName])) {
			$wfs = new VTWorkflowManager();
			$this->workflows[$moduleName] = $wfs->getWorkflowsForModule($moduleName);
		}
		foreach ($this->workflows[$moduleName] as &$workflow) {
			if ($condition && !\in_array($workflow->executionCondition, $condition)) {
				continue;
			}
			switch ($workflow->executionCondition) {
				case VTWorkflowManager::$ON_FIRST_SAVE:
					if ($recordModel->isNew()) {
						$doEvaluate = true;
					} else {
						$doEvaluate = false;
					}
					break;
				case VTWorkflowManager::$ONCE:
					if ($workflow->isCompletedForRecord($recordModel->getId())) {
						$doEvaluate = false;
					} else {
						$doEvaluate = true;
					}
					break;
				case VTWorkflowManager::$ON_EVERY_SAVE:
					$doEvaluate = true;
					break;
				case VTWorkflowManager::$ON_MODIFY:
					$doEvaluate = !$recordModel->isNew() && !empty($recordModel->getPreviousValue());
					break;
				case VTWorkflowManager::$MANUAL:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$ON_SCHEDULE:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$ON_DELETE:
					$doEvaluate = true;
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
					throw new \App\Exceptions\AppException('Should never come here! Execution Condition:' . $workflow->executionCondition);
			}
			if ($doEvaluate && $workflow->evaluate($recordModel, $recordModel->getId())) {
				if (VTWorkflowManager::$ONCE == $workflow->executionCondition) {
					$workflow->markAsCompletedForRecord($recordModel->getId());
				}
				$workflow->performTasks($recordModel);
			}
		}
	}
}
