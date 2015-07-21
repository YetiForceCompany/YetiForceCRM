<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class Calendar_Calendar_Model extends Vtiger_Base_Model{
	var $relationAcounts = [
		'Contacts' => ['vtiger_contactdetails','contactid','parentid'],
		'Potentials' => ['vtiger_potential','potentialid','related_to'],
		'Project' => ['vtiger_project','projectid','linktoaccountscontacts'],
		'HelpDesk' => ['vtiger_troubletickets','ticketid','parent_id'],
		'ServiceContracts' => ['vtiger_servicecontracts','servicecontractsid','sc_related_to'],
	];
	
	public function getEntity() {
		$db = PearDatabase::getInstance();
		$module = 'Calendar';
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$query = "SELECT vtiger_activity.activityid as act_id,vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.setype,
		vtiger_activity.*, relcrm.setype AS linkmod, relcrm.label AS linklabel, procrm.label AS processlabel, procrm.setype AS processmod
		FROM vtiger_activity
		LEFT JOIN vtiger_activitycf
			ON vtiger_activitycf.activityid = vtiger_activity.activityid
		LEFT JOIN vtiger_crmentity
			ON vtiger_crmentity.crmid = vtiger_activity.activityid
		LEFT JOIN vtiger_crmentity relcrm
			ON relcrm.crmid = vtiger_activity.link
		LEFT JOIN vtiger_crmentity procrm
			ON procrm.crmid = vtiger_activity.process
		WHERE vtiger_crmentity.deleted = 0 AND activitytype != 'Emails' ";
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);
		if($securityParameter != '')
			$query.= $securityParameter;
		
		$params = array();
		if($this->get('start') && $this->get('end')){
			$dbStartDateOject = DateTimeField::convertToDBTimeZone($this->get('start'));
			$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
			$dbStartDate = $dbStartDateOject->format('Y-m-d');
			$dbEndDateObject = DateTimeField::convertToDBTimeZone($this->get('end'));
			$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
			$dbEndDate = $dbEndDateObject->format('Y-m-d');
			$query.= " AND ( (concat(date_start, ' ', time_start)  >= ? AND concat(date_start, ' ', time_start) <= ?) OR (concat(due_date, ' ', time_end)  >= ? AND concat(due_date, ' ', time_end) <= ?) OR (date_start < ? AND due_date > ?) ) ";
			$params[] = $dbStartDateTime;
			$params[] = $dbEndDateTime;
			$params[] = $dbStartDateTime;
			$params[] = $dbEndDateTime;
			$params[] = $dbStartDate;
			$params[] = $dbEndDate;
		}
		if($this->get('types')){
			$query.= " AND vtiger_activity.activitytype IN ('".implode("','", $this->get('types'))."')";
		}
		if ($this->get('time') == 'current') {
			$query .= " AND ((vtiger_activity.activitytype='Task' and vtiger_activity.status not in ('Completed','Deferred'))
			OR (vtiger_activity.activitytype not in ('Emails','Task') and vtiger_activity.eventstatus not in ('','Held')))";
		}
		if ($this->get('time') == 'history') {
			$query .= " AND ((vtiger_activity.activitytype='Task' and vtiger_activity.status in ('Completed','Deferred'))
			OR (vtiger_activity.activitytype not in ('Emails','Task') and  vtiger_activity.eventstatus in ('','Held')))";
		}
		if($this->get('user')){
			if(is_array($this->get('user'))){
				$query.= ' AND vtiger_crmentity.smownerid IN ('.implode(",", $this->get('user')).')';
			}else{
				$query.= ' AND vtiger_crmentity.smownerid IN ('.$this->get('user').')';
			}
		}
		$query.= ' ORDER BY date_start,time_start ASC';
		$queryResult = $db->pquery($query,$params);
		$result = array();
		for($i = 0; $i < $db->num_rows($queryResult); $i++){
			$record = $db->raw_query_result_rowdata($queryResult, $i);

			$item = array();
			$crmid = $record['activityid'];
			$activitytype = $record['activitytype'];
			$item['id'] = $crmid;
			$item['title'] = $record['subject'];
			$item['url']   = 'index.php?module='.$module.'&view=Detail&record='.$crmid;
			$item['set'] = $record['activitytype']=='Task'?'Task':'Event';
			$item['lok'] = $record['location'];
			$item['pri'] = $record['priority'];
			$item['sta'] = $record['status']==''?$record['eventstatus']:$record['status'];
			$item['vis'] = $record['visibility'];
			$item['state'] = $record['state'];
			
			//Relation
			$item['link'] = $record['link'];
			$item['linkl'] = $record['linklabel'];
			$item['linkm'] = $record['linkmod'];
			//Process
			$item['process'] = $record['process'];
			$item['procl'] = $record['processlabel'];
			$item['procm'] = $record['processmod'];

			if ($record['linkmod'] != 'Accounts' && (!empty($record['link']) || !empty($record['process']))) {
				$findId = 0;
				$findMod = '';
				if (!empty($record['link'])){
					$findId = $record['link'];
					$findMod = $record['linkmod'];
				}
				if (!empty($record['process'])){
					$findId = $record['process'];
					$findMod = $record['processmod'];
				}
				$tabInfo = $this->relationAcounts[$findMod];
				if($tabInfo){
					$findResult = $db->pquery('SELECT accountid, accountname FROM vtiger_account '
							. 'INNER JOIN '.$tabInfo[0].' ON vtiger_account.accountid = '.$tabInfo[0].'.'.$tabInfo[2]
							. ' WHERE '.$tabInfo[1].' = ?;', [$findId]);
					if ($db->num_rows($findResult) > 0) {
						$item['accid'] = $db->query_result_raw($findResult, 0, 'accountid');
						$item['accname'] = $db->query_result_raw($findResult, 0, 'accountname');
					}
				}
			}

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$startTimeFormated = $dateTimeComponents[1];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$startDateFormated= DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			
			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$endTimeFormated = $dateTimeComponents[1];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$endDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			
			$item['start'] = $startDateFormated.' '. $startTimeFormated;
			$item['end'] = $endDateFormated.' '. $endTimeFormated;
			$item['allDay'] = $record['allday']==1?true:false;
			$item['className'] = ' userCol_'.$record['smownerid'].' calCol_'.$activitytype;
				if($this->has('widget') && $this->get('widget') == TRUE){
					$firstDate = strtotime($startDateFormated);
					$endDate = strtotime($endDateFormated);
					$diffDate = intval(($endDate - $firstDate)/(60*60*24));
					for($q=0;$q<=$diffDate;$q++){
						if($q == 0){
						   $date = $startDateFormated;
						}else{
							$date = strtotime(date("Y-m-d", strtotime($date)) . " +1 day");
							$date = date('Y-m-d', $date);
						}
						if($activitytype == 'Task'){
							$widgetElements[$date]['start'] = $date;
							$widgetElements[$date]['event']['Task']['ids'][] = $crmid;
							$crmids = Zend_Json::encode($widgetElements[$date]['event']['Task']['ids']);
							$widgetElements[$date]['event']['Task']['url'] = "index.php?module=Calendar&view=List&searchResult=".$crmids; 
							$widgetElements[$date]['event']['Task']['className'] = ' col-md-5 fc-draggable calCol_'.$activitytype; 
							$widgetElements[$date]['type'] = 'widget';
						}else{
							$widgetElements[$date]['start'] = $date;
							$widgetElements[$date]['event']['Meeting']['ids'][] = $crmid;
							$crmids = Zend_Json::encode($widgetElements[$date]['event']['Meeting']['ids']);
							$widgetElements[$date]['event']['Meeting']['url'] = "index.php?module=Calendar&view=List&searchResult=".$crmids; 
							$widgetElements[$date]['event']['Meeting']['className'] = ' col-md-5 fc-draggable calCol_'.$activitytype;
							$widgetElements[$date]['type'] = 'widget';
						} 
					}
					$result = array_values($widgetElements);
				}else{
				   $result[] = $item; 
				}
			
		}
		return $result;
	}
	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name
	 * @param mixed id or name of the module
	 */
	public static function getInstance() {
        $instance = Vtiger_Cache::get('calendar',$value);
        if(!$instance){
            $instance = new self();
        }
		return $instance;
	}
	
	public static function getCalendarTypes() {
		$calendarConfig = Array(
			'PLL_WORKING_TIME',
			'PLL_BREAK_TIME',
			'PLL_HOLIDAY'
		);
		return $calendarConfig;
	}
}
