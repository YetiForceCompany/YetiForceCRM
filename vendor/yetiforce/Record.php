<?php
namespace App;

use vtlib\Functions;

/**
 * Record basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Record
{

	protected static $recordLabelCache = [];

	public static function getLabel($mixedId)
	{
		$multiMode = is_array($mixedId);
		$ids = $multiMode ? $mixedId : [$mixedId];
		$missing = [];
		foreach ($ids as $id) {
			if ($id && !isset(static::$recordLabelCache[$id])) {
				$missing[] = $id;
			}
		}
		if (!empty($missing)) {
			$query = (new \App\Db\Query())->select('crmid, label')->from('u_#__crmentity_label')->where(['crmid' => $missing]);
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				static::$recordLabelCache[$row['crmid']] = $row['label'];
			}
			foreach ($ids as $id) {
				if ($id && !isset(static::$recordLabelCache[$id])) {
					$metainfo = Functions::getCRMRecordMetadata($id);
					$computeLabel = static::computeLabels($metainfo['setype'], $id);
					static::$recordLabelCache[$id] = $computeLabel[$id];
				}
			}
		}
		$result = [];
		foreach ($ids as $id) {
			if (isset(static::$recordLabelCache[$id])) {
				$result[$id] = static::$recordLabelCache[$id];
			} else {
				$result[$id] = NULL;
			}
		}
		return $multiMode ? $result : array_shift($result);
	}

	protected static $crmidByLabelCache = [];

	public static function findCrmidByLabel($label, $moduleName = false, $limit = 20, $entityName = true)
	{
		if (isset(static::$crmidByLabelCache[$label])) {
			$crmIds = static::$crmidByLabelCache[$label];
		} else {
			$userId = \App\User::getCurrentUserId();
			$crmIds = [];
			$query = (new \App\Db\Query())
				->select(['csl.crmid', 'csl.setype', 'csl.searchlabel'])
				->from('u_#__crmentity_search_label csl')
				->where(['like', 'csl.userid', ",$userId,"])
				->innerJoin('vtiger_crmentity', 'csl.crmid = vtiger_crmentity.crmid')
				->andWhere(['like', 'csl.searchlabel', $label]);
			if ($moduleName) {
				$query->andWhere(['csl.setype' => $moduleName]);
			} elseif ($entityName) {
				$query->andWhere(['vtiger_entityname.turn_off' => 1]);
				$query->innerJoin('vtiger_entityname', 'csl.setype = vtiger_entityname.modulename');
				if (\AppConfig::search('GLOBAL_SEARCH_SORTING_RESULTS') === 2) {
					$query->orderBy('vtiger_entityname.sequence');
				}
			}
			if ($limit) {
				$query->limit($limit);
			}
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$crmIds[] = $row;
			}
			static::$crmidByLabelCache[$label] = $crmIds;
		}
		return $crmIds;
	}

	/**
	 * Function gets labels for record data
	 * @param string $moduleName
	 * @param int|array $ids
	 * @param bool $search
	 * @return string[]
	 */
	public static function computeLabels($moduleName, $ids, $search = false)
	{
		if (empty($moduleName) || empty($ids)) {
			return [];
		}
		if (!is_array($ids))
			$ids = [$ids];
		if ($moduleName === 'Events') {
			$moduleName = 'Calendar';
		}
		$entityDisplay = [];
		$cacheName = 'computeLabelsQuery';
		if (!\App\Cache::staticHas($cacheName, $moduleName)) {
			$metainfo = \App\Module::getEntityInfo($moduleName);
			if (empty($metainfo)) {
				return $entityDisplay;
			}
			$table = $metainfo['tablename'];
			$idColumn = $metainfo['entityidfield'];
			$columnsName = $metainfo['fieldnameArr'];
			$columnsSearch = $metainfo['searchcolumnArr'];
			$columns = array_unique(array_merge($columnsName, $columnsSearch));

			$moduleInfoExtend = Functions::getModuleFieldInfos($moduleName, true);
			$leftJoinTables = [];
			$paramsCol = [];
			$query = new \App\Db\Query();
			$focus = \CRMEntity::getInstance($moduleName);
			foreach (array_filter($columns) as $column) {
				if (array_key_exists($column, $moduleInfoExtend)) {
					$paramsCol[] = $column;
					if ($moduleInfoExtend[$column]['tablename'] !== $table && !in_array($moduleInfoExtend[$column]['tablename'], $leftJoinTables)) {
						$otherTable = $moduleInfoExtend[$column]['tablename'];
						$leftJoinTables[] = $otherTable;
						$focusTables = $focus->tab_name_index;
						$query->leftJoin($otherTable, "$table.$focusTables[$table] = $otherTable.$focusTables[$otherTable]");
					}
				}
			}
			$paramsCol['id'] = $idColumn;
			$query->select($paramsCol)->from($table);
			\App\Cache::staticSave($cacheName, $moduleName, $query);
		} else {
			$query = \App\Cache::staticGet($cacheName, $moduleName);
			$metainfo = \App\Module::getEntityInfo($moduleName);
			$moduleInfoExtend = Functions::getModuleFieldInfos($moduleName, true);
			if (empty($moduleInfoExtend) || empty($metainfo)) {
				return [];
			}
			$columnsName = $metainfo['fieldnameArr'];
			$columnsSearch = $metainfo['searchcolumnArr'];
			$idColumn = $metainfo['entityidfield'];
		}
		$ids = array_unique($ids);
		$query->where([$idColumn => $ids]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$labelName = [];
			foreach ($columnsName as $columnName) {
				if (in_array($moduleInfoExtend[$columnName]['uitype'], [10, 51, 59, 75, 81, 66, 67, 68]))
					$labelName[] = static::getLabel($row[$columnName]);
				elseif (in_array($moduleInfoExtend[$columnName]['uitype'], [53]))
					$labelName[] = \App\Fields\Owner::getLabel($row[$columnName]);
				else
					$labelName[] = $row[$columnName];
			}
			if ($search) {
				$labelSearch = [];
				foreach ($columnsSearch as $columnName) {
					if (in_array($moduleInfoExtend[$columnName]['uitype'], [10, 51, 59, 75, 81, 66, 67, 68]))
						$labelSearch[] = static::getLabel($row[$columnName]);
					elseif (in_array($moduleInfoExtend[$columnName]['uitype'], [53]))
						$labelSearch[] = \App\Fields\Owner::getLabel($row[$columnName]);
					else
						$labelSearch[] = $row[$columnName];
				}
				$entityDisplay[$row['id']] = ['name' => implode(' ', $labelName), 'search' => implode(' ', $labelSearch)];
			} else {
				$entityDisplay[$row['id']] = trim(implode(' ', $labelName));
			}
		}
		return $entityDisplay;
	}

	public static function updateLabel($moduleName, $id, $mode = 'edit', $updater = false)
	{
		$labelInfo = static::computeLabels($moduleName, $id, true);
		if (!empty($labelInfo)) {
			$db = \App\Db::getInstance();
			$label = decode_html($labelInfo[$id]['name']);
			$search = decode_html($labelInfo[$id]['search']);
			$insertMode = $mode !== 'edit';
			$rowCount = 0;
			if (empty($label)) {
				$label = '';
			}
			if (empty($search)) {
				$search = '';
			}
			if (!$insertMode) {
				$labelRowCount = $db->createCommand()
					->update('u_#__crmentity_label', ['label' => $label], ['crmid' => $id])
					->execute();
				if (!$labelRowCount) {
					$labelRowCount = (new Db\Query())->from('u_#__crmentity_label')->where(['crmid' => $id])->count();
				}
				$searchRowCount = $db->createCommand()
					->update('u_#__crmentity_search_label', ['searchlabel' => $search], ['crmid' => $id])
					->execute();
				if (!$searchRowCount) {
					$searchRowCount = (new Db\Query())->from('u_#__crmentity_search_label')->where(['crmid' => $id])->count();
				}
			}
			if (($insertMode || !$labelRowCount) && $updater !== 'searchlabel') {
				$db->createCommand()->insert('u_#__crmentity_label', ['crmid' => $id, 'label' => $label])->execute();
			}
			if (($insertMode || !$searchRowCount) && $updater !== 'label') {
				$db->createCommand()->insert('u_#__crmentity_search_label', ['crmid' => $id, 'searchlabel' => $search, 'setype' => $moduleName])->execute();
			}
			static::$recordLabelCache[$id] = $labelInfo[$id]['name'];
		}
	}

	/**
	 * Function checks if record exists
	 * @param int $recordId - Rekord ID
	 * @param string $moduleName
	 * @return boolean
	 */
	public static function isExists($recordId, $moduleName = false)
	{
		$recordMetaData = Functions::getCRMRecordMetadata($recordId);
		return (isset($recordMetaData) && $recordMetaData['deleted'] === 0 && ($moduleName ? $recordMetaData['setype'] === \App\Module::getTabName($moduleName) : true)) ? true : false;
	}

	public static function getType($recordId)
	{
		$metadata = Functions::getCRMRecordMetadata($recordId);
		return $metadata ? $metadata['setype'] : NULL;
	}
}
