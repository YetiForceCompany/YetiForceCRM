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
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$date = $request->getArray('date', 'DateInUserFormat');
		if (!$date) {
			$startDate = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
			$startDate = new DateTimeField($startDate);
			$endDate = date('Y-m-d', mktime(23, 59, 59, date('m') + 1, 0, date('Y')));
			$endDate = new DateTimeField($endDate);
			$date = [
				$startDate->getDisplayDate(),
				$endDate->getDisplayDate(),
			];
		}
		$holidays = Settings_PublicHoliday_Module_Model::getHolidays($date);
		$viewer->assign('DATE', implode(',', $date));
		$viewer->assign('HOLIDAYS', $holidays);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->view('Configuration.tpl', $request->getModule(false));
	}
}
