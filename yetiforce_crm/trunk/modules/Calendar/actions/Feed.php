<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/

vimport ('~~/include/Webservices/Query.php');

class Calendar_Feed_Action extends Vtiger_BasicAjax_Action {

	public function process(Vtiger_Request $request) {
		try {
			$result = array();

			$start = $request->get('start');
			$end   = $request->get('end');
			$type = $request->get('type');
			$userid = $request->get('userid');
			$color = $request->get('color');
			$textColor = $request->get('textColor');
			
			switch ($type) {
				case 'Events': $this->pullEvents($start, $end, $result,$userid,$color,$textColor); break;
				case 'Calendar': $this->pullTasks($start, $end, $result,$color,$textColor); break;
				case 'Potentials': $this->pullPotentials($start, $end, $result, $color, $textColor); break;
				case 'Contacts':
							if($request->get('fieldname') == 'support_end_date') {
								$this->pullContactsBySupportEndDate($start, $end, $result, $color, $textColor);
							}else{
								$this->pullContactsByBirthday($start, $end, $result, $color, $textColor);
							}
							break;

				case 'Invoice': $this->pullInvoice($start, $end, $result, $color, $textColor); break;
				case 'MultipleEvents' : $this->pullMultipleEvents($start,$end, $result,$request->get('mapping'));break;
				case 'Project': $this->pullProjects($start, $end, $result, $color, $textColor); break;
				case 'ProjectTask': $this->pullProjectTasks($start, $end, $result, $color, $textColor); break;
			}
			echo json_encode($result);
		} catch (Exception $ex) {
			echo $ex->getMessage();
		}
	}
    
    protected function getGroupsIdsForUsers($userId) {
        vimport('~~/include/utils/GetUserGroups.php');
        
        $userGroupInstance = new GetUserGroups();
        $userGroupInstance->getAllUserGroups($userId);
        return $userGroupInstance->user_groups;
    }

	protected function queryForRecords($query, $onlymine=true) {
		$user = Users_Record_Model::getCurrentUserModel();
		if ($onlymine) {
            $groupIds = $this->getGroupsIdsForUsers($user->getId());
            $groupWsIds = array();
            foreach($groupIds as $groupId) {
                $groupWsIds[] = vtws_getWebserviceEntityId('Groups', $groupId);
            }
			$userwsid = vtws_getWebserviceEntityId('Users', $user->getId());
            $userAndGroupIds = array_merge(array($userwsid),$groupWsIds);
			$query .= " AND assigned_user_id IN ('".implode("','",$userAndGroupIds)."')";
		}
		// TODO take care of pulling 100+ records
		return vtws_query($query.';', $user);
	}

	protected function pullEvents($start, $end, &$result, $userid = false,$color = null,$textColor = 'white') {
		$dbStartDateOject = DateTimeField::convertToDBTimeZone($start);
		$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
		$dbStartDateTimeComponents = explode(' ', $dbStartDateTime);
		$dbStartDate = $dbStartDateTimeComponents[0];
		
		$dbEndDateObject = DateTimeField::convertToDBTimeZone($end);
		$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
		
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$moduleModel = Vtiger_Module_Model::getInstance('Events'); 
		if($userid){
			$focus = new Users();
			$focus->id = $userid;
			$focus->retrieve_entity_info($userid, 'Users');
			$user = Users_Record_Model::getInstanceFromUserObject($focus);
			$userName = $user->getName();
		}

        $params = array();
		if(empty($userid)){
            $eventUserId  = $currentUser->getId();
        }else{
            $eventUserId = $userid;
        }
        $params = array_merge(array($eventUserId), $this->getGroupsIdsForUsers($eventUserId));
		$query = 'SELECT vtiger_activity.subject, vtiger_activity.eventstatus, vtiger_activity.visibility, vtiger_activity.date_start, vtiger_activity.time_start, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_activity.activityid, vtiger_activity.activitytype, vtiger_seactivityrel.crmid as parent_id FROM vtiger_activity LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid WHERE';
		$query.= " vtiger_activity.activitytype NOT IN ('Emails','Task') AND ";
        $hideCompleted = $currentUser->get('hidecompletedevents');
        if($hideCompleted)
        $query.= "vtiger_activity.eventstatus != 'HELD' AND ";
		$query.= " ((concat(date_start, '', time_start)  >= '$dbStartDateTime' AND concat(due_date, '', time_end) < '$dbEndDateTime') ) AND vtiger_activity.smownerid IN (".  generateQuestionMarks($params).") AND vtiger_activity.deleted=0";
		$queryResult = $db->pquery($query,$params);
		while($record = $db->fetchByAssoc($queryResult)){
			
			$item = array();
			$crmid = $record['activityid'];
			$visibility = $record['visibility'];
            $activitytype = $record['activitytype'];
            $status = $record['eventstatus'];
			$item['id'] = $crmid;
			$item['visibility'] = $visibility;
			$item['activitytype'] = $activitytype;
            $item['status'] = $status;
			//dodanie powiazan albo z kontaktow albo z powiazanych
			$title_add=$record['parent_id']?Vtiger_Functions::getCRMRecordLabel($record['parent_id']): implode(', ',getActivityRelatedContacts($crmid));			
			if(!$currentUser->isAdminUser() && $visibility == 'Private' && $userid && $userid != $currentUser->getId()) {
				$item['title'] = decode_html($userName);
				$item['url']   = '';
			} else {
				$item['title'] = decode_html($record['subject']);
				$item['url']   = sprintf('index.php?module=Calendar&view=Detail&record=%s', $crmid);
			}
			if($title_add != ''){
				$item['title'] .= ' ['.decode_html($title_add).']';
			}
			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['end']   =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$item['className'] = $cssClass;
			$item['allDay'] = false;
			$item['color'] = $color;
			$item['textColor'] = $textColor;
            $item['module'] = $moduleModel->getName();
			$result[] = $item;
		}
	}

	protected function pullMultipleEvents($start, $end, &$result, $data) {

		foreach ($data as $id=>$backgroundColorAndTextColor) {
			$userEvents = array();
			$colorComponents = explode(',',$backgroundColorAndTextColor);
			$this->pullEvents($start, $end, $userEvents ,$id, $colorComponents[0], $colorComponents[1]);
			$result[$id] = $userEvents;
		}
	}

	protected function pullTasks($start, $end, &$result, $color = null,$textColor = 'white') {
		$user = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
        $userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $user);

		$queryGenerator->setFields(array('activityid','subject', 'taskstatus','activitytype', 'date_start','time_start','due_date','time_end','id'));				
		$query = $queryGenerator->getQuery();
		$newQuery = spliti('FROM', $query);
		foreach ($newQuery as $key=>$val) {
			if($key == 0){
				$query = $newQuery[0].' ,vtiger_seactivityrel.crmid as parent_id';
			}else{
				$query .= ' FROM '.$val;
			}
		}
		$joinQuery = ' LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid';
		$newQuery = spliti('WHERE ', $query);
		$query = $newQuery[0].$joinQuery;
		foreach ($newQuery as $key=>$val) {
			if($key != 0){
				$query .= ' WHERE '.$val;
			}
		}
		$query.= " AND vtiger_activity.activitytype = 'Task' AND ";
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $hideCompleted = $currentUser->get('hidecompletedevents');
        if($hideCompleted)
            $query.= "vtiger_activity.status != 'Completed' AND ";
		//$query.= " ((date_start >= '$start' AND due_date < '$end') OR ( due_date >= '$start'))";
		// opensaas
		$query.= " ((date_start >= '$start' AND due_date < '$end') )";
		// opensaas
        $params = $userAndGroupIds;
		$query.= " AND vtiger_crmentity.smownerid IN (".generateQuestionMarks($params).")";
		
		$queryResult = $db->pquery($query,$params);
		
		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['activityid'];
			//dodanie infromacji z wyciagnietych
			$title_add=$record['parent_id']?Vtiger_Functions::getCRMRecordLabel($record['parent_id']): implode(', ',getActivityRelatedContacts($crmid));			
			$item['title'] = decode_html($record['subject']);
			if($title_add != ''){
				$item['title'] .= ' ['.decode_html($title_add).']';
			}
            $item['status'] = $record['status'];
            $item['activitytype'] = $record['activitytype'];
            $item['id'] = $crmid;
			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];
			$item['end']   = $record['due_date'];
			$item['url']   = sprintf('index.php?module=Calendar&view=Detail&record=%s', $crmid);
			$item['color'] = $color;
			$item['textColor'] = $textColor;
            $item['module'] = $moduleModel->getName();
			$result[] = $item;
		}
	}

	protected function pullPotentials($start, $end, &$result, $color = null,$textColor = 'white') {
		$query = "SELECT potentialname,closingdate FROM Potentials";
		$query.= " WHERE closingdate >= '$start' AND closingdate <= '$end'";
		$records = $this->queryForRecords($query);
		foreach ($records as $record) {
			$item = array();
			list ($modid, $crmid) = vtws_getIdComponents($record['id']);
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['potentialname']);
			$item['start'] = $record['closingdate'];
			$item['url']   = sprintf('index.php?module=Potentials&view=Detail&record=%s', $crmid);
			$item['color'] = $color;
			$item['textColor'] = $textColor;
			$result[] = $item;
		}
	}

	protected function pullContacts($start, $end, &$result, $color = null,$textColor = 'white') {
		$this->pullContactsBySupportEndDate($start, $end, $result, $color, $textColor);
		$this->pullContactsByBirthday($start, $end, $result, $color, $textColor);
	}

	protected function pullContactsBySupportEndDate($start, $end, &$result, $color = null,$textColor = 'white') {
		$query = "SELECT firstname,lastname,support_end_date FROM Contacts";
		$query.= " WHERE support_end_date >= '$start' AND support_end_date <= '$end'";
		$records = $this->queryForRecords($query);
		foreach ($records as $record) {
			$item = array();
			list ($modid, $crmid) = vtws_getIdComponents($record['id']);
			$item['id'] = $crmid;
			$item['title'] = decode_html(trim($record['firstname'] . ' ' . $record['lastname']));
			$item['start'] = $record['support_end_date'];
			$item['url']   = sprintf('index.php?module=Contacts&view=Detail&record=%s', $crmid);
			$item['color'] = $color;
			$item['textColor'] = $textColor;
			$result[] = $item;
		}
	}

	protected  function pullContactsByBirthday($start, $end, &$result, $color = null,$textColor = 'white') {
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
		$startDateComponents = split('-', $start);
		$endDateComponents = split('-', $end);
        
        $userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
        $params = $userAndGroupIds;
        
		$year = $startDateComponents[0];

		$query = "SELECT firstname,lastname,birthday,crmid FROM vtiger_contactdetails";
		$query.= " INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid";
		$query.= " INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid";
		$query.= " WHERE vtiger_crmentity.deleted=0 AND smownerid IN (".  generateQuestionMarks($params) .") AND";
		$query.= " ((CONCAT('$year-', date_format(birthday,'%m-%d')) >= '$start'
						AND CONCAT('$year-', date_format(birthday,'%m-%d')) <= '$end')";

        
		$endDateYear = $endDateComponents[0];
		if ($year !== $endDateYear) {
			$query .= " OR
						(CONCAT('$endDateYear-', date_format(birthday,'%m-%d')) >= '$start'
							AND CONCAT('$endDateYear-', date_format(birthday,'%m-%d')) <= '$end')";
		}
		$query .= ")";

		$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['crmid'];
			$recordDateTime = new DateTime($record['birthday']);

			$calendarYear = $year;
			if($recordDateTime->format('m') < $startDateComponents[1]) {
				$calendarYear = $endDateYear;
			}
			$recordDateTime->setDate($calendarYear, $recordDateTime->format('m'), $recordDateTime->format('d'));
			$item['id'] = $crmid;
			$item['title'] = decode_html(trim($record['firstname'] . ' ' . $record['lastname']));
			$item['start'] = $recordDateTime->format('Y-m-d');
			$item['url']   = sprintf('index.php?module=Contacts&view=Detail&record=%s', $crmid);
			$item['color'] = $color;
			$item['textColor'] = $textColor;
			$result[] = $item;
		}
	}

	protected function pullInvoice($start, $end, &$result, $color = null,$textColor = 'white') {
		$query = "SELECT subject,duedate FROM Invoice";
		$query.= " WHERE duedate >= '$start' AND duedate <= '$end'";
		$records = $this->queryForRecords($query);
		foreach ($records as $record) {
			$item = array();
			list ($modid, $crmid) = vtws_getIdComponents($record['id']);
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['subject']);
			$item['start'] = $record['duedate'];
			$item['url']   = sprintf('index.php?module=Invoice&view=Detail&record=%s', $crmid);
			$item['color'] = $color;
			$item['textColor'] = $textColor;
			$result[] = $item;
		}
	}

	/**
	 * Function to pull all the current user projects
	 * @param type $startdate
	 * @param type $actualenddate
	 * @param type $result
	 * @param type $color
	 * @param type $textColor
	 */
	protected function pullProjects($start, $end, &$result, $color = null,$textColor = 'white') {
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
		$userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
        $params = $userAndGroupIds;
        
		$query = "SELECT projectname, startdate, targetenddate, crmid FROM vtiger_project";
		$query.= " INNER JOIN vtiger_crmentity ON vtiger_project.projectid = vtiger_crmentity.crmid";
		$query.= " WHERE vtiger_crmentity.deleted=0 AND smownerid IN (". generateQuestionMarks($params) .") AND ";
		$query.= " ((startdate >= '$start' AND targetenddate < '$end') OR ( targetenddate >= '$start'))";
		$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['crmid'];
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['projectname']);
			$item['start'] = $record['startdate'];
			$item['end'] = $record['targetenddate'];
			$item['url']   = sprintf('index.php?module=Project&view=Detail&record=%s', $crmid);
			$item['color'] = $color;
			$item['textColor'] = $textColor;
			$result[] = $item;
		}
	}

	/**
	 * Function to pull all the current user porjecttasks
	 * @param type $startdate
	 * @param type $enddate
	 * @param type $result
	 * @param type $color
	 * @param type $textColor
	 */
	protected function pullProjectTasks($start, $end, &$result, $color = null,$textColor = 'white') {
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
        $userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
        $params = $userAndGroupIds;
		
		$query = "SELECT projecttaskname, startdate, enddate, crmid FROM vtiger_projecttask";
		$query.= " INNER JOIN vtiger_crmentity ON vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid";
		$query.= " WHERE vtiger_crmentity.deleted=0 AND smownerid IN (". generateQuestionMarks($params) .") AND ";
		$query.= " ((startdate >= '$start' AND enddate < '$end') OR ( enddate >= '$start'))";
		$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['crmid'];
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['projecttaskname']);
			$item['start'] = $record['startdate'];
			$item['end'] = $record['enddate'];
			$item['url']   = sprintf('index.php?module=ProjectTask&view=Detail&record=%s', $crmid);
			$item['color'] = $color;
			$item['textColor'] = $textColor;
			$result[] = $item;
		}
	}

}