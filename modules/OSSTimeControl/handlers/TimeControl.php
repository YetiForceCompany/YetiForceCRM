<?php
/**
 * Time Control Handler Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * EntityAfterDelete handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		$this->entityAfterSave($eventHandler);
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
		$recordModel->set('sum_time', round(abs(ceil((($end - $start) / 60) * 100) / 100), 0));
	}

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
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$start = $recordModel->get('date_start') . ' ' . $recordModel->get('time_start');
		$end = $recordModel->get('due_date') . ' ' . $recordModel->get('time_end');
		$response = [
			'result' => true,
		];
		if (\App\Fields\DateTime::getDiff($start, $end, 'minutes') > 24 * 60) {
			$response = [
				'result' => false,
				'message' => App\Language::translate('LBL_DATE_NOT_SHOULD_BE_GREATER_THAN_24H', $recordModel->getModuleName())
			];
		}
		return $response;
	}
}
