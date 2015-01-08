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
class Calendar_Calendar_ActivityTypes{
	public function process($feed, $request, $start, $end, &$result, $userid = false,$color = null,$textColor = 'white') {
		$user = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
        $userAndGroupIds = array_merge(array($user->getId()),$feed->getGroupsIdsForUsers($user->getId()));
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
}