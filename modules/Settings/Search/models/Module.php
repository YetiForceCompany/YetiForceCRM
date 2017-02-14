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

	public static function getModulesEntity($tabId = false, $onlyActive = false)
	{
		$query = (new \App\Db\Query);
		if ($onlyActive) {
			$query->select(['vtiger_entityname.*'])->from('vtiger_entityname')->leftJoin('vtiger_tab', 'vtiger_entityname.tabid = vtiger_tab.tabid')
				->where(['vtiger_tab.presence' => 0]);
		} else {
			$query->from(('vtiger_entityname'));

			if ($tabId) {
				$query->where(['tabid' => $tabId]);
			}
		}
		$query->orderBy('sequence');
		$dataReader = $query->createCommand()->query();
		$moduleEntity = [];
		while ($row = $dataReader->read()) {
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

	public static function save($params)
	{
		$db = App\Db::getInstance();
		$name = $params['name'];

		if ($name == 'searchcolumn' || $name == 'fieldname') {
			$value = implode(',', $params['value']);
			$db->createCommand()
				->update('vtiger_entityname', [$name => $value], ['tabid' => (int) $params['tabid']])
				->execute();
		} elseif ($name == 'turn_off') {
			$db->createCommand()
				->update('vtiger_entityname', ['turn_off' => $params['value']], ['tabid' => (int) $params['tabid']])
				->execute();
		}
	}

	public static function updateLabels($params)
	{
		$moduleName = App\Module::getModuleName((int) $params['tabid']);
		$db = App\Db::getInstance();
		$db->createCommand()->update('u_#__crmentity_search_label', ['searchlabel' => ''], ['setype' => $moduleName])->execute();
		$subQuery = (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentity')->where(['setype' => $moduleName]);
		$db->createCommand()->delete('u_#__crmentity_label', ['crmid' => $subQuery])->execute();
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
		
		\App\Log::trace("Entering Settings_Search_Module_Model::updateSequenceNumber(" . $modulesSequence . ") method ...");
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
		\App\Log::trace("Exiting Settings_Search_Module_Model::updateSequenceNumber() method ...");
	}
}
