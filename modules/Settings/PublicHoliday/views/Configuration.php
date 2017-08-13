<?php

/**
 * Settings PublicHoliday configuration view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_PublicHoliday_Configuration_View extends Settings_Vtiger_Index_View
{

	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$date = $request->get('date');
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
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->view('Configuration.tpl', $request->getModule(false));
	}
}
