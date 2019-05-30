<?php
/**
 * Settings BusinessHours save action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_BusinessHours_Save_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Save business hours entry.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$workingDays = $request->getArray('working_days', 'Integer');
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($request->getModule(false));
		if (!$request->isEmpty('record', true)) {
			$recordModel = Settings_BusinessHours_Record_Model::getInstanceById($request->getInteger('record'));
		} else {
			$recordModel = new Settings_BusinessHours_Record_Model();
		}
		$recordModel->set('businesshoursname', $request->getByType('businesshoursname', 'Text'));
		$recordModel->set('working_days', implode(',', $workingDays));
		$recordModel->set('working_hours_from', \App\Fields\Time::formatToDB($request->getByType('working_hours_from', 'TimeInUserFormat'), false));
		$recordModel->set('working_hours_to', \App\Fields\Time::formatToDB($request->getByType('working_hours_to', 'TimeInUserFormat'), false));
		$recordModel->set('holidays', $request->getBoolean('holidays') ? 1 : 0);
		$recordModel->set('default', $request->getBoolean('default') ? 1 : 0);
		$recordModel->save();
		header('location: ' . $moduleModel->getListViewUrl());
	}
}
