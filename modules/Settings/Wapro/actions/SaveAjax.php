<?php

/**
 * Settings WAPRO ERP save ajax action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings WAPRO ERP save ajax action class.
 */
class Settings_Wapro_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('saveWidget');
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		if ($request->getMode()) {
			$this->updateSynchronizer($request);
			return;
		}
		if ($request->isEmpty('record', true)) {
			$recordModel = Settings_Wapro_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_Wapro_Record_Model::getInstanceById($request->getInteger('record'));
		}
		$recordModel->setDataFromRequest($request);
		$response = new Vtiger_Response();
		$verify = App\Integrations\Wapro::verifyDatabaseAccess($recordModel->get('server'), $recordModel->get('database'), $recordModel->get('username'), $recordModel->get('password'), $recordModel->get('port'));
		if ($verify['status']) {
			$recordModel->save();
			$response->setResult($recordModel->get('status') ? $recordModel->getId() : 0);
		} else {
			$response->setError($verify['code'] ?: 500, nl2br($verify['message']));
		}
		$response->emit();
	}

	/**
	 * Update synchronizer list.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function updateSynchronizer(App\Request $request): void
	{
		\App\Db::getInstance('admin')->createCommand()
			->update(\App\Integrations\Wapro::TABLE_NAME, ['synchronizer' => \App\Json::encode($request->getArray('synchronizer', 'Standard'))], ['id' => $request->getInteger('id')])
			->execute();
		\App\Cache::delete('App\Integrations\Wapro::getById', $request->getInteger('id'));

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
