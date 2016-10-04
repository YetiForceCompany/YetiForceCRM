<?php

/**
 * ChangesReviewedOn Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ModTracker_ChangesReviewedOn_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$sourceModule = $request->get('sourceModule');
		if (!empty($record)) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record);
			if (!$recordModel->getModule()->isTrackingEnabled()) {
				throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
		} elseif (!empty($sourceModule)) {
			$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
			if (!$moduleModel || $moduleModel->isTrackingEnabled()) {
				throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
		} else {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getUnreviewed');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$record = $request->get('record');
		$result = ModTracker_Record_Model::setLastReviewed($record);
		ModTracker_Record_Model::unsetReviewed($record, false, $result);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function getUnreviewed(Vtiger_Request $request)
	{
		$records = $request->get('recordsId');
		$result = ModTracker_Record_Model::getUnreviewed($records, false, true);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request)
	{
		return $request->validateWriteAccess();
	}
}
