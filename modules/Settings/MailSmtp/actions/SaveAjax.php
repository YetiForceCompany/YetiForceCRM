<?php

/**
 * MailSmtp SaveAjax action model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MailSmtp_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('preSaveValidation');
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Settings_MailSmtp_Record_Model
	 */
	protected function getRecordModelFromRequest(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			$recordModel = Settings_MailSmtp_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_MailSmtp_Record_Model::getInstanceById($request->getInteger('record'));
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
}
