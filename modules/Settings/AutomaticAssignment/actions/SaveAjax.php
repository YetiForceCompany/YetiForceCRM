<?php

/**
 * Automatic assignment save action model class.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('preSaveValidation');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if (!\App\YetiForce\Shop::check('YetiForceAutoAssignment')) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
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
			$recordId = $recordModel->getId();
			$recordModel->save();
			\Settings_Vtiger_Tracker_Model::addDetail($recordModel->getPreviousValue(), $recordId ? array_intersect_key($recordModel->getData(), $recordModel->getPreviousValue()) : $recordModel->getData());
			$result = ['success' => true, 'url' => $recordModel->getModule()->getDefaultUrl()];
		} catch (\App\Exceptions\AppException $e) {
			$result = ['success' => false, 'message' => $e->getDisplayMessage()];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
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
}
