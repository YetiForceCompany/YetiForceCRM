<?php
/**
 * Settings Comarch save action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings Comarch save action class.
 */
class Settings_Comarch_SaveAjax_Action extends Settings_Vtiger_Save_Action
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
	public function save(App\Request $request): void
	{
		if ($request->isEmpty('record')) {
			$recordModel = Settings_Comarch_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_Comarch_Record_Model::getInstanceById($request->getInteger('record'));
		}
		$recordModel->setDataFromRequest($request);
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['url' => 'index.php?parent=Settings&module=Comarch&view=EditConfigModal&record=' . $recordModel->getId()]);
		$response->emit();
	}

	/**
	 * Restart synchronization function.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function reload(App\Request $request): void
	{
		try {
			\App\Integrations\Comarch\Config::reload($request->getInteger('record'));
			$result = ['success' => true, 'message' => \App\Language::translate('LBL_RELOAD_MESSAGE', $request->getModule(false))];
		} catch (\App\Exceptions\AppException $e) {
			$result = ['success' => false, 'message' => $e->getDisplayMessage()];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
