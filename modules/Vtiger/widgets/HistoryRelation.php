<?php

/**
 * Class for history widget.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_HistoryRelation_Widget extends Vtiger_Basic_Widget
{
	/**
	 * Names od classes that define color.
	 *
	 * @var string[]
	 */
	public static $colors = [
		'ModComments' => 'bg-primary',
		'OSSMailViewReceived' => 'bg-danger',
		'OSSMailViewSent' => 'bg-success',
		'OSSMailViewInternal' => 'bg-primary',
		'Calendar' => 'bg-warning',
	];

	/**
	 * Function gets modules name.
	 *
	 * @return string[]
	 */
	public static function getActions()
	{
		$modules = ['ModComments', 'OSSMailView', 'Calendar'];
		foreach ($modules as $key => $module) {
			if (!\App\Privilege::isPermitted($module)) {
				unset($modules[$key]);
			}
		}

		return $modules;
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
		return $this->Config;
	}

	public function getConfigTplName()
	{
		return 'HistoryRelationConfig';
	}

	/**
	 * Function gets records for timeline widget.
	 *
	 * @param \App\Request        $request
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return array - List of records
	 */
	public static function getHistory(\App\Request $request, Vtiger_Paging_Model $pagingModel)
	{
		$recordId = $request->getInteger('record');
		if ($request->isEmpty('type')) {
			return [];
		}
		$query = static::getQuery($recordId, $request->getModule(), $request->getArray('type', 'Alnum'));
		if (empty($query)) {
			return [];
		}
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$query->limit($pageLimit)->offset($startIndex);
		$history = [];
		$groups = Settings_Groups_Record_Model::getAll();
		$groupIds = array_keys($groups);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
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
			$body = trim(App\Purifier::purify($row['body']));
			if (!$request->getBoolean('isFullscreen')) {
				$body = App\TextParser::textTruncate($body, 100);
			} else {
				$body = str_replace(['<p></p>', '<p class="MsoNormal">'], ["\r\n", "\r\n"], App\Purifier::decodeHtml(App\Purifier::purify($body)));
				$body = nl2br(App\TextParser::textTruncate($body, 500), false);
			}
			$row['body'] = $body;
			$history[] = $row;
		}

		return $history;
	}

	/**
	 * Function creates database query in order to get records for timeline widget.
	 *
	 * @param int    $recordId
	 * @param string $moduleName
	 * @param array  $type
	 *
	 * @return \App\Db\Query()|false
	 */
	public static function getQuery($recordId, $moduleName, $type)
	{
		$queries = [];
		$field = \App\ModuleHierarchy::getMappingRelatedField($moduleName);
		$db = App\Db::getInstance();
		if (in_array('Calendar', $type)) {
			$query = (new \App\Db\Query())
				->select([
					'body' => new \yii\db\Expression($db->quoteValue('')),
					'attachments_exist' => new \yii\db\Expression($db->quoteValue('')),
					'type' => new \yii\db\Expression($db->quoteValue('Calendar')),
					'id' => 'vtiger_crmentity.crmid',
					'content' => 'a.subject',
					'user' => 'vtiger_crmentity.smownerid',
					'time' => new \yii\db\Expression('CONCAT(a.date_start, ' . $db->quoteValue(' ') . ', a.time_start)'),
				])
				->from('vtiger_activity a')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = a.activityid')
				->where(['vtiger_crmentity.deleted' => 0])
				->andWhere(['=', 'a.' . $field, $recordId]);
			\App\PrivilegeQuery::getConditions($query, 'Calendar', false, $recordId);
			$queries[] = $query;
		}
		if (in_array('ModComments', $type)) {
			$query = (new \App\Db\Query())
				->select([
					'body' => new \yii\db\Expression($db->quoteValue('')),
					'attachments_exist' => new \yii\db\Expression($db->quoteValue('')),
					'type' => new \yii\db\Expression($db->quoteValue('ModComments')),
					'id' => 'm.modcommentsid',
					'content' => 'm.commentcontent',
					'user' => 'vtiger_crmentity.smownerid',
					'time' => 'vtiger_crmentity.createdtime',
				])
				->from('vtiger_modcomments m')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = m.modcommentsid')
				->where(['vtiger_crmentity.deleted' => 0])
				->andWhere(['=', 'related_to', $recordId]);
			\App\PrivilegeQuery::getConditions($query, 'ModComments', false, $recordId);
			$queries[] = $query;
		}
		if (in_array('OSSMailView', $type)) {
			$query = (new \App\Db\Query())
				->select([
					'body' => 'o.content',
					'attachments_exist',
					'type' => new \yii\db\Expression('CONCAT(\'OSSMailView\', o.ossmailview_sendtype)'),
					'id' => 'o.ossmailviewid',
					'content' => 'o.subject',
					'user' => 'vtiger_crmentity.smownerid',
					'time' => 'vtiger_crmentity.createdtime',
				])
				->from('vtiger_ossmailview o')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = o.ossmailviewid')
				->innerJoin('vtiger_ossmailview_relation r', 'r.ossmailviewid = o.ossmailviewid ')
				->where(['vtiger_crmentity.deleted' => 0])
				->andWhere(['=', 'r.crmid', $recordId]);
			\App\PrivilegeQuery::getConditions($query, 'OSSMailView', false, $recordId);
			$queries[] = $query;
		}
		if (count($queries) == 1) {
			$sql = reset($queries);
		} else {
			$subQuery = reset($queries);
			$index = 0;
			foreach ($queries as $query) {
				if ($index !== 0) {
					$subQuery->union($query, true);
				}

				++$index;
			}
			if ($subQuery) {
				$sql = (new \App\Db\Query())->from(['records' => $subQuery]);
			} else {
				return false;
			}
		}

		return $sql->orderBy(['time' => SORT_DESC]);
	}
}
