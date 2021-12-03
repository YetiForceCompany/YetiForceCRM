<?php

/**
 * Automatic assignment save action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::lockTracking();
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('preSaveValidation');
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($request->getInteger('record'));
		}
		$recordModel->setDataFromRequest($request);
		return $recordModel;
	}

	/**
	 * PreSave validation function.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function preSaveValidation(App\Request $request)
	{
		$recordModel = $this->getRecordModelFromRequest($request);
		$response = new Vtiger_Response();
		$response->setResult($recordModel->validate());
		$response->emit();
	}

	/**
	 * Save function.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function save(App\Request $request)
	{
		try {
			$recordModel = $this->getRecordModelFromRequest($request);
			$recordModel->save();
			$result = ['success' => true, 'url' => $recordModel->getModule()->getDefaultUrl()];
		} catch (\App\Exceptions\AppException $e) {
			$result = ['success' => false, 'message' => $e->getDisplayMessage()];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function changes the type of a given role.
	 *
	 * @param \App\Request $request
	 */
	public function changeRoleType(App\Request $request)
	{
		$member = $request->getByType('param', 'Text');
		$recordId = $request->getInteger('record');
		if ($recordId) {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getCleanInstance();
		}
		$recordModel->changeRoleType($member);

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($recordModel->getId());
		$responceToEmit->emit();
	}

	/**
	 * Function removes given value from record.
	 *
	 * @param \App\Request $request
	 */
	public function deleteElement(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($recordId);
		$recordModel->deleteElement($request->getByType('name'), $request->getByType('value', 'Text'));

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($recordModel->getId());
		$responceToEmit->emit();
	}
}
