<?php

/**
 * Settings PublicHoliday configuration view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_PublicHoliday_Configuration_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$date = $request->getArray('date', 'DateInUserFormat');
		if ($date) {
			$start = App\Fields\Date::formatToDB($date[0]);
			$end = App\Fields\Date::formatToDB($date[1]);
		} else {
			$start = date('Y') . '-01-01';
			$end = date('Y') . '-12-31';
		}
		$viewer->assign('DATE', implode(',', $date));
		$viewer->assign('HOLIDAYS', App\Fields\Date::getHolidays($start, $end));
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->view('Configuration.tpl', $request->getModule(false));
	}
}
