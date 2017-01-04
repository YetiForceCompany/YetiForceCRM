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

class OSSTimeControl_Calendar_Model extends Vtiger_Base_Model
{

	public function getEntity()
	{
		$db = PearDatabase::getInstance();
		$module = 'OSSTimeControl';
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$query = getListQuery($module);
		$params = array();
		if ($this->get('start') && $this->get('end')) {
			$dbStartDateOject = DateTimeField::convertToDBTimeZone($this->get('start'), null, false);
			$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
			$dbStartDate = $dbStartDateOject->format('Y-m-d');
			$dbEndDateObject = DateTimeField::convertToDBTimeZone($this->get('end'), null, false);
			$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
			$dbEndDate = $dbEndDateObject->format('Y-m-d');
			$query.= " AND ((concat(vtiger_osstimecontrol.date_start, ' ', vtiger_osstimecontrol.time_start) >= ? AND concat(vtiger_osstimecontrol.date_start, ' ', vtiger_osstimecontrol.time_start) <= ?) OR (concat(vtiger_osstimecontrol.due_date, ' ', vtiger_osstimecontrol.time_end) >= ? AND concat(vtiger_osstimecontrol.due_date, ' ', vtiger_osstimecontrol.time_end) <= ?) OR (vtiger_osstimecontrol.date_start < ? AND vtiger_osstimecontrol.due_date > ?) )";
			$params[] = $dbStartDateTime;
			$params[] = $dbEndDateTime;
			$params[] = $dbStartDateTime;
			$params[] = $dbEndDateTime;
			$params[] = $dbStartDate;
			$params[] = $dbEndDate;
		}
		if ($this->get('types')) {
			$query.= " AND vtiger_osstimecontrol.timecontrol_type IN ('" . implode("','", $this->get('types')) . "')";
		}
		if ($this->get('user')) {
			if (is_array($this->get('user'))) {
				$query.= ' AND vtiger_crmentity.smownerid IN (' . implode(",", $this->get('user')) . ')';
			} else {
				$query.= ' AND vtiger_crmentity.smownerid IN (' . $this->get('user') . ')';
			}
		}
		$query .= \App\PrivilegeQuery::getAccessConditions($module, $currentUser->getId());
		$query .= ' ORDER BY date_start,time_start ASC';

		$queryResult = $db->pquery($query, $params);
		$result = [];
		$numRowsCount = $db->num_rows($queryResult);
		for ($i = 0; $i < $numRowsCount; $i++) {
			$record = $db->raw_query_result_rowdata($queryResult, $i);

			$item = [];
			$crmid = $record['osstimecontrolid'];
			$item['id'] = $crmid;
			$item['title'] = \App\Language::translate($record['name'], $module);
			$item['url'] = 'index.php?module=OSSTimeControl&view=Detail&record=' . $crmid;

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString . ' ' . $dateTimeComponents[1];

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));


			$item['end'] = $dataBaseDateFormatedString . ' ' . $dateTimeComponents[1];
			$item['className'] = ' userCol_' . $record['smownerid'] . ' calCol_' . $record['timecontrol_type'];
			$result[] = $item;
		}
		return $result;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name
	 * @param mixed id or name of the module
	 */
	public static function getInstance()
	{
		$instance = Vtiger_Cache::get('ossTimeControlModels', 'Calendar');
		if ($instance === false) {
			$instance = new self();
			Vtiger_Cache::set('ossTimeControlModels', 'Calendar', clone $instance);
			return $instance;
		} else {
			return clone $instance;
		}
	}

	public static function getCalendarTypes()
	{
		$calendarConfig = Array(
			'PLL_WORKING_TIME',
			'PLL_BREAK_TIME',
			'PLL_HOLIDAY_TIME'
		);
		return $calendarConfig;
	}
}
