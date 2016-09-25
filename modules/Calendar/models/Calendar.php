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

class Calendar_Calendar_Model extends Vtiger_Base_Model
{

	var $moduleName = 'Calendar';
	var $relationAcounts = [
		'Contacts' => ['vtiger_contactdetails', 'contactid', 'parentid'],
		'Project' => ['vtiger_project', 'projectid', 'linktoaccountscontacts'],
		'HelpDesk' => ['vtiger_troubletickets', 'ticketid', 'parent_id'],
		'ServiceContracts' => ['vtiger_servicecontracts', 'servicecontractsid', 'sc_related_to'],
	];

	public function getModuleName()
	{
		return $this->moduleName;
	}

	public function getQuery()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$query = 'SELECT vtiger_activity.*, relcrm.setype AS linkmod, procrm.setype AS processmod, subprocrm.setype AS subprocessmod
		FROM vtiger_activity
		LEFT JOIN vtiger_activitycf ON vtiger_activitycf.activityid = vtiger_activity.activityid
		LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
		LEFT JOIN vtiger_crmentity relcrm ON relcrm.crmid = vtiger_activity.link
		LEFT JOIN vtiger_crmentity procrm ON procrm.crmid = vtiger_activity.process
		LEFT JOIN vtiger_crmentity subprocrm ON subprocrm.crmid = vtiger_activity.subprocess
		WHERE vtiger_activity.deleted = 0 ';
		$instance = CRMEntity::getInstance($this->getModuleName());
		$securityParameter = $instance->getUserAccessConditionsQuerySR($this->getModuleName(), $currentUser);
		if (!empty($securityParameter))
			$query.= $securityParameter;

		$params = [];
		if ($this->get('start') && $this->get('end')) {
			$dbStartDateOject = DateTimeField::convertToDBTimeZone($this->get('start'));
			$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
			$dbStartDate = $dbStartDateOject->format('Y-m-d');
			$dbEndDateObject = DateTimeField::convertToDBTimeZone($this->get('end'));
			$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
			$dbEndDate = $dbEndDateObject->format('Y-m-d');
			$query.= " && ( (concat(date_start, ' ', time_start)  >= ? && concat(date_start, ' ', time_start) <= ?) || (concat(due_date, ' ', time_end)  >= ? && concat(due_date, ' ', time_end) <= ?) || (date_start < ? && due_date > ?) ) ";
			$params[] = $dbStartDateTime;
			$params[] = $dbEndDateTime;
			$params[] = $dbStartDateTime;
			$params[] = $dbEndDateTime;
			$params[] = $dbStartDate;
			$params[] = $dbEndDate;
		}
		if ($this->get('types')) {
			$query .= ' && vtiger_activity.activitytype ' . \PearDatabase::whereEquals($this->get('types'));
		}
		if ($this->get('time') == 'current') {
			$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('current');
			$query .= ' && vtiger_activity.status ' . \PearDatabase::whereEquals($stateActivityLabels);
		}
		if ($this->get('time') == 'history') {
			$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('history');
			$query .= ' && vtiger_activity.status ' . \PearDatabase::whereEquals($stateActivityLabels);
		}
		if ($this->get('activitystatus')) {
			$query .= ' && vtiger_activity.status ' . \PearDatabase::whereEquals($this->get('activitystatus'));
		}
		if ($this->get('restrict') && is_array($this->get('restrict'))) {
			$query .= ' && vtiger_activity.activityid ' . \PearDatabase::whereEquals($this->get('restrict'));
		}
		if ($this->has('filters')) {
			foreach ($this->get('filters') as $filter) {
				$filterClassName = Vtiger_Loader::getComponentClassName('CalendarFilter', $filter['name'], 'Calendar');
				$filterInstance = new $filterClassName();
				$condition = $filterInstance->getCondition($filter['value']);
				if (!empty($condition)) {
					$query .= ' ' . $condition;
				}
			}
		}
		if ($this->get('user')) {
			$query .= ' && vtiger_activity.smownerid ' . \PearDatabase::whereEquals($this->get('user'));
		}
		$query.= ' ORDER BY date_start,time_start ASC';
		return ['query' => $query, 'params' => $params];
	}

	public function getEntity()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		$data = $this->getQuery();
		$result = $db->pquery($data['query'], $data['params']);
		$return = $records = $ids = [];

		while ($record = $db->getRow($result)) {
			$records[] = $record;
			if (!empty($record['link'])) {
				$ids[] = $record['link'];
			}
			if (!empty($record['process'])) {
				$ids[] = $record['process'];
			}
			if (!empty($record['subprocess'])) {
				$ids[] = $record['subprocess'];
			}
		}
		$labels = \includes\Record::getLabel($ids);

		foreach ($records as &$record) {
			$item = [];
			$crmid = $record['activityid'];
			$activitytype = $record['activitytype'];
			$item['id'] = $crmid;
			$item['module'] = $this->getModuleName();
			$item['title'] = $record['subject'];
			$item['url'] = 'index.php?module=' . $this->getModuleName() . '&view=Detail&record=' . $crmid;
			$item['set'] = $record['activitytype'] == 'Task' ? 'Task' : 'Event';
			$item['lok'] = $record['location'];
			$item['pri'] = $record['priority'];
			$item['sta'] = $record['status'];
			$item['vis'] = $record['visibility'];
			$item['state'] = $record['state'];
			$item['smownerid'] = vtlib\Functions::getOwnerRecordLabel($record['smownerid']);

			//translate
			$item['labels']['sta'] = vtranslate($record['status'], $this->getModuleName());
			$item['labels']['pri'] = vtranslate($record['priority'], $this->getModuleName());
			$item['labels']['state'] = vtranslate($record['state'], $this->getModuleName());

			//Relation
			$item['link'] = $record['link'];
			$item['linkl'] = $labels[$record['link']];
			$item['linkm'] = $record['linkmod'];
			//Process
			$item['process'] = $record['process'];
			$item['procl'] = $labels[$record['process']];
			$item['procm'] = $record['processmod'];
			//Subprocess
			$item['subprocess'] = $record['subprocess'];
			$item['subprocl'] = $labels[$record['subprocess']];
			$item['subprocm'] = $record['subprocessmod'];

			if ($record['linkmod'] != 'Accounts' && (!empty($record['link']) || !empty($record['process']))) {
				$findId = 0;
				$findMod = '';
				if (!empty($record['link'])) {
					$findId = $record['link'];
					$findMod = $record['linkmod'];
				}
				if (!empty($record['process'])) {
					$findId = $record['process'];
					$findMod = $record['processmod'];
				}
				$tabInfo = $this->relationAcounts[$findMod];
				if ($tabInfo) {
					$findResult = $db->pquery('SELECT vtiger_account.accountid, vtiger_account.accountname FROM vtiger_account '
						. 'INNER JOIN ' . $tabInfo[0] . ' ON vtiger_account.accountid = ' . $tabInfo[0] . '.' . $tabInfo[2]
						. ' WHERE ' . $tabInfo[1] . ' = ?;', [$findId]);
					if ($db->num_rows($findResult) > 0) {
						$item['accid'] = $db->query_result_raw($findResult, 0, 'accountid');
						$item['accname'] = $db->query_result_raw($findResult, 0, 'accountname');
					}
				}
			}

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getFullcalenderDateTimevalue($currentUser);
			$startDateTimeDisplay = $dateTimeFieldInstance->getDisplayDateTimeValue();
			$startTimeDisplay = $dateTimeFieldInstance->getDisplayTime();
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$startTimeFormated = $dateTimeComponents[1];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$startDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getFullcalenderDateTimevalue($currentUser);
			$endDateTimeDisplay = $dateTimeFieldInstance->getDisplayDateTimeValue();
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$endTimeFormated = $dateTimeComponents[1];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$endDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));

			$item['start'] = $startDateFormated . ' ' . $startTimeFormated;
			$item['end'] = $endDateFormated . ' ' . $endTimeFormated;

			// display date time values
			$item['start_display'] = $startDateTimeDisplay;
			$item['end_display'] = $endDateTimeDisplay;
			$item['hour_start'] = $startTimeDisplay;
			$hours = vtlib\Functions::getDateTimeHoursDiff($item['start'], $item['end']);
			$item['hours'] = vtlib\Functions::decimalTimeFormat($hours)['short'];
			$item['allDay'] = $record['allday'] == 1 ? true : false;
			$item['className'] = ' userCol_' . $record['smownerid'] . ' calCol_' . $activitytype;
			$return[] = $item;
		}
		return $return;
	}

	public function getEntityCount()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		$startDate = DateTimeField::convertToDBTimeZone($this->get('start'));
		$startDate = strtotime($startDate->format('Y-m-d H:i:s'));
		$endDate = DateTimeField::convertToDBTimeZone($this->get('end'));
		$endDate = strtotime($endDate->format('Y-m-d H:i:s'));

		if ($this->get('customFilter')) {
			$this->set('restrict', CustomView_Record_Model::getInstanceById($this->get('customFilter'))->getRecordIds());
		}
		$data = $this->getQuery();
		$result = $db->pquery($data['query'], $data['params']);
		$return = [];
		while ($record = $db->fetch_array($result)) {
			$crmid = $record['activityid'];
			$activitytype = $record['activitytype'];

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$startDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$endDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));

			$begin = new DateTime($startDateFormated);
			$end = new DateTime($endDateFormated);
			$end->modify('+1 day');
			$interval = DateInterval::createFromDateString('1 day');
			foreach (new DatePeriod($begin, $interval, $end) as $dt) {
				$date = strtotime($dt->format('Y-m-d'));
				if ($date >= $startDate && $date <= $endDate) {
					$date = date('Y-m-d', $date);

					$return[$date]['start'] = $date;
					$return[$date]['date'] = $date;
					$return[$date]['event'][$activitytype]['count'] += 1;
					$return[$date]['event'][$activitytype]['className'] = '  fc-draggable calCol_' . $activitytype;
					$return[$date]['event'][$activitytype]['label'] = vtranslate($activitytype, $this->getModuleName());
					$return[$date]['type'] = 'widget';
				}
			}
		}
		return array_values($return);
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name
	 * @param mixed id or name of the module
	 */
	public static function getCleanInstance()
	{
		$instance = Vtiger_Cache::get('calendarModels', 'Calendar');
		if ($instance === false) {
			$instance = new self();
			Vtiger_Cache::set('calendarModels', 'Calendar', clone $instance);
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
			'PLL_HOLIDAY'
		);
		return $calendarConfig;
	}
}
