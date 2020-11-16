<?php

/**
 * Settings log overview view file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Settings log overview view class.
 */
class Settings_LogOverview_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * Function gets module settings.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$type = $request->getByType('type', 1);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', 'Settings:LogOverview');
		$viewer->assign('TYPE', ($type) ? $type : 'magento');
		$viewer->view('Index.tpl', $request->getModule(false));
	}
}
