<?php

/**
 * Class for history widget
 * @package YetiForce.Widget
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_History_Widget extends Vtiger_Basic_Widget
{

	public static $colors = [
		'ModComments' => 'bgBlue',
		'OSSMailView' => 'bgOrange',
		'Calendar' => 'bgGreen',
	];

	static public function getActions()
	{
		return ['ModComments', 'Emails', 'Calendar'];
	}

	public function getUrl()
	{
		$url = 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=getHistory&page=1&limit=' . $this->Data['limit'];
		foreach (self::getActions() as $type) {
			$url .= '&type[]=' . $type;
		}
		return $url;
	}

	public function getWidget()
	{
		$this->Config['tpl'] = 'History.tpl';
		$this->Config['url'] = $this->getUrl();
		$widget = $this->Config;
		return $widget;
	}

	public function getConfigTplName()
	{
		return 'HistoryConfig';
	}

	public function getHistory(Vtiger_Request $request, Vtiger_Paging_Model $pagingModel)
	{
		$db = PearDatabase::getInstance();
		$recordId = $request->get('record');
		$type = $request->get('type');
		if (empty($type)) {
			return [];
		}

		$query = self::getQuery($recordId, $request->getModule(), $type);
		if (empty($query)) {
			return [];
		}
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$limitQuery = $query . ' LIMIT ' . $startIndex . ',' . $pageLimit;
		$results = $db->query($limitQuery);
		$history = [];
		$groups = Settings_Groups_Record_Model::getAll();
		$groupIds = array_keys($groups);
		while ($row = $db->getRow($results)) {
			if (in_array($row['user'], $groupIds)) {
				$row['isGroup'] = true;
				$row['userModel'] = $groups[$row['user']];
			} else {
				$row['isGroup'] = false;
				$row['userModel'] = Users_Privileges_Model::getInstanceById($row['user']);
			}
			$row['class'] = self::$colors[$row['type']];
			$history[] = $row;
		}
		return $history;
	}

	public function getQuery($recordId, $moduleName, $type)
	{
		$queries = [];
		$field = Vtiger_Module_Model::getMappingRelatedField($moduleName);

		if (in_array('Calendar', $type)) {
			$sql = 'SELECT CONCAT(\'Calendar\') AS type, c.crmid AS id,a.subject AS content,c.smownerid AS user,concat(a.date_start, " ", a.time_start) AS `time` FROM vtiger_activity a
				INNER JOIN vtiger_crmentity c ON c.crmid = a.activityid 
				WHERE c.deleted = 0 AND a.' . $field . ' = ' . $recordId;
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
			$sql = 'SELECT CONCAT(\'OSSMailView\') AS type,o.ossmailviewid AS id,o.subject AS content,c.smownerid AS user,c.createdtime AS `time` FROM vtiger_ossmailview o
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
			$sql = rtrim($sql, ' UNION  ALL ') . ') AS records';
		}
		$sql .= ' ORDER BY time DESC';
		return $sql;
	}
}
