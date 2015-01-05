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
class Calendar_Events_ActivityTypes{
	public function process($feed, $request, $start, $end, &$result, $userid = false,$color = null,$textColor = 'white') {
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
        $params = array_merge(array($eventUserId), $feed->getGroupsIdsForUsers($eventUserId));
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
			$item['end'] =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];
			$item['className'] = $cssClass;
			$item['allDay'] = false;
			$item['color'] = $color;
			$item['textColor'] = $textColor;
            $item['module'] = $moduleModel->getName();
			$result[] = $item;
		}
		return $widget;
	}
}