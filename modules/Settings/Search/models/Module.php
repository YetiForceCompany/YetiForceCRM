<?php

/**
 * Settings search Module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Search_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Get entity modules.
	 *
	 * @param int  $tabId
	 * @param bool $onlyActive
	 *
	 * @return array
	 */
	public static function getModulesEntity($tabId = false, $onlyActive = false)
	{
		$query = (new \App\Db\Query());
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
		$dataReader->close();

		return $moduleEntity;
	}

	/**
	 * Get fields.
	 *
	 * @return array
	 */
	public static function getFieldFromModule()
	{
		$fields = [];
		$dataReader = (new \App\Db\Query())->select(['columnname', 'tabid', 'fieldlabel'])->from('vtiger_field')->where(['not in', 'uitype', [15, 16, 52, 53, 56, 70, 99, 120]])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$fields[$row['tabid']][$row['columnname']] = $row;
		}
		$dataReader->close();

		return $fields;
	}

	/**
	 * Save parameters.
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public static function save($params)
	{
		$db = App\Db::getInstance();
		$name = $params['name'];
		$fields = self::getFieldFromModule();
		$tabId = (int) $params['tabid'];
		if ($name === 'searchcolumn' || $name === 'fieldname') {
			foreach ($params['value'] as $field) {
				if (!isset($fields[$tabId][$field])) {
					return false;
				}
			}
			$db->createCommand()
				->update('vtiger_entityname', [$name => implode(',', $params['value'])], ['tabid' => $tabId])
				->execute();
		} elseif ($name === 'turn_off') {
			$db->createCommand()
				->update('vtiger_entityname', ['turn_off' => $params['value']], ['tabid' => $tabId])
				->execute();
		}

		return true;
	}

	/**
	 * Update labels.
	 *
	 * @param array $params
	 */
	public static function updateLabels($params)
	{
		$moduleName = App\Module::getModuleName((int) $params['tabid']);
		$db = App\Db::getInstance();
		$db->createCommand()->update('u_#__crmentity_search_label', ['searchlabel' => ''], ['setype' => $moduleName])->execute();
		$subQuery = (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentity')->where(['setype' => $moduleName]);
		$db->createCommand()->delete('u_#__crmentity_label', ['crmid' => $subQuery])->execute();
	}

	/**
	 * Update sequence number.
	 *
	 * @param array $modulesSequence
	 */
	public static function updateSequenceNumber($modulesSequence)
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
