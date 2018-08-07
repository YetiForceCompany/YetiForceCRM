<?php

/**
 * Settings HideBlocks delete action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_HideBlocks_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		Settings_HideBlocks_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule(false))->delete();
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
