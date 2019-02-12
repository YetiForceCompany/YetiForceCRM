<?php
/**
 * Time Control Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
Vtiger_Loader::includeOnce('~~include/Webservices/Utils.php');

class OSSTimeControl_TimeControl_Handler
{
	/**
	 * EntityAfterUnLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterUnLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		$wfs = new VTWorkflowManager();
		$workflows = $wfs->getWorkflowsForModule($params['destinationModule'], VTWorkflowManager::$MANUAL);
		$recordModel = Vtiger_Record_Model::getInstanceById($params['destinationRecordId'], $params['destinationModule']);
		foreach ($workflows as &$workflow) {
			if ($workflow->evaluate($recordModel)) {
				$workflow->performTasks($recordModel);
			}
		}
	}

	/**
	 * EntityAfterDelete handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$wfs = new VTWorkflowManager();
		$workflows = $wfs->getWorkflowsForModule($eventHandler->getModuleName(), VTWorkflowManager::$MANUAL);
		foreach ($workflows as &$workflow) {
			if ($workflow->evaluate($recordModel)) {
				$workflow->performTasks($recordModel);
			}
		}
	}

	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$wfs = new VTWorkflowManager();
		$workflows = $wfs->getWorkflowsForModule($eventHandler->getModuleName(), VTWorkflowManager::$MANUAL);
		foreach ($workflows as &$workflow) {
			if ($workflow->evaluate($recordModel)) {
				$workflow->performTasks($recordModel);
			}
		}
	}

	/**
	 * EntityBeforeSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$start = strtotime($recordModel->get('date_start') . ' ' . $recordModel->get('time_start'));
		$end = strtotime($recordModel->get('due_date') . ' ' . $recordModel->get('time_end'));
		$recordModel->set('sum_time', round(abs(ceil((($end - $start) / 3600) * 100) / 100), 2));
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityChangeState(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$wfs = new VTWorkflowManager();
		$workflows = $wfs->getWorkflowsForModule($eventHandler->getModuleName(), VTWorkflowManager::$MANUAL);
		foreach ($workflows as &$workflow) {
			if ($workflow->evaluate($recordModel)) {
				$workflow->performTasks($recordModel);
			}
		}
	}
}
