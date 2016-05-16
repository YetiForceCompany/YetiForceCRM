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

class Vtiger_History_Widget extends Vtiger_Basic_Widget
{

	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=getHistory&page=1&limit=' . $this->Data['limit'];
	}

	public function getWidget()
	{
		$this->Config['tpl'] = 'History.tpl';
		$this->Config['url'] = $this->getUrl();
		$widget = $this->Config;
		return $widget;
	}

	public function getHistory(Vtiger_Request $request, Vtiger_Paging_Model $pagingModel)
	{
		$db = PearDatabase::getInstance();
		$recordId = $request->get('record');
		$type = $request->get('type');
		if (empty($type)) {
			return [];
		}

		$query = self::getQuery($recordId, $type);
		if (empty($query)) {
			return [];
		}
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$limitQuery = $query . ' LIMIT ' . $startIndex . ',' . $pageLimit;
		$results = $db->query($limitQuery);
		//var_dump($limitQuery, $db->getRowCount($results));
		$history = [];
		while ($row = $db->getRow($results)) {
			$row['userModel'] = Users_Privileges_Model::getInstanceById($row['user']);
			$history[] = $row;
		}
		return $history;
	}

	public function getQuery($recordId, $type)
	{
		$queries = [];
		if (in_array('Calendar', $type)) {
			$sql = 'SELECT CONCAT(\'Calendar\') AS type, c.crmid AS id,a.subject AS content,c.smownerid AS user,concat(a.date_start, " ", a.time_start) AS `time` FROM vtiger_activity a
				INNER JOIN vtiger_crmentity c ON c.crmid = a.activityid 
				WHERE c.deleted = 0 AND a.link = ' . $recordId;
			$instance = CRMEntity::getInstance('Calendar');
			$securityParameter = $instance->getUserAccessConditionsQuerySR('Calendar', false, $recordId);
			if ($securityParameter != '')
				$sql .= $securityParameter;
			$queries[] = $sql;
		}
		if (in_array('ModComments', $type)) {
			$sql = 'SELECT CONCAT(\'ModComments\') AS type,m.modcommentsid AS id,m.commentcontent AS content,c.smownerid AS user,c.createdtime AS `time` FROM vtiger_modcomments m
				INNER JOIN vtiger_crmentity c ON m.modcommentsid = c.crmid 
				WHERE c.deleted = 0 AND related_to = ' . $recordId;
			$instance = CRMEntity::getInstance('ModComments');
			$securityParameter = $instance->getUserAccessConditionsQuerySR('ModComments', false, $recordId);
			if ($securityParameter != '')
				$sql .= $securityParameter;
			$queries[] = $sql;
		}
		if (in_array('Emails', $type)) {
			$sql = 'SELECT CONCAT(\'Emails\') AS type,o.ossmailviewid AS id,o.subject AS content,c.smownerid AS user,c.createdtime AS `time` FROM vtiger_ossmailview o
			INNER JOIN vtiger_crmentity c ON c.crmid = o.ossmailviewid 
			INNER JOIN vtiger_ossmailview_relation r ON r.ossmailviewid = o.ossmailviewid 
			WHERE c.deleted = 0 AND r.crmid = ' . $recordId;
			$instance = CRMEntity::getInstance('OSSMailView');
			$securityParameter = $instance->getUserAccessConditionsQuerySR('OSSMailView', false, $recordId);
			if ($securityParameter != '')
				$sql .= $securityParameter;
			$queries[] = $sql;
		}

		if (count($queries) == 1) {
			$sql = reset($queries);
		} else {
			$sql = 'SELECT * FROM (';
			foreach ($queries as $query) {
				$sql .= $query . ' UNION ALL ';
			}
			$sql = rtrim($sql, ' UNION  ALL ').') AS records ORDER BY time DESC';
		}
		return $sql;
	}
}
