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
class Calendar_ProjectTask_ActivityTypes{
	public function process($feed, $request, $start, $end, &$result, $userid = false,$color = null,$textColor = 'white') {
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
        $userAndGroupIds = array_merge(array($user->getId()),$feed->getGroupsIdsForUsers($user->getId()));
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