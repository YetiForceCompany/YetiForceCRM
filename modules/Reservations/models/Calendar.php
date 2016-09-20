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

class Reservations_Calendar_Model extends Vtiger_Base_Model
{

	public function getEntity()
	{
		$db = PearDatabase::getInstance();
		$module = 'Reservations';
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$query = getListQuery($module);
		$params = array();
		if ($this->get('start') && $this->get('end')) {
			$dbStartDateOject = DateTimeField::convertToDBTimeZone($this->get('start'), $currentUser, false);
			$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
			$dbStartDate = $dbStartDateOject->format('Y-m-d');
			$dbEndDateObject = DateTimeField::convertToDBTimeZone($this->get('end'), $currentUser, false);
			$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
			$dbEndDate = $dbEndDateObject->format('Y-m-d');
			$query.= " && ((concat(vtiger_reservations.date_start, ' ', vtiger_reservations.time_start) >= ? && concat(vtiger_reservations.date_start, ' ', vtiger_reservations.time_start) <= ?) || (concat(vtiger_reservations.due_date, ' ', vtiger_reservations.time_end) >= ? && concat(vtiger_reservations.due_date, ' ', vtiger_reservations.time_end) <= ?) || (vtiger_reservations.date_start < ? && vtiger_reservations.due_date > ?) )";
			$params[] = $dbStartDateTime;
			$params[] = $dbEndDateTime;
			$params[] = $dbStartDateTime;
			$params[] = $dbEndDateTime;
			$params[] = $dbStartDate;
			$params[] = $dbEndDate;
		}
		if ($this->get('types')) {
			$query.= " && vtiger_reservations.type IN ('" . implode("','", $this->get('types')) . "')";
		}
		if ($this->get('user')) {
			if (is_array($this->get('user'))) {
				$query.= ' && vtiger_crmentity.smownerid IN (' . implode(",", $this->get('user')) . ')';
			} else {
				$query.= ' && vtiger_crmentity.smownerid IN (' . $this->get('user') . ')';
			}
		}
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);
		if ($securityParameter != '')
			$query.= $securityParameter;
		$query.= ' ORDER BY date_start,time_start ASC';

		$queryResult = $db->pquery($query, $params);
		$result = array();
		for ($i = 0; $i < $db->num_rows($queryResult); $i++) {
			$record = $db->raw_query_result_rowdata($queryResult, $i);

			$item = array();
			$crmid = $record['reservationsid'];
			$item['id'] = $crmid;
			$item['title'] = $record['title'];
			$item['url'] = 'index.php?module=Reservations&view=Detail&record=' . $crmid;

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
			$item['className'] = ' userCol_' . $record['smownerid'] . ' calCol_' . $record['type'];
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
		$instance = Vtiger_Cache::get('reservationsModels', 'Calendar');
		if ($instance === false) {
			$instance = new self();
			Vtiger_Cache::set('reservationsModels', 'Calendar', clone $instance);
			return $instance;
		} else {
			return clone $instance;
		}
	}

	public static function getCalendarTypes()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT fieldparams FROM vtiger_field WHERE columnname = ? && tablename = ?;", ['type', 'vtiger_reservations']);
		$templateId = $db->query_result($result, 0, 'fieldparams');
		$result = $db->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ?;', [$templateId]);
		$calendarConfig = [];
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$calendarConfig[$db->query_result_raw($result, $i, 'tree')] = $db->query_result_raw($result, $i, 'label');
		}
		return $calendarConfig;
	}
}
