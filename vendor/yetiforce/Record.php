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
				->select('crmid,setype,searchlabel')
				->from('u_#__crmentity_search_label')
				->where(['like', 'userid', ",$userId,"])
				->andWhere(['like', 'searchlabel', $label]);
			if ($moduleName) {
				$query->andWhere(['setype' => $moduleName]);
			} elseif ($entityName) {
				$query->andWhere(['vtiger_entityname.turn_off' => 1]);
				$query->innerJoin('vtiger_entityname', 'u_#__crmentity_search_label.setype = vtiger_entityname.modulename');
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

	protected static $computeLabelsSqlCache = [];
	protected static $computeLabelsColumnsCache = [];
	protected static $computeLabelsInfoExtendCache = [];
	protected static $computeLabelsColumnsSearchCache = [];

	public static function computeLabels($moduleName, $ids, $search = false)
	{
		$adb = \PearDatabase::getInstance();
		if (!is_array($ids))
			$ids = [$ids];
		if ($moduleName === 'Events') {
			$moduleName = 'Calendar';
		}
		if ($moduleName) {
			$entityDisplay = [];
			if (!empty($ids)) {
				if (!isset(static::$computeLabelsSqlCache[$moduleName])) {
					if ($moduleName == 'Groups') {
						$metainfo = ['tablename' => 'vtiger_groups', 'entityidfield' => 'groupid', 'fieldname' => 'groupname'];
					} else {
						$metainfo = \App\Module::getEntityInfo($moduleName);
					}
					if (empty($metainfo)) {
						return $entityDisplay;
					}
					$table = $metainfo['tablename'];
					$idcolumn = $metainfo['entityidfield'];
					$columnsName = $metainfo['fieldnameArr'];
					$columnsSearch = $metainfo['searchcolumnArr'];
					$columns = array_unique(array_merge($columnsName, $columnsSearch));

					$moduleInfo = Functions::getModuleFieldInfos($moduleName);
					$moduleInfoExtend = [];
					if (count($moduleInfo) > 0) {
						foreach ($moduleInfo as $field => $fieldInfo) {
							$moduleInfoExtend[$fieldInfo['columnname']] = $fieldInfo;
						}
					}
					$leftJoin = '';
					$leftJoinTables = [];
					$paramsCol = [];
					if ($moduleName != 'Groups') {
						$focus = \CRMEntity::getInstance($moduleName);
						foreach (array_filter($columns) as $column) {
							if (array_key_exists($column, $moduleInfoExtend)) {
								$paramsCol[] = $column;
								if ($moduleInfoExtend[$column]['tablename'] != $table && !in_array($moduleInfoExtend[$column]['tablename'], $leftJoinTables)) {
									$otherTable = $moduleInfoExtend[$column]['tablename'];
									$leftJoinTables[] = $otherTable;
									$focusTables = $focus->tab_name_index;
									$leftJoin .= ' LEFT JOIN ' . $otherTable . ' ON ' . $otherTable . '.' . $focusTables[$otherTable] . ' = ' . $table . '.' . $focusTables[$table];
								}
							}
						}
					} else {
						$paramsCol = $columnsName;
					}
					$paramsCol[] = $idcolumn;
					$sql = sprintf('SELECT %s AS id FROM %s %s WHERE %s IN', implode(',', $paramsCol), $table, $leftJoin, $idcolumn);
					static::$computeLabelsSqlCache[$moduleName] = $sql;
					static::$computeLabelsColumnsCache[$moduleName] = $columnsName;
					static::$computeLabelsInfoExtendCache[$moduleName] = $moduleInfoExtend;
					static::$computeLabelsColumnsSearchCache[$moduleName] = $columnsSearch;
				} else {
					$sql = static::$computeLabelsSqlCache[$moduleName];
					$columnsName = static::$computeLabelsColumnsCache[$moduleName];
					$moduleInfoExtend = static::$computeLabelsInfoExtendCache[$moduleName];
					$columnsSearch = static::$computeLabelsColumnsSearchCache[$moduleName];
				}
				$ids = array_unique($ids);
				$sql = sprintf($sql . '(%s)', $adb->generateQuestionMarks($ids));
				$result = $adb->pquery($sql, $ids);
				while ($row = $adb->getRow($result)) {
					$labelSearch = $labelName = [];
					foreach ($columnsName as $columnName) {
						if ($moduleInfoExtend && in_array($moduleInfoExtend[$columnName]['uitype'], [10, 51, 75, 81]))
							$labelName[] = static::getLabel($row[$columnName]);
						else
							$labelName[] = $row[$columnName];
					}
					if ($search) {
						foreach ($columnsSearch as $columnName) {
							if ($moduleInfoExtend && in_array($moduleInfoExtend[$columnName]['uitype'], [10, 51, 75, 81]))
								$labelSearch[] = static::getLabel($row[$columnName]);
							else
								$labelSearch[] = $row[$columnName];
						}
						$entityDisplay[$row['id']] = ['name' => implode(' ', $labelName), 'search' => implode(' ', $labelSearch)];
					}else {
						$entityDisplay[$row['id']] = trim(implode(' ', $labelName));
					}
				}
			}
			return $entityDisplay;
		}
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
		return (isset($recordMetaData) && $recordMetaData['deleted'] === 0 && ($moduleName ? $recordMetaData['setype'] === $moduleName : true)) ? true : false;
	}

	public static function getType($recordId)
	{
		$metadata = Functions::getCRMRecordMetadata($recordId);
		return $metadata ? $metadata['setype'] : NULL;
	}
}
