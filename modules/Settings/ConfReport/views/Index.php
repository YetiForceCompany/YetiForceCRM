<?php

/**
 * Settings ConfReport index view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_ConfReport_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		\App\Cache::clear();
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('ALL', \App\Utils\ConfReport::getAll());
		$viewer->assign('ERRORS', \App\Utils\ConfReport::$errors);
		$viewer->assign('MODULE_NAME', $qualifiedModuleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
