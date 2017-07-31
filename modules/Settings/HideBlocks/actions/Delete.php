<?php

/**
 * Settings HideBlocks delete action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_HideBlocks_Delete_Action extends Settings_Vtiger_Index_Action
{

	public function process(\App\Request $request)
	{
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_HideBlocks_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		$recordModel->delete();

		$returnUrl = Settings_HideBlocks_Module_Model::getListViewUrl();
		header("Location: $returnUrl");
	}
}
