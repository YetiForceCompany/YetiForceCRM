<?php

/**
 * Settings search Module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$query->orderBy('vtiger_entityname.sequence');
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
	 * @param mixed $blocks
	 *
	 * @return array
	 */
	public static function getFieldFromModule($blocks = true)
	{
		$fields = [];
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.tabid', 'vtiger_field.columnname', 'vtiger_field.fieldlabel', 'vtiger_blocks.blocklabel'])
			->from('vtiger_field')
			->innerJoin('vtiger_blocks', 'vtiger_blocks.blockid = vtiger_field.block')
			->where(['not in', 'uitype', [15, 16, 52, 53, 56, 70, 99, 120]])
			->andWhere(['presence' => [0, 2]])
			->createCommand()
			->query();
		while ($row = $dataReader->read()) {
			if ($blocks) {
				$fields[$row['tabid']][$row['blocklabel']][$row['columnname']] = $row;
			} else {
				$fields[$row['tabid']][$row['columnname']] = $row;
			}
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
		$tabId = (int) $params['tabid'];
		if ('searchcolumn' === $name || 'fieldname' === $name) {
			if (empty($params['value']) || array_diff($params['value'], array_keys(self::getFieldFromModule(false)[$tabId]))) {
				throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE');
			}
			$db->createCommand()
				->update('vtiger_entityname', [$name => implode(',', $params['value'])], ['tabid' => $tabId])
				->execute();
		} elseif ('turn_off' === $name) {
			$db->createCommand()
				->update('vtiger_entityname', ['turn_off' => (int) $params['value']], ['tabid' => $tabId])
				->execute();
		}
		\App\Cache::delete('ModuleEntityInfo', '');
		return true;
	}

	/**
	 * Update labels.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public static function updateLabels($params): void
	{
		$moduleName = App\Module::getModuleName((int) $params['tabid']);
		$db = App\Db::getInstance();
		if ('Users' === $moduleName) {
			(new \App\BatchMethod(['method' => '\App\User::updateLabels', 'params' => [0]]))->save();
		} else {
			$db->createCommand()->delete('u_#__crmentity_search_label', ['tabid' => $params['tabid']])->execute();
			$subQuery = (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentity')->where(['setype' => $moduleName]);
			$db->createCommand()->delete('u_#__crmentity_label', ['crmid' => $subQuery])->execute();
		}
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
