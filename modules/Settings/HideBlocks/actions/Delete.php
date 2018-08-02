<?php

/**
 * Settings HideBlocks delete action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_HideBlocks_Delete_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_HideBlocks_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		$recordModel->delete();

		$returnUrl = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName)->getListViewUrl();
		header("Location: $returnUrl");
	}
}
