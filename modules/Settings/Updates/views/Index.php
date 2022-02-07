<?php

/**
 * Settings Updates index view class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Updates_Index_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$toInstall = \App\YetiForce\Updater::getToInstall();
		if (!$request->isEmpty('download')) {
			$download = $request->getByType('download', 'Alnum');
			foreach ($toInstall as  $package) {
				if ($package['hash'] === $download) {
					\App\YetiForce\Updater::download($package);
				}
			}
		}
		$viewer->assign('TO_INSTALL', $toInstall);
		$viewer->assign('INSTALLED', Settings_Updates_Module_Model::getUpdates());
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
