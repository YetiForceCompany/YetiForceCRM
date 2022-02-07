<?php

/**
 * Save Application.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_WebserviceApps_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Main process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		if ($request->isEmpty('id')) {
			$recordModel = Settings_WebserviceApps_Record_Model::getCleanInstance();
			$recordModel->set('type', $request->getByType('type'));
		} else {
			$recordModel = Settings_WebserviceApps_Record_Model::getInstanceById($request->getInteger('id'));
		}
		$recordModel->set('status', $request->getBoolean('status'));
		$recordModel->set('name', $request->getByType('name', 'Text'));
		$recordModel->set('url', $request->getByType('url', 'Url'));
		$recordModel->set('ips', $request->getByType('ips', 'Text'));
		$recordModel->set('pass', $request->getRaw('pass'));
		$result = true;
		try {
			$recordModel->save();
		} catch (\App\Exceptions\IllegalValue $e) {
			$result = ['error' => $e->getDisplayMessage()];
		}
		$responce = new Vtiger_Response();
		$responce->setResult($result);
		$responce->emit();
	}
}
