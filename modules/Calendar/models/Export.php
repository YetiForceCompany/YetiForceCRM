<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
vimport('modules.Calendar.iCal.iCalendar_rfc2445');
vimport('modules.Calendar.iCal.iCalendar_components');
vimport('modules.Calendar.iCal.iCalendar_properties');
vimport('modules.Calendar.iCal.iCalendar_parameters');

class Calendar_Export_Model extends Vtiger_Export_Model
{

	/**
	 * Function that generates Export Query based on the mode
	 * @param Vtiger_Request $request
	 * @return string export query
	 */
	public function getExportQuery(Vtiger_Request $request)
	{
		$moduleName = $request->get('source_module');
		$cvId = $request->get('viewname');
		$listInstance = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
		if (!empty($operator)) {
			$listInstance->set('operator', $operator);
		}
		if (!empty($searchKey) && !empty($searchValue)) {
			$listInstance->set('search_key', $searchKey);
			$listInstance->set('search_value', $searchValue);
		}
		$searchParams = $request->get('search_params');
		if (!empty($searchParams) && is_array($searchParams)) {
			$transformedSearchParams = $listInstance->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
			$listInstance->set('search_params', $transformedSearchParams);
		}
		$listInstance->loadListViewCondition();
		$moduleModel = $listInstance->getModule();
		$fields = array_keys($moduleModel->getFields());
		$fields[] = 'id';
		$listInstance->getQueryGenerator()->setFields($fields);

		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		if (!empty($selectedIds) && !in_array($selectedIds, ['all', '"all"'])) {
			if (!empty($selectedIds) && count($selectedIds) > 0) {
				$listInstance->getQueryGenerator()->addCondition('id', $selectedIds, 'e');
			}
		}
		if ($excludedIds) {
			$listInstance->getQueryGenerator()->addCondition('id', $excludedIds, 'n');
		}
		$query = $listInstance->getQueryGenerator()->createQuery();
		$query->limit(AppConfig::performance('MAX_NUMBER_EXPORT_RECORDS'));
		$fields = array_values($query->select);
		$query->select($fields);
		return $query;
	}

	/**
	 * Function returns the export type - This can be extended to support different file exports
	 * @param Vtiger_Request $request
	 * @return string
	 */
	public function getExportContentType(Vtiger_Request $request)
	{
		return 'text/calendar';
	}

	/**
	 * Function exports the data based on the mode
	 * @param Vtiger_Request $request
	 */
	public function exportData(Vtiger_Request $request)
	{
		$moduleName = $request->get('source_module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$moduleModel->setEventFieldsForExport();
		$moduleModel->setTodoFieldsForExport();

		$query = $this->getExportQuery($request);
		$fileName = $request->get('filename');
		$this->outputData($request, $query->createCommand()->query(), $moduleModel, $fileName);
	}

	/**
	 * Function that create the exported file
	 * @param Vtiger_Request $request
	 * @param array $dataReader
	 * @param Vtiger_Module_Model $moduleModel
	 */
	public function outputData($request, $dataReader, $moduleModel, $fileName, $toFile = false)
	{
		$adb = PearDatabase::getInstance();
		$timeZone = new iCalendar_timezone;
		$timeZoneId = explode('/', date_default_timezone_get());

		if (!empty($timeZoneId[1])) {
			$zoneId = $timeZoneId[1];
		} else {
			$zoneId = $timeZoneId[0];
		}

		$timeZone->add_property('TZID', $zoneId);
		$timeZone->add_property('TZOFFSETTO', date('O'));

		if (date('I') == 1) {
			$timeZone->add_property('DAYLIGHTC', date('I'));
		} else {
			$timeZone->add_property('STANDARDC', date('I'));
		}

		$myiCal = new iCalendar;
		$myiCal->add_component($timeZone);

		while ($row = $dataReader->read()) {
			$eventFields = $row;
			$id = $eventFields['activityid'];
			$type = $eventFields['activitytype'];
			if ($type != 'Task') {
				$temp = $moduleModel->get('eventFields');
				foreach ($temp as $fieldName => $access) {
					/* Priority property of ical is Integer
					 * http://kigkonsult.se/iCalcreator/docs/using.html#PRIORITY
					 */
					if ($fieldName == 'priority') {
						$priorityMap = array('High' => '1', 'Medium' => '2', 'Low' => '3');
						$priorityval = $eventFields[$fieldName];
						$icalZeroPriority = 0;
						if (array_key_exists($priorityval, $priorityMap))
							$temp[$fieldName] = $priorityMap[$priorityval];
						else
							$temp[$fieldName] = $icalZeroPriority;
					}
					else {
						$temp[$fieldName] = $eventFields[$fieldName];
					}
				}
				$temp['id'] = $id;

				$iCalTask = new iCalendar_event;
				$iCalTask->assign_values($temp);

				$iCalAlarm = new iCalendar_alarm;
				$iCalAlarm->assign_values($temp);
				$iCalTask->add_component($iCalAlarm);
			} else {
				$temp = $moduleModel->get('todoFields');
				foreach ($temp as $fieldName => $access) {
					if ($fieldName == 'priority') {
						$priorityMap = array('High' => '1', 'Medium' => '2', 'Low' => '3');
						$priorityval = $eventFields[$fieldName];
						$icalZeroPriority = 0;
						if (array_key_exists($priorityval, $priorityMap))
							$temp[$fieldName] = $priorityMap[$priorityval];
						else
							$temp[$fieldName] = $icalZeroPriority;
					} else
						$temp[$fieldName] = $eventFields[$fieldName];
				}
				$iCalTask = new iCalendar_todo;
				$iCalTask->assign_values($temp);
			}
			$myiCal->add_component($iCalTask);
		}
		if ($toFile) {
			return $myiCal->serialize();
		} else {
			$exportType = $this->getExportContentType($request);
			// Send the right content type and filename
			header("Content-type: $exportType");
			header("Content-Disposition: attachment; filename={$fileName}.ics");
			echo $myiCal->serialize();
		}
	}
}
