<?php

/**
 * Settings search Module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
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

	/**
	 * Get fields
	 * @return array
	 */
	public function getFieldFromModule()
	{
		$fields = [];
		$dataReader = (new \App\Db\Query())->select(['columnname', 'tabid', 'fieldlabel'])->from('vtiger_field')->where(['not in', 'uitype', [15, 16, 52, 53, 56, 70, 120]])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$fields[$row['tabid']][$row['columnname']] = $row;
		}
		return $fields;
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

	/**
	 * Update sequence number
	 * @param array $modulesSequence
	 */
	public function updateSequenceNumber($modulesSequence)
	{
		\App\Log::trace('Entering Settings_Search_Module_Model::updateSequenceNumber() method ...');
		$tabIdList = [];
		$db = App\Db::getInstance();
		$case = ' CASE ';
		foreach ($modulesSequence as $newModuleSequence) {
			$tabId = $newModuleSequence['tabid'];
			$tabIdList[] = $tabId;
			$case .= " WHEN tabid = {$db->quoteValue($tabId)} THEN {$db->quoteValue($newModuleSequence['sequence'])}";
		}
		$case .= ' END ';
		$db->createCommand()->update('vtiger_entityname', ['sequence' => new yii\db\Expression($case)], ['tabid' => $tabIdList])->execute();
		\App\Log::trace('Exiting Settings_Search_Module_Model::updateSequenceNumber() method ...');
	}
}
