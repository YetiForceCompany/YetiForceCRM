<?php namespace includes;

use vtlib\Functions;

/**
 * Record basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
			if ($id && !isset(self::$recordLabelCache[$id])) {
				$missing[] = $id;
			}
		}
		if (!empty($missing)) {
			$adb = \PearDatabase::getInstance();

			$query = sprintf('SELECT `crmid`,`label` FROM `u_yf_crmentity_label` WHERE `crmid` IN(%s)', $adb->generateQuestionMarks($missing));
			$result = $adb->pquery($query, $missing);
			while ($row = $adb->getRow($result)) {
				self::$recordLabelCache[$row['crmid']] = $row['label'];
			}
			foreach ($ids as $id) {
				if ($id && !isset(self::$recordLabelCache[$id])) {
					$metainfo = \vtlib\Functions::getCRMRecordMetadata($id);
					$computeLabel = self::computeLabels($metainfo['setype'], $id);
					self::$recordLabelCache[$id] = $computeLabel[$id];
				}
			}
		}
		$result = [];
		foreach ($ids as $id) {
			if (isset(self::$recordLabelCache[$id])) {
				$result[$id] = self::$recordLabelCache[$id];
			} else {
				$result[$id] = NULL;
			}
		}
		return $multiMode ? $result : array_shift($result);
	}

	protected static $crmidByLabelCache = [];

	public static function findCrmidByLabel($label, $moduleName = false, $limit = 20)
	{
		if (isset(self::$crmidByLabelCache[$label])) {
			$crmIds = self::$crmidByLabelCache[$label];
		} else {
			$currentUser = \Users_Record_Model::getCurrentUserModel();
			$adb = \PearDatabase::getInstance();
			$crmIds = [];
			$params = ['%,' . $currentUser->getId() . ',%', "%$label%"];
			$queryFrom = 'SELECT `crmid`,`setype`,`searchlabel` FROM `u_yf_crmentity_search_label`';
			$queryWhere = ' WHERE `userid` LIKE ? && `searchlabel` LIKE ?';
			$orderWhere = '';
			if ($moduleName !== false) {
				$multiMode = is_array($moduleName);
				if ($multiMode) {
					$queryWhere .= sprintf(' && `setype` IN (%s)', $adb->generateQuestionMarks($moduleName));
					$params = array_merge($params, $moduleName);
					$orderWhere = 'setype';
				} else {
					$queryWhere .= ' && `setype` = ?';
					$params[] = $moduleName;
				}
			} elseif (\AppConfig::search('GLOBAL_SEARCH_SORTING_RESULTS') == 2) {
				$queryFrom .= ' LEFT JOIN vtiger_entityname ON vtiger_entityname.modulename = u_yf_crmentity_search_label.setype';
				$queryWhere .= ' && vtiger_entityname.`turn_off` = 1 ';
				$orderWhere = 'vtiger_entityname.sequence';
			}
			$query = $queryFrom . $queryWhere;
			if (!empty($orderWhere)) {
				$query .= sprintf(' ORDER BY %s', $orderWhere);
			}
			if ($limit) {
				$query .= ' LIMIT ';
				$query .= $limit;
			}
			$result = $adb->pquery($query, $params);
			while ($row = $adb->getRow($result)) {
				$crmIds[] = $row;
			}
			self::$crmidByLabelCache[$label] = $crmIds;
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
		if ($moduleName == 'Events') {
			$moduleName = 'Calendar';
		}
		if ($moduleName) {
			$entityDisplay = [];
			if (!empty($ids)) {
				if (!isset(self::$computeLabelsSqlCache[$moduleName])) {
					if ($moduleName == 'Groups') {
						$metainfo = ['tablename' => 'vtiger_groups', 'entityidfield' => 'groupid', 'fieldname' => 'groupname'];
					} else {
						$metainfo = Modules::getEntityInfo($moduleName);
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
					self::$computeLabelsSqlCache[$moduleName] = $sql;
					self::$computeLabelsColumnsCache[$moduleName] = $columnsName;
					self::$computeLabelsInfoExtendCache[$moduleName] = $moduleInfoExtend;
					self::$computeLabelsColumnsSearchCache[$moduleName] = $columnsSearch;
				} else {
					$sql = self::$computeLabelsSqlCache[$moduleName];
					$columnsName = self::$computeLabelsColumnsCache[$moduleName];
					$moduleInfoExtend = self::$computeLabelsInfoExtendCache[$moduleName];
					$columnsSearch = self::$computeLabelsColumnsSearchCache[$moduleName];
				}
				$ids = array_unique($ids);
				$sql = sprintf($sql . '(%s)', $adb->generateQuestionMarks($ids));
				$result = $adb->pquery($sql, $ids);
				while ($row = $adb->getRow($result)) {
					$labelSearch = $labelName = [];
					foreach ($columnsName as $columnName) {
						if ($moduleInfoExtend && in_array($moduleInfoExtend[$columnName]['uitype'], [10, 51, 75, 81]))
							$labelName[] = self::getLabel($row[$columnName]);
						else
							$labelName[] = $row[$columnName];
					}
					if ($search) {
						foreach ($columnsSearch as $columnName) {
							if ($moduleInfoExtend && in_array($moduleInfoExtend[$columnName]['uitype'], [10, 51, 75, 81]))
								$labelSearch[] = self::getLabel($row[$columnName]);
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
		$labelInfo = self::computeLabels($moduleName, $id, true);
		if (!empty($labelInfo)) {
			$adb = \PearDatabase::getInstance();
			$label = decode_html($labelInfo[$id]['name']);
			$search = decode_html($labelInfo[$id]['search']);
			$insertMode = $mode != 'edit';
			$rowCount = 0;
			if (empty($label)) {
				$adb->delete('u_yf_crmentity_label', 'crmid = ?', [$id]);
			} else {
				if (!$insertMode) {
					$rowCount = $adb->update('u_yf_crmentity_label', ['label' => $label], 'crmid = ?', [$id]);
					if ($rowCount == 0) {
						$result = $adb->pquery('SELECT 1 FROM `u_yf_crmentity_label` WHERE `crmid` = ?', [$id]);
						$rowCount = $adb->getRowCount($result);
					}
				}
				if (($insertMode || $rowCount == 0) && $updater != 'searchlabel') {
					$adb->insert('u_yf_crmentity_label', ['crmid' => $id, 'label' => $label]);
				}
			}
			if (empty($search)) {
				$adb->delete('u_yf_crmentity_search_label', 'crmid = ?', [$id]);
			} else {
				if (!$insertMode) {
					$rowCount = $adb->update('u_yf_crmentity_search_label', ['searchlabel' => $search], 'crmid = ?', [$id]);
					if ($rowCount == 0) {
						$result = $adb->pquery('SELECT 1 FROM `u_yf_crmentity_search_label` WHERE `crmid` = ?', [$id]);
						$rowCount = $adb->getRowCount($result);
					}
				}
				if (($insertMode || $rowCount == 0) && $updater != 'label') {
					$adb->insert('u_yf_crmentity_search_label', ['crmid' => $id, 'searchlabel' => $search, 'setype' => $moduleName]);
				}
			}
			self::$recordLabelCache[$id] = $labelInfo[$id]['name'];
		}
	}

	public static function isExists($recordId)
	{
		$recordMetaData = Functions::getCRMRecordMetadata($recordId);
		return (isset($recordMetaData) && $recordMetaData['deleted'] == 0 ) ? true : false;
	}

	public static function getType($recordId)
	{
		$metadata = Functions::getCRMRecordMetadata($recordId);
		return $metadata ? $metadata['setype'] : NULL;
	}
}
