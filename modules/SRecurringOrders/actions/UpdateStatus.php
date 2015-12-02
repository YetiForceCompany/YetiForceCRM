<?php

/**
 * UpdateStatus SRecurringOrders Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SRecurringOrders_UpdateStatus_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');

		if (!Users_Privileges_Model::isPermitted($moduleName, 'Save', $record)) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$state = $request->get('state');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->set('id', $recordId);
		$recordModel->set('srecurringorders_status', $state);
		$recordModel->set('mode', 'edit');
		if (in_array($state, ['PLL_UNREALIZED', 'PLL_REALIZED'])) {
			$currentTime = date('Y-m-d H:i:s');
			$responseTime = strtotime($currentTime) - strtotime($recordModel->get('createdtime'));
			$recordModel->set('response_time', $responseTime / 60 / 60);
			$recordModel->set('closedtime', $currentTime);
		}
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request)
	{
		return $request->validateWriteAccess();
	}
}
