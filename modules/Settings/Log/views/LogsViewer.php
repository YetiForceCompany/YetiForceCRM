<?php

/**
 * Log list viewer file.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$type = $request->has('type') ? $request->getByType('type', 'Text') : 'magento';
		$viewer = $this->getViewer($request);
		$viewer->assign('TYPE', $type);
		$viewer->assign('MAPPING', \App\Log::$logsViewerColumnMapping[$type]);
		$viewer->view('LogsViewer.tpl', $request->getModule(false));
	}
}
