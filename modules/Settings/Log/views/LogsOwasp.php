<?php

/**
 * Log list view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 */
class Settings_Log_LogsOwasp_View extends Settings_Vtiger_Index_View
{
	/**
	 * Function gets module settings.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', 'Settings:Log');
		$viewer->assign('TYPE', $request->has('type') ? $request->getByType('type', 'Text') : 'access_for_admin');
		$viewer->view('LogsOwasp.tpl', $request->getModule(false));
	}
}
