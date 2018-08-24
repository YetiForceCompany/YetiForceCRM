<?php

/**
 * Log list view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 */
class Settings_Log_List_View extends Settings_Vtiger_Index_View
{
	/**
	 * Function gets module settings.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$type = $request->getByType('type', 1);
		$module = new Settings_Log_Module_Model();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $module->getName(true));
		$viewer->assign('MODULE', $module);
		$viewer->assign('TABLE_HEADERS', $module::$tableHeaders);
		$viewer->assign('TYPE', ($type) ? $type : 'access_for_admin');
		$viewer->view('List.tpl', $request->getModule(false));
	}
}
