<?php

/**
 * Save Application.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Main process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		if ($request->isEmpty('id')) {
			$recordModel = Settings_WebserviceApps_Record_Model::getCleanInstance();
			$recordModel->set('type', $request->getByType('type'));
		} else {
			$recordModel = Settings_WebserviceApps_Record_Model::getInstanceById($request->getInteger('id'));
		}
		$recordModel->set('status', $request->getBoolean('status'));
		$recordModel->set('name', $request->getByType('name', 'Text'));
		$recordModel->set('acceptable_url', $request->getByType('url', 'Text'));
		$recordModel->set('pass', $request->getRaw('pass'));
		$recordModel->set('accounts_id', $request->isEmpty('accounts') ? 0 : $request->getInteger('accounts'));
		$recordModel->save();
		$responce = new Vtiger_Response();
		$responce->setResult(true);
		$responce->emit();
	}
}
