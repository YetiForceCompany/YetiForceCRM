<?php

/**
 * Settings Updates index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Updates_Index_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$toInstall = \App\YetiForce\Updater::getToInstall();
		if (!$request->isEmpty('download')) {
			$key = array_search($request->getByType('download', 'Alnum'), array_column($toInstall, 'hash'));
			\App\YetiForce\Updater::download($toInstall[$key]);
		}
		$viewer->assign('TO_INSTALL', $toInstall);
		$viewer->assign('INSTALLED', Settings_Updates_Module_Model::getUpdates());
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
