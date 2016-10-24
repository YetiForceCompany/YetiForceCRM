<?php

/**
 * Class for history widget
 * @package YetiForce.Widget
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_HistoryRelation_Widget extends Vtiger_Basic_Widget
{

	public static $colors = [
		'ModComments' => 'bgBlue',
		'OSSMailViewReceived' => 'bgGreen',
		'OSSMailViewSent' => 'bgDanger',
		'OSSMailViewInternal' => 'bgBlue',
		'Calendar' => 'bgOrange',
	];

	static public function getActions()
	{
		return ['ModComments', 'Emails', 'Calendar'];
	}

	public function getUrl()
	{
		$url = 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showRecentRelation&page=1&limit=' . $this->Data['limit'];
		foreach (self::getActions() as $type) {
			$url .= '&type[]=' . $type;
		}
		return $url;
	}

	public function getWidget()
	{
		$this->Config['tpl'] = 'HistoryRelation.tpl';
		$this->Config['url'] = $this->getUrl();
		$widget = $this->Config;
		return $widget;
	}

	public function getConfigTplName()
	{
		return 'HistoryRelationConfig';
	}

	/**
	 * Function gets records for timeline widget
	 * @param Vtiger_Request $request
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return array - List of records
	 */
	public static function getHistory(Vtiger_Request $request, Vtiger_Paging_Model $pagingModel)
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
			if (strpos($row['type'], 'OSSMailView') !== false) {
				$row['type'] = 'OSSMailView';
				$row['url'] = Vtiger_Module_Model::getInstance('OSSMailView')->getPreviewViewUrl($row['id']);
			} else {
				$row['url'] = Vtiger_Module_Model::getInstance($row['type'])->getDetailViewUrl($row['id']);
			}
			$row['body'] = vtlib\Functions::textLength(trim(preg_replace('/[ \t]+/', ' ', strip_tags($row['body']))), 100);
			$history[] = $row;
		}
		return $history;
	}

	/**
	 * Function creates database query in order to get records for timeline widget
	 * @param int $recordId
	 * @param string $moduleName
	 * @param array $type
	 * @return query
	 */
	public function getQuery($recordId, $moduleName, $type)
	{
		$queries = [];
		$field = Vtiger_ModulesHierarchy_Model::getMappingRelatedField($moduleName);

		if (in_array('Calendar', $type)) {
			$sql = sprintf('SELECT NULL AS `body`, NULL AS `attachments_exist`, CONCAT(\'Calendar\') AS type, vtiger_crmentity.crmid AS id,a.subject AS content,vtiger_crmentity.smownerid AS user,concat(a.date_start, " ", a.time_start) AS `time`
				FROM vtiger_activity a
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = a.activityid 
				WHERE vtiger_crmentity.deleted = 0 && a.%s = %d', $field, $recordId);
			$sql .= \App\PrivilegeQuery::getAccessConditions('Calendar', false, $recordId);
			$queries[] = $sql;
		}
		if (in_array('ModComments', $type)) {
			$sql = sprintf('SELECT NULL AS `body`, NULL AS `attachments_exist`, CONCAT(\'ModComments\') AS type,m.modcommentsid AS id,m.commentcontent AS content,vtiger_crmentity.smownerid AS user,vtiger_crmentity.createdtime AS `time` 
				FROM vtiger_modcomments m
				INNER JOIN vtiger_crmentity ON m.modcommentsid = vtiger_crmentity.crmid 
				WHERE vtiger_crmentity.deleted = 0 && related_to = %d', $recordId);
			$sql .= \App\PrivilegeQuery::getAccessConditions('ModComments', false, $recordId);
			$queries[] = $sql;
		}
		if (in_array('Emails', $type)) {
			$sql = sprintf('SELECT o.content AS `body`, `attachments_exist`, CONCAT(\'OSSMailView\', o.ossmailview_sendtype) AS `type`,o.ossmailviewid AS id,o.subject AS content,vtiger_crmentity.smownerid AS user,vtiger_crmentity.createdtime AS `time` 
				FROM vtiger_ossmailview o
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = o.ossmailviewid 
				INNER JOIN vtiger_ossmailview_relation r ON r.ossmailviewid = o.ossmailviewid 
				WHERE vtiger_crmentity.deleted = 0 && r.crmid = %d', $recordId);
			$sql .= \App\PrivilegeQuery::getAccessConditions('OSSMailView', false, $recordId);
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
