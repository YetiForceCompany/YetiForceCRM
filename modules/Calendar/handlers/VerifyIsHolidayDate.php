<?php
/**
 * Verify Is Holiday Date handler.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Calendar_VerifyIsHolidayDate_Handler class.
 */
class Calendar_VerifyIsHolidayDate_Handler
{
	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$response = ['result' => true];
		$recordModel = $eventHandler->getRecordModel();
		if (!empty(App\Fields\Date::getHolidays($recordModel->get('date_start'), $recordModel->get('due_date')))) {
			$response = [
				'result' => false,
				'type'=>'confirm',
				'message' => App\Language::translate('LBL_DATES_SELECTED_HOLIDAYS_CONFIRM', $recordModel->getModuleName()),
				'hash' => hash('sha256', implode('|',$recordModel->getData()))
			];
		}

		return $response;
	}
}
