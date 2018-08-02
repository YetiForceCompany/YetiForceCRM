<?php

/**
 * Settings Updates index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Updates_Index_View extends Settings_Vtiger_Index_View
{
	public function process(\App\Request $request)
	{
		$updates = Settings_Updates_Module_Model::getUpdates();

		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);

		$viewer->assign('UPDATES', $updates);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
