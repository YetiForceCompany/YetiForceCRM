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

	public static function findCrmidByLabel($label, $moduleName = false)
	{
		if (isset(self::$crmidByLabelCache[$label])) {
			$crmIds = self::$crmidByLabelCache[$label];
		} else {
			$adb = \PearDatabase::getInstance();
			$crmIds = [];
			$params = ["%$label%"];
			if ($moduleName === false) {
				$query = 'SELECT `crmid`,`searchlabel` FROM `u_yf_crmentity_search_label` WHERE `searchlabel` LIKE ?';
			} else {
				$multiMode = is_array($moduleName);
				$query = 'SELECT `crmid`,`setype`,`smownerid AS moduleName FROM vtiger_crmentity WHERE crmid IN(SELECT `crmid` FROM `u_yf_crmentity_search_label` WHERE `searchlabel` LIKE ?)';
				if ($multiMode) {
					$query .= sprintf(' AND `setype` IN(%s)', $adb->generateQuestionMarks($moduleName));
					$params = array_merge($params, $moduleName);
				} else {
					$query .= ' AND `setype` = ?';
					$params[] = $moduleName;
				}
			}
			$result = $adb->pquery($query, $params);
			while ($row = $adb->getRow($result)) {
				$crmIds[] = $row;
			}
			self::$crmidByLabelCache[$label] = $crmIds;
		}
		return $crmIds;
	}

	static function computeLabels($moduleName, $ids, $search = false)
	{
		$adb = \PearDatabase::getInstance();
		if (!is_array($ids))
			$ids = [$ids];
		if ($moduleName == 'Events') {
			$moduleName = 'Calendar';
		}
		if ($moduleName) {
			$entityDisplay = [];
			if ($ids) {
				if ($moduleName == 'Groups') {
					$metainfo = ['tablename' => 'vtiger_groups', 'entityidfield' => 'groupid', 'fieldname' => 'groupname'];
				} else {
					$metainfo = Functions::getEntityModuleInfo($moduleName);
				}
				$table = $metainfo['tablename'];
				$idcolumn = $metainfo['entityidfield'];
				$columnsName = explode(',', $metainfo['fieldname']);
				$columnsSearch = explode(',', $metainfo['searchcolumn']);
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
				$ids = array_unique($ids);
				$sql = sprintf('SELECT %s AS id FROM %s %s WHERE %s IN (%s)', implode(',', $paramsCol), $table, $leftJoin, $idcolumn, $adb->generateQuestionMarks($ids));
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

	static function updateLabel($moduleName, $id, $mode = 'edit')
	{
		$labelInfo = self::computeLabels($moduleName, $id, true);
		if (!empty($labelInfo)) {
			$adb = \PearDatabase::getInstance();
			$label = decode_html($labelInfo[$id]['name']);
			$search = decode_html($labelInfo[$id]['search']);
			if ($mode == 'edit') {
				$adb->update('u_yf_crmentity_label', ['label' => $label], 'crmid = ?', [$id]);
				$adb->update('u_yf_crmentity_search_label', ['searchlabel' => $search], 'crmid = ?', [$id]);
			} else {
				$adb->insert('u_yf_crmentity_label', ['crmid' => $id, 'label' => $label]);
				$adb->insert('u_yf_crmentity_search_label', ['crmid' => $id, 'searchlabel' => $search]);
			}
			self::$recordLabelCache[$id] = $labelInfo[$id]['name'];
		}
	}
}
