<?php

/**
 * ChangesReviewedOn Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ModTracker_ChangesReviewedOn_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		$record = $request->get('record');
		if (!empty($record)) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record);
			if (!$recordModel->getModule()->isTrackingEnabled()) {
				throw new NoPermittedToRecordException('LBL_PERMISSION_DENIED');
			}
		} else {
			throw new NoPermittedToRecordException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$result = ModTracker_Record_Model::setLastReviewed($record);
		ModTracker_Record_Model::unsetReviewed($record);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request)
	{
		return $request->validateWriteAccess();
	}
}
