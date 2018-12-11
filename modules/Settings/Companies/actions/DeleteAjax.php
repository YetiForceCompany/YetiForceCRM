<?php

/**
 * Companies delete action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Companies_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		Settings_Companies_Record_Model::getInstance($request->getInteger('record'))->delete();
		$response = new Vtiger_Response();
		$response->setResult(Settings_Vtiger_Module_Model::getInstance($request->getModule(false))->getDefaultUrl());
		$response->emit();
	}
}
