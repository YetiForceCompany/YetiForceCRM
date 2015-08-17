<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_PublicHoliday_Holiday_Action extends Settings_Vtiger_Index_Action
{

	public function __construct()
	{
		$this->exposeMethod('delete');
		$this->exposeMethod('save');
	}

	/**
	 * Delete date
	 * @param <Object> $request
	 * @return true if deleted, false otherwise
	 */
	public function delete(Vtiger_Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = 'Settings:' . $request->getModule();

		try {
			$id = $request->get('id');

			if (Settings_PublicHoliday_Module_Model::delete($id)) {
				$response->setResult(array('success' => true, 'message' => vtranslate('JS_HOLIDAY_DELETE_OK', $moduleName)));
			} else {
				$response->setResult(array('success' => false, 'message' => vtranslate('JS_HOLIDAY_DELETE_ERROR', $moduleName)));
			}
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}

		$response->emit();
	}

	/**
	 * Save date
	 * @param <Object> $request
	 * @return true if saved, false otherwise
	 */
	public function save(Vtiger_Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = 'Settings:' . $request->getModule();

		try {
			$id = $request->get('holidayId');
			$date = DateTimeField::convertToDBFormat($request->get('holidayDate'));
			$name = $request->get('holidayName');
			$type = $request->get('holidayType');

			if (empty($name) || empty($date)) {
				$response->setResult(array('success' => false, 'message' => vtranslate('LBL_FILL_FORM_ERROR', $moduleName)));
			} else if (!empty($id)) {
				if (Settings_PublicHoliday_Module_Model::edit($id, $date, $name, $type)) {
					$response->setResult(array('success' => true, 'message' => vtranslate('LBL_EDIT_DATE_OK', $moduleName)));
				} else {
					$response->setResult(array('success' => false, 'message' => vtranslate('LBL_EDIT_DATE_ERROR', $moduleName)));
				}
			} else {
				if (Settings_PublicHoliday_Module_Model::save($date, $name, $type)) {
					$response->setResult(array('success' => true, 'message' => vtranslate('LBL_NEW_DATE_OK', $moduleName)));
				} else {
					$response->setResult(array('success' => false, 'message' => vtranslate('LBL_NEW_DATE_ERROR', $moduleName)));
				}
			}
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}

		$response->emit();
	}
}
