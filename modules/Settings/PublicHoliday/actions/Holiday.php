<?php

/**
 * Settings PublicHoliday holiday action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * @param <Object> $request
	 *
	 * @return true if deleted, false otherwise
	 */
	public function delete(\App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = 'Settings:' . $request->getModule();

		try {
			$id = $request->get('id');

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
	 * @param <Object> $request
	 *
	 * @return true if saved, false otherwise
	 */
	public function save(\App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = 'Settings:' . $request->getModule();

		try {
			$id = $request->get('holidayId');
			$date = DateTimeField::convertToDBFormat($request->get('holidayDate'));
			$name = $request->get('holidayName');
			$type = $request->get('holidayType');

			if (empty($name) || empty($date)) {
				$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_FILL_FORM_ERROR', $moduleName)]);
			} elseif (!empty($id)) {
				if (Settings_PublicHoliday_Module_Model::edit($id, $date, $name, $type)) {
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
			$response->setError($e->getCode(), $e->getMessage());
		}

		$response->emit();
	}
}
