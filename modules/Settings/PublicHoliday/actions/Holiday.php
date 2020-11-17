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
		parent::__construct();
		$this->exposeMethod('list');
		$this->exposeMethod('save');
		$this->exposeMethod('duplicate');
		$this->exposeMethod('delete');
		$this->exposeMethod('massDelete');
	}

	/**
	 * Static function to get the instance of the Vtiger_Viewer
	 *
	 * @param \App\Request $request
	 * @return \Vtiger_Viewer
	 */
	protected function getViewer(\App\Request $request): Vtiger_Viewer
	{
		$viewer = \Vtiger_Viewer::getInstance();
		$viewer->assign('APPTITLE', \App\Language::translate('APPTITLE'));
		$viewer->assign('YETIFORCE_VERSION', \App\Version::get());
		$viewer->assign('MODULE_NAME', $request->getModule());
		if ($request->isAjax()) {
			$viewer->assign('USER_MODEL', \Users_Record_Model::getCurrentUserModel());
			if (!$request->isEmpty('parent', true) && 'Settings' === $request->getByType('parent', 2)) {
				$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
			}
		}
		return $viewer;
	}

	/**
	 * Returns view for holiday item list by date filtering
	 * 
	 * @param \App\Request $request
	 * @return none
	 */
	public function list(App\Request $request): void
	{
		$moduleModel = Settings_PublicHoliday_Module_Model::getInstance();
		$date = !$request->isEmpty('date') ? $request->getArray('date', 'DateInUserFormat') : [];
		$start = is_array($date) && count($date) == 2 ? $date[0] : '';
		$end = is_array($date) && count($date) == 2 ? $date[1] : '';
		$range = $start && $end ? [$start, $end] : [];
		$sysStart = is_array($date) && count($date) == 2 ? App\Fields\Date::formatToDB($date[0]) : '';
		$sysEnd = is_array($date) && count($date) == 2 ? App\Fields\Date::formatToDB($date[1]) : '';
		$sysRange = $sysStart && $sysEnd ? [$sysStart, $sysEnd] : [];
		$viewer = $this->getViewer($request);
		$viewer->assign('DATE', implode(',', $range));
		$viewer->assign('HOLIDAYS', $moduleModel->getHolidaysByRange($sysRange));
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$view = $viewer->view('ConfigurationItems.tpl', $request->getModule(false), true);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'items' => $view,
		]);
		$response->emit();
	}

	/**
	 * Saves holiday
	 *
	 * @param \App\Request $request
	 * @return none
	 */
	public function save(App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule(false);
		$result = [
			'success' => true,
			'message' => '',
		];
		try {
			$isNew = $request->isEmpty('holidayId');
			$holidayId = !$isNew ? $request->getInteger('holidayId'): 0;
			$date = !$request->isEmpty('holidayDate') ? $request->getByType('holidayDate', 'DateInUserFormat') : '';
			$date = DateTimeField::convertToDBFormat($date);
			if ($date) {
				$moduleModel = Settings_PublicHoliday_Module_Model::getInstance();
				$isCreatingExistingDate = $isNew && (0 < count($moduleModel->getHolidayByDate($date)));
				if (!$isCreatingExistingDate) {
					$name = !$request->isEmpty('holidayName') ? $request->getByType('holidayName', 'Text') : '';
					$type = !$request->isEmpty('holidayType') ? $request->getByType('holidayType', 'Text'): '';
					$saveResult = false;
					if ($isNew) {
						if ($date && $type && $name) {
							$recordModel = Settings_PublicHoliday_Record_Model::getCleanInstance();
							$recordModel->setData([
								'holidaydate' => $date,
								'holidayname' => $name,
								'holidaytype' => $type,
							]);
							$saveResult = $recordModel->save();
							if ($saveResult) {
								$result['message'] = \App\Language::translate('LBL_NEW_DATE_OK', $moduleName);
							} else {
								$result['success'] = false;
								$result['message'] = \App\Language::translate('LBL_NEW_DATE_ERROR', $moduleName);
							}
						} else {
							$result['success'] = false;
							$result['message'] = \App\Language::translate('LBL_FILL_FORM_ERROR', $moduleName);
						}
					} else {
						$recordModel = Settings_PublicHoliday_Record_Model::getInstanceById($holidayId);
						if ($date) {
							$recordModel->set('holidaydate', $date);
						}
						if ($name) {
							$recordModel->set('holidayname', $name);
						}
						if ($type) {
							$recordModel->set('holidaytype', $type);
						}
						$saveResult = $recordModel->save();
						if ($saveResult) {
							$result['message'] = \App\Language::translate('LBL_EDIT_DATE_OK', $moduleName);
						} else {
							$result['message'] = \App\Language::translate('LBL_EDIT_DATE_NOTHINGTOUPDATE', $moduleName);
						}
					}
				} else {
					$result['success'] = false;
					$result['message'] = $request->getByType('holidayDate', 'Text') . '<br />' . \App\Language::translate('LBL_DATE_EXISTS', $moduleName);
				}
			} else {
				$result['success'] = false;
				$result['message'] = \App\Language::translate('LBL_FILL_FORM_ERROR', $moduleName);
			}
			$response->setResult([
				'success' => $result['success'],
				'message' => $result['message'],
			]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Duplicates holidays for year
	 * 
	 * @param \App\Request $request
	 * @return none
	 */
	public function duplicate(App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule(false);
		$moduleModel = Settings_PublicHoliday_Module_Model::getInstance();
		$result = [
			'success' => true,
			'message' => '',
		];
		try {
			$holidayIds = !$request->isEmpty('holidayIds') ? $request->getByType('holidayIds', 'Text') : '';
			$sourceIds = $holidayIds ? explode(',', $holidayIds) : [];
			$targetYear = !$request->isEmpty('targetYear') ? $request->getByType('targetYear', 'Text') : '';
			if (count($sourceIds) && $targetYear) {
				$notDuplicated = [];
				$targetDates = [];
				foreach ($sourceIds as $sourceId) {
					$sourceRecordModel = Settings_PublicHoliday_Record_Model::getInstanceById((int) $sourceId);
					$sourceDate = $sourceRecordModel->getDate();
					$targetDate = date($targetYear . '-m-d', strtotime($sourceDate));
					$targetDateArray = explode('-', $targetDate);
					if (checkdate($targetDateArray[1], $targetDateArray[2], $targetDateArray[0])) {
						$targetDates[$targetDate] = $sourceRecordModel;
					} else {
						$notDuplicated[] = DateTimeField::convertToUserFormat($sourceDate);
					}
				}
				foreach ($targetDates as $targetDate => $sourceRecordModel) {
					$sourceDate = $sourceRecordModel->getDate();
					$dateExists = $moduleModel->getHolidayByDate($targetDate);
					if (count($dateExists)) {
						$notDuplicated[] = DateTimeField::convertToUserFormat($sourceDate);
					} else {
						$targetRecordModel = Settings_PublicHoliday_Record_Model::getCleanInstance();
						$targetRecordModel->setData([
							'holidaydate' => $targetDate,
							'holidayname' => $sourceRecordModel->getName(),
							'holidaytype' => $sourceRecordModel->getType(),
						]);
						$saveResult = $targetRecordModel->save();
						if (!$saveResult) {
							$notDuplicated[] = DateTimeField::convertToUserFormat($sourceDate);
						}
					}
				}
				if (count($notDuplicated)) {
					$result['success'] = false;
					$result['message'] = \App\Language::translate('LBL_DUPLICATE_ERROR', $moduleName) . ' ' . $targetYear . '<br />' . implode(', ', $notDuplicated);	
				} else {
					$result['success'] = true;
					$result['message'] = \App\Language::translate('LBL_DUPLICATE_SUCCESS', $moduleName);	
				}
			} else {
				$result['success'] = false;
				$result['message'] = \App\Language::translate('LBL_FILL_FORM_ERROR', $moduleName);
			}
			$response->setResult([
				'success' => $result['success'],
				'message' => $result['message'],
			]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Deletes holiday
	 *
	 * @param \App\Request $request
	 * @return none
	 */
	public function delete(App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule(false);
		$result = [
			'success' => true,
			'message' => '',
		];
		try {
			$id = !$request->isEmpty('id') ? $request->getInteger('id') : 0;
			if ($id) {
				$recordModel = Settings_PublicHoliday_Record_Model::getInstanceById((int) $id);
				$deleteResult = $recordModel->delete();
				$result['message'] = $deleteResult == 0 ?
									\App\Language::translate('LBL_HOLIDAY_DELETE_ALREADYDELETED', $moduleName):
									\App\Language::translate('LBL_HOLIDAY_DELETE_OK', $moduleName);
			} else {
				$result['success'] = false;
				$result['message'] = \App\Language::translate('LBL_HOLIDAY_DELETE_NOTHINGTODELETE', $moduleName);
			}
			$response->setResult([
				'success' => $result['success'],
				'message' => $result['message'],
			]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Mass delete of holidays
	 * 
	 * @param \App\Request $request
	 * @return none
	 */
	public function massDelete(App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule(false);
		$result = [
			'success' => true,
			'message' => '',
		];
		try {
			$records = !$request->isEmpty('records') ? $request->getArray('records') : [];
			if (count($records)) {
				$deletedRecords = 0;
				foreach ($records as $id) {
					$recordModel = Settings_PublicHoliday_Record_Model::getInstanceById((int) $id);
					$deletedRecords += $recordModel->delete();
				}
				$result['message'] = $deletedRecords == count($records) ?
									\App\Language::translate('LBL_HOLIDAY_DELETE_OK', $moduleName):
									\App\Language::translate('LBL_HOLIDAY_DELETE_SOMENOTDELETED', $moduleName);
			} else {
				$result['success'] = false;
				$result['message'] = \App\Language::translate('LBL_HOLIDAY_DELETE_NOTHINGTODELETE', $moduleName);
			}
			$response->setResult([
				'success' => $result['success'],
				'message' => $result['message'],
			]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
