<?php
/**
 * Magento save action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Magento save action class.
 */
class Settings_Magento_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('reload');
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
		if ($request->isEmpty('record')) {
			$recordModel = Settings_Magento_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_Magento_Record_Model::getInstanceById($request->getInteger('record'));
		}
		try {
			$recordModel->setDataFromRequest($request);
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
	 * Restart synchronization function.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function reload(App\Request $request)
	{
		try {
			\App\Integrations\Magento\Config::reload($request->getInteger('record'));
			$result = ['success' => true, 'message' => \App\Language::translate('LBL_RELOAD_MESSAGE', $request->getModule(false))];
		} catch (\App\Exceptions\AppException $e) {
			$result = ['success' => false, 'message' => $e->getDisplayMessage()];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
