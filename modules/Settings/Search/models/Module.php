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

class Settings_Search_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getModulesEntity($tabid = false, $onlyActive = false)
	{
		$db = PearDatabase::getInstance();
		$params = [];
		if ($onlyActive) {
			$sql = 'SELECT vtiger_entityname.* FROM vtiger_entityname 
				LEFT JOIN vtiger_tab ON vtiger_entityname.tabid = vtiger_tab.tabid 
				WHERE vtiger_tab.presence = ?';
			$params [] = '0';
		} else {
			$sql = 'SELECT * FROM vtiger_entityname';
			if ($tabid) {
				$sql .= ' WHERE tabid = ?';
				$params[] = $tabid;
			}
		}
		$sql .= ' ORDER BY sequence';
		$result = $db->pquery($sql, $params);
		$moduleEntity = [];
		while ($row = $db->getRow($result)) {
			$moduleEntity[$row['tabid']] = $row;
		}
		return $moduleEntity;
	}

	public function getFieldFromModule()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT * from vtiger_field WHERE uitype NOT IN ('15','16','52','53','56','70','120')");
		$fields = array();
		while ($row = $adb->fetch_array($result)) {
			$fields[$row['tabid']][] = $row;
		}
		return $fields;
	}

	public static function compare_vale($actions, $item)
	{
		if (strpos($actions, ',')) {
			$actionsTab = explode(",", $actions);
			if (in_array($item, $actionsTab)) {
				$return = true;
			} else {
				$return = false;
			}
		} else {
			$return = $actions == $item ? true : false;
		}
		return $return;
	}

	public function Save($params)
	{
		$adb = PearDatabase::getInstance();
		$name = $params['name'];

		if ($name == 'searchcolumn' || $name == 'fieldname') {
			$value = implode(',', $params['value']);
			$adb->update('vtiger_entityname', [$name => $value], 'tabid = ?', [(int) $params['tabid']]);
		} elseif ($name == 'turn_off') {
			$adb->update('vtiger_entityname', ['turn_off' => $params['value']], 'tabid = ?', [(int) $params['tabid']]);
		}
	}

	public static function updateLabels($params)
	{
		$adb = PearDatabase::getInstance();
		$tabId = (int) $params['tabid'];
		$modulesEntity = self::getModulesEntity($tabId);
		$moduleEntity = $modulesEntity[$tabId];
		$moduleName = $moduleEntity['modulename'];
		$fieldName = $moduleEntity['fieldname'];
		$searchColumns = $moduleEntity['searchcolumn'];
		$moduleInfo = vtlib\Functions::getModuleFieldInfos($moduleName);
		$columnsEntityName = explode(',', $fieldName);
		$searchColumns = explode(',', $searchColumns);
		$allColumns = array_unique(array_merge($columnsEntityName, $searchColumns));
		$sqlExt = '';
		$entityColumnName = '';
		$searchColumnName = '';

		$moduleInfoExtend = [];
		foreach ($moduleInfo as $field => $fieldInfo) {
			$moduleInfoExtend[$fieldInfo['columnname']] = $fieldInfo;
		}

		foreach ($allColumns as $key => $columnName) {
			$columnNameExt = ',' . $columnName;
			$fieldObiect = $moduleInfoExtend[$columnName];
			if (in_array($fieldObiect['uitype'], [10, 51, 75, 81, 66, 67, 68])) {
				$sqlExt .= " LEFT JOIN (SELECT extj_$key.crmid, extj_$key.label AS ext_$columnName FROM vtiger_crmentity extj_$key) ext_$key ON ext_$key.crmid = " . $fieldObiect['tablename'] . ".$columnName";
				$columnNameExt = ',ext_' . $columnName;
			}
			if (in_array($columnName, $columnsEntityName)) {
				$entityColumnName .= $columnNameExt;
			}
			if (in_array($columnName, $searchColumns)) {
				$searchColumnName .= $columnNameExt;
			}
		}

		$sql = 'UPDATE vtiger_crmentity';
		$sql .= self::getFromClauseByColumn($moduleName, $moduleInfoExtend, $allColumns);
		$sql .= $sqlExt;
		$sql .= ' INNER JOIN u_yf_crmentity_label ON u_yf_crmentity_label.crmid = vtiger_crmentity.crmid';
		$sql .= ' INNER JOIN u_yf_crmentity_search_label ON u_yf_crmentity_search_label.crmid = vtiger_crmentity.crmid';
		$sql .= " SET u_yf_crmentity_label.label = CONCAT_WS(' ' $entityColumnName), u_yf_crmentity_search_label.searchlabel = CONCAT_WS(' ' $searchColumnName)";
		$sql .= " WHERE vtiger_crmentity.setype = '$moduleName'";
		$adb->query($sql);
	}

	public static function getFromClauseByColumn($moduleName, $moduleInfoExtend, $columns)
	{
		$focus = CRMEntity::getInstance($moduleName);
		$tableBase = $focus->table_name;
		$leftJoinTables = [$tableBase];
		$leftJoin = '  LEFT JOIN ' . $tableBase . ' ON vtiger_crmentity.crmid = ' . $tableBase . '.' . $focus->table_index;
		foreach ($columns as $columnName) {
			$table = $moduleInfoExtend[$columnName]['tablename'];
			if (in_array($table, $leftJoinTables)) {
				continue;
			}
			$leftJoinTables[] = $table;
			$focusTables = $focus->tab_name_index;
			$leftJoin .= ' LEFT JOIN ' . $table . ' ON ' . $table . '.' . $focusTables[$table] . ' = ' . $tableBase . '.' . $focusTables[$tableBase];
		}
		return $leftJoin;
	}

	public function updateSequenceNumber($modulesSequence)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_Search_Module_Model::updateSequenceNumber(" . $modulesSequence . ") method ...");
		$tabIdList = array();
		$db = PearDatabase::getInstance();

		$query = 'UPDATE vtiger_entityname SET ';
		$query .=' sequence= CASE ';
		foreach ($modulesSequence as $newModuleSequence) {
			$tabId = $newModuleSequence['tabid'];
			$sequence = $newModuleSequence['sequence'];
			$tabIdList[] = $tabId;
			$query .= ' WHEN tabid=' . $tabId . ' THEN ' . $sequence;
		}

		$query .=' END ';

		$query .= sprintf(' WHERE tabid IN (%s)', generateQuestionMarks($tabIdList));
		$db->pquery($query, [$tabIdList]);
		$log->debug("Exiting Settings_Search_Module_Model::updateSequenceNumber() method ...");
	}
}
