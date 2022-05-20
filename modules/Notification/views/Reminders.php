<?php
/**
 * Notifications reminders.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Class for Notifications reminders.
 */
class Notification_Reminders_View extends Vtiger_IndexAjax_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$entries = $moduleModel->getEntriesInstance(\App\Config::module($moduleName, 'MAX_NUMBER_NOTIFICATIONS'));
		$viewer->assign('RECORDS', $entries);
		$viewer->assign('COLORS', ['PLL_SYSTEM' => '#FF9800', 'PLL_USERS' => '#1baee2']);
		$viewer->view('Reminders.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function isSessionExtend(App\Request $request)
	{
		return false;
	}
}
