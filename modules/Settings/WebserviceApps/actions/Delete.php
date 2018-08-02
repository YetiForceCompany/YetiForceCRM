<?php

/**
 * Delete Application.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_Delete_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Main process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		Settings_WebserviceApps_Record_Model::getInstanceById($request->getInteger('id'))->delete();
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult(true);
		$responceToEmit->emit();
	}
}
