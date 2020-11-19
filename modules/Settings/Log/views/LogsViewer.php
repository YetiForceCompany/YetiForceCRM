<?php

/**
 * Log list viewer file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadisz Sołek <a.solek@yetiforce.com>
 */
/**
 * Log list viewer class.
 */
class Settings_Log_LogsViewer_View extends Settings_Vtiger_Index_View
{
	/**
	 * Function gets module settings.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', 'Settings:' . $request->getModule());
		$viewer->assign('TYPE', $request->has('type') ? $request->getByType('type', 'Text') : 'magento');
		$viewer->view('LogsViewer.tpl', $request->getModule(false));
	}
}
