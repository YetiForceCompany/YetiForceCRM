<?php

/**
 * Settings PublicHoliday holiday action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_PublicHoliday_Holiday_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('duplicate');
		$this->exposeMethod('delete');
		$this->exposeMethod('massDelete');
	}

	/**
	 * Saves holiday.
	 *
	 * @param \App\Request $request
	 */
	public function save(App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule(false);
		$result = true;
		$message = '';
		try {
			$holidayId = $request->isEmpty('holidayId', true) ? 0 : $request->getInteger('holidayId');
			$date = $request->getByType('holidayDate', 'DateInUserFormat');
			$name = $request->getByType('holidayName', \App\Purifier::TEXT);
			$type = $request->getByType('holidayType', \App\Purifier::TEXT);
			if ($date && $type && $name) {
				$date = App\Fields\Date::formatToDB($date);
				$recordModel = $holidayId ? Settings_PublicHoliday_Record_Model::getInstanceById($holidayId) : Settings_PublicHoliday_Record_Model::getCleanInstance();
				$recordModel->set('holidaydate', $date)
					->set('holidayname', $name)
					->set('holidaytype', $type);
				if ($recordModel->isDuplicate()) {
					$result = false;
					$message = $request->getByType('holidayDate', 'Text') . '<br />' . \App\Language::translate('LBL_DATE_EXISTS', $moduleName);
				} elseif ($recordModel->save()) {
					$message = $holidayId ? \App\Language::translate('LBL_EDIT_DATE_OK', $moduleName) : \App\Language::translate('LBL_NEW_DATE_OK', $moduleName);
				} else {
					$message = $holidayId ? \App\Language::translate('LBL_EDIT_DATE_NOTHINGTOUPDATE', $moduleName) : \App\Language::translate('LBL_NEW_DATE_ERROR', $moduleName);
				}
			} else {
				$result = false;
				$message = \App\Language::translate('LBL_FILL_FORM_ERROR', $moduleName);
			}
			$response->setResult([
				'success' => $result,
				'message' => $message ?: \App\Language::translate('LBL_EDIT_DATE_OK', $moduleName),
			]);
		} catch (Throwable $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Duplicates holidays for year.
	 *
	 * @param \App\Request $request
	 */
	public function duplicate(App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule(false);
		$result = true;
		$message = '';
		try {
			$sourceIds = $request->getArray('holidayIds', \App\Purifier::INTEGER);
			$targetYear = $request->getByType('targetYear', \App\Purifier::INTEGER);
			if ($sourceIds && $targetYear) {
				$notDuplicated = [];
				foreach ($sourceIds as $sourceId) {
					$sourceRecordModel = Settings_PublicHoliday_Record_Model::getInstanceById((int) $sourceId);
					$sourceDate = $sourceRecordModel->getDate();
					$targetDate = date($targetYear . '-m-d', strtotime($sourceDate));

					$targetRecordModel = Settings_PublicHoliday_Record_Model::getCleanInstance();
					$targetRecordModel->setData([
						'holidaydate' => $targetDate,
						'holidayname' => $sourceRecordModel->getName(),
						'holidaytype' => $sourceRecordModel->getType(),
					]);
					if ($targetRecordModel->isDuplicate() || !$targetRecordModel->save()) {
						$notDuplicated[] = \App\Fields\Date::formatToDisplay($sourceDate);
					}
				}
				if ($notDuplicated) {
					$result = false;
					$message = \App\Language::translate('LBL_DUPLICATE_ERROR', $moduleName) . ' ' . $targetYear . '<br />' . implode(', ', $notDuplicated);
				}
			} else {
				$result = false;
				$message = \App\Language::translate('LBL_FILL_FORM_ERROR', $moduleName);
			}
			$response->setResult([
				'success' => $result,
				'message' => $message ?: \App\Language::translate('LBL_DUPLICATE_SUCCESS', $moduleName),
			]);
		} catch (Throwable $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Deletes holiday.
	 *
	 * @param \App\Request $request
	 *
	 * @return none
	 */
	public function delete(App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule(false);
		$result = true;
		$message = '';
		try {
			$id = !$request->isEmpty('id') ? $request->getInteger('id') : 0;
			if ($id) {
				$recordModel = Settings_PublicHoliday_Record_Model::getInstanceById((int) $id);
				$deleteResult = $recordModel->delete();
				$message = 0 === $deleteResult ?
									\App\Language::translate('LBL_HOLIDAY_DELETE_ALREADYDELETED', $moduleName) :
									\App\Language::translate('LBL_HOLIDAY_DELETE_OK', $moduleName);
			} else {
				$result = false;
				$message = \App\Language::translate('LBL_HOLIDAY_DELETE_NOTHINGTODELETE', $moduleName);
			}
			$response->setResult([
				'success' => $result,
				'message' => $message,
			]);
		} catch (Throwable $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Mass delete of holidays.
	 *
	 * @param \App\Request $request
	 */
	public function massDelete(App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule(false);
		$result = true;
		$message = '';
		try {
			$records = !$request->isEmpty('records') ? $request->getArray('records') : [];
			if (\count($records)) {
				$deletedRecords = 0;
				foreach ($records as $id) {
					$recordModel = Settings_PublicHoliday_Record_Model::getInstanceById((int) $id);
					$deletedRecords += $recordModel->delete();
				}
				$message = $deletedRecords === \count($records) ?
									\App\Language::translate('LBL_HOLIDAY_DELETE_OK', $moduleName) :
									\App\Language::translate('LBL_HOLIDAY_DELETE_SOMENOTDELETED', $moduleName);
			} else {
				$result = false;
				$message = \App\Language::translate('LBL_HOLIDAY_DELETE_NOTHINGTODELETE', $moduleName);
			}
			$response->setResult([
				'success' => $result,
				'message' => $message,
			]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
