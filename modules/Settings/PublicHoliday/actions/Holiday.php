<?php

/**
 * Settings PublicHoliday holiday action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_PublicHoliday_Holiday_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		$this->exposeMethod('delete');
		$this->exposeMethod('save');
	}

	/**
	 * Delete date.
	 *
	 * @param \App\Request $request
	 */
	public function delete(\App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule(false);
		try {
			$id = $request->getInteger('id');
			if (Settings_PublicHoliday_Module_Model::delete($id)) {
				$response->setResult(['success' => true, 'message' => \App\Language::translate('JS_HOLIDAY_DELETE_OK', $moduleName)]);
			} else {
				$response->setResult(['success' => false, 'message' => \App\Language::translate('JS_HOLIDAY_DELETE_ERROR', $moduleName)]);
			}
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Save date.
	 *
	 * @param \App\Request $request
	 */
	public function save(\App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule(false);
		try {
			$date = DateTimeField::convertToDBFormat($request->getByType('holidayDate', 'DateInUserFormat'));
			$name = $request->getByType('holidayName', 'Text');
			$type = $request->getByType('holidayType');
			if (empty($name) || empty($date)) {
				$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_FILL_FORM_ERROR', $moduleName)]);
			} elseif (!$request->isEmpty('holidayId')) {
				if (Settings_PublicHoliday_Module_Model::edit($request->getInteger('holidayId'), $date, $name, $type)) {
					$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_EDIT_DATE_OK', $moduleName)]);
				} else {
					$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_EDIT_DATE_ERROR', $moduleName)]);
				}
			} else {
				if (Settings_PublicHoliday_Module_Model::save($date, $name, $type)) {
					$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_NEW_DATE_OK', $moduleName)]);
				} else {
					$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_NEW_DATE_ERROR', $moduleName)]);
				}
			}
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getDisplayMessage());
		}
		$response->emit();
	}
}
