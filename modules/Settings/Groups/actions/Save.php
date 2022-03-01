<?php
/**
 * The basic class to save.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Save actions for group module.
 */
class Settings_Groups_Save_Action extends Settings_Vtiger_Save_Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('preSaveValidation');
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->save($request);
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
		if (!$request->isEmpty('record', true)) {
			$recordModel = Settings_Groups_Record_Model::getInstance($request->getInteger('record'));
		} else {
			$recordModel = Settings_Groups_Record_Model::getCleanInstance();
		}
		$recordModel->setDataFromRequest($request);
		$response = new Vtiger_Response();
		if ($errorLabel = $recordModel->validate()) {
			$response->setResult(['success' => true, 'message' => \App\Language::translate($errorLabel, $request->getModule(false))]);
		} else {
			$response->setResult(['success' => false]);
		}
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
		$recordId = $request->isEmpty('record', true) ? null : $request->getInteger('record');
		if ($recordId) {
			$recordModel = Settings_Groups_Record_Model::getInstance($recordId);
		} else {
			$recordModel = Settings_Groups_Record_Model::getCleanInstance();
		}
		$recordModel->setDataFromRequest($request);
		$recordModel->save();
		Settings_Vtiger_Tracker_Model::addDetail($recordModel->getPreviousValue(), $recordId ? array_intersect_key($recordModel->getData(), $recordModel->getPreviousValue()) : $recordModel->getData());

		$redirectUrl = $recordModel->getDetailViewUrl();
		header("location: {$redirectUrl}");
	}
}
