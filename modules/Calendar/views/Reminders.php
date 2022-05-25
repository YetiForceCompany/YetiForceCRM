<?php

/**
 * Panel file with reminders of calendar module events.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Panel class with reminders of calendar module events.
 */
class Calendar_Reminders_View extends Vtiger_IndexAjax_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('PERMISSION_TO_SENDE_MAIL', \App\Mail::checkMailClient());
		$viewer->assign('RECORDS', Calendar_Module_Model::getCalendarReminder());
		$viewer->view('Reminders.tpl', $request->getModule());
	}
}
