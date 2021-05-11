<?php

namespace App;

use vtlib\Functions;

/**
 * Record basic class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Record
{
	/**
	 * Get label.
	 *
	 * @param mixed $mixedId
	 * @param bool  $raw
	 *
	 * @return mixed
	 */
	public static function getLabel($mixedId, bool $raw = false)
	{
		$multiMode = \is_array($mixedId);
		$ids = array_filter($multiMode ? array_unique($mixedId) : [$mixedId]);
		$result = $missing = [];
		foreach ($ids as $id) {
			if (!Cache::has('recordLabel', $id)) {
				$missing[$id] = $id;
			} else {
				$result[$id] = Cache::get('recordLabel', $id);
			}
		}
		if (!empty($missing)) {
			$query = (new \App\Db\Query())->select(['crmid', 'label'])->from('u_#__crmentity_label')->where(['crmid' => $missing]);
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				Cache::save('recordLabel', $row['crmid'], $row['label']);
				$result[$row['crmid']] = $row['label'];
			}
			foreach (array_diff_key($missing, $result) as $id) {
				$metaInfo = Functions::getCRMRecordMetadata($id);
				$computeLabel = static::computeLabels($metaInfo['setype'] ?? '', $id)[$id] ?? '';
				Cache::save('recordLabel', $id, $computeLabel);
				$result[$id] = $computeLabel;
			}
		}
		if (!$raw && $result) {
			$result = array_map('\App\Purifier::encodeHtml', $result);
		}
		return $multiMode ? $result : reset($result);
	}

	/**
	 * Function searches for record ID with given label.
	 *
	 * @param string $moduleName
	 * @param string $label
	 * @param int    $userId
	 *
	 * @return int
	 */
	public static function getCrmIdByLabel($moduleName, $label, $userId = false)
	{
		$key = $moduleName . $label . '_' . $userId;
		if (\App\Cache::staticHas(__METHOD__, $key)) {
			return \App\Cache::staticGet(__METHOD__, $key);
		}
		$query = (new \App\Db\Query())
			->select(['cl.crmid'])
			->from('u_#__crmentity_label cl')
			->innerJoin('vtiger_crmentity', 'cl.crmid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.setype' => $moduleName])
			->andWhere(['cl.label' => $label]);
		if ($userId) {
			$query->andWhere(['like', 'vtiger_crmentity.users', ",$userId,"]);
		}
		$crmId = $query->limit(1)->scalar();
		\App\Cache::staticSave(__METHOD__, $key, $crmId);

		return $crmId;
	}

	/**
	 * Function gets labels for record data.
	 *
	 * @param string    $moduleName
	 * @param array|int $ids
	 * @param bool      $search
	 *
	 * @return string[]|null
	 */
	public static function computeLabels($moduleName, $ids, $search = false): ?array
	{
		if (empty($moduleName) || empty($ids)) {
			return [];
		}
		$metaInfo = \App\Module::getEntityInfo($moduleName);
		if (!$metaInfo || (empty($metaInfo['fieldnameArr']) && empty($metaInfo['searchcolumnArr']))) {
			return null;
		}
		if (!\is_array($ids)) {
			$ids = [$ids];
		}
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$entityDisplay = [];
		$cacheName = 'computeLabelsQuery';
		if (!\App\Cache::staticHas($cacheName, $moduleName)) {
			$table = $metaInfo['tablename'];
			$idColumn = $table . '.' . $metaInfo['entityidfield'];
			$columnsName = $metaInfo['fieldnameArr'];
			$columnsSearch = $metaInfo['searchcolumnArr'];
			$columns = array_unique(array_merge($columnsName, $columnsSearch));
			$leftJoinTables = $paramsCol = [];
			$query = new \App\Db\Query();
			$focus = $moduleModel->getEntityInstance();
			$moduleInfoExtend = Functions::getModuleFieldInfos($moduleName, true);
			foreach (array_filter($columns) as $column) {
				if (\array_key_exists($column, $moduleInfoExtend)) {
					$otherTable = $moduleInfoExtend[$column]['tablename'];
					$paramsCol[] = $otherTable . '.' . $column;
					if ($otherTable !== $table && !\in_array($otherTable, $leftJoinTables)) {
						$leftJoinTables[] = $otherTable;
						$focusTables = $focus->tab_name_index;
						$query->leftJoin($otherTable, "$table.$focusTables[$table] = $otherTable.$focusTables[$otherTable]");
					}
				}
			}
			$paramsCol['id'] = $idColumn;
			$query->select($paramsCol)->from($table);
			\App\Cache::staticSave($cacheName, $moduleName, clone $query);
		} else {
			$query = \App\Cache::staticGet($cacheName, $moduleName);
			$columnsName = $metaInfo['fieldnameArr'];
			$columnsSearch = $metaInfo['searchcolumnArr'];
			$idColumn = $metaInfo['entityidfield'];
		}
		$separator = $metaInfo['separator'] ?? ' ';
		$ids = array_unique($ids);
		$query->where([$idColumn => $ids]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$recordId = $row['id'];
			$labelName = [];
			foreach ($columnsName as $columnName) {
				$fieldModel = $moduleModel->getFieldByColumn($columnName);
				$labelName[] = $fieldModel ? $fieldModel->getDisplayValue($row[$columnName], $recordId, false, true) : '';
			}
			$label = TextParser::textTruncate(trim(implode($separator, $labelName)), 250, false);
			if ($search) {
				$labelName = [];
				foreach ($columnsSearch as $columnName) {
					$fieldModel = $moduleModel->getFieldByColumn($columnName);
					$labelName[] = $fieldModel ? $fieldModel->getDisplayValue($row[$columnName], $recordId, false, true) : '';
				}
				$searchLabel = TextParser::textTruncate(trim(implode($separator, $labelName)), 250, false);
				$entityDisplay[$recordId] = ['name' => $label, 'search' => $searchLabel];
			} else {
				$entityDisplay[$recordId] = $label;
			}
		}
		return $entityDisplay;
	}

	/**
	 * Update record label.
	 *
	 * @param string      $moduleName
	 * @param int         $id
	 * @param bool        $insertMode
	 * @param string|null $updater
	 *
	 * @return void
	 */
	public static function updateLabel(string $moduleName, int $id, bool $insertMode = false, ?string $updater = null): void
	{
		$metaInfo = static::computeLabels($moduleName, $id, true);
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (!$metaInfo) {
			$dbCommand->delete('u_#__crmentity_label', ['crmid' => $id])->execute();
			$dbCommand->delete('u_#__crmentity_search_label', ['crmid' => $id])->execute();
		} else {
			$label = $metaInfo[$id]['name'];
			$search = $metaInfo[$id]['search'];
			if (!$insertMode) {
				if (!($labelRowCount = $dbCommand->update('u_#__crmentity_label', ['label' => $label], ['crmid' => $id])->execute())) {
					$labelRowCount = (new Db\Query())->from('u_#__crmentity_label')->where(['crmid' => $id])->count();
				}
				if (!($searchRowCount = $dbCommand->update('u_#__crmentity_search_label', ['searchlabel' => $search], ['crmid' => $id])->execute())) {
					$searchRowCount = (new Db\Query())->from('u_#__crmentity_search_label')->where(['crmid' => $id])->count();
				}
			}
			if (($insertMode || !$labelRowCount) && 'searchlabel' !== $updater) {
				$dbCommand->insert('u_#__crmentity_label', ['crmid' => $id, 'label' => $label])->execute();
			}
			if (($insertMode || !$searchRowCount) && 'label' !== $updater) {
				$dbCommand->insert('u_#__crmentity_search_label', ['crmid' => $id, 'searchlabel' => $search, 'tabid' => \App\Module::getModuleId($moduleName)])->execute();
			}
			Cache::save('recordLabel', $id, $metaInfo[$id]['name']);
		}
	}

	/**
	 * Update record label on save.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	public static function updateLabelOnSave(\Vtiger_Record_Model $recordModel): void
	{
		$label = '';
		$metaInfo = \App\Module::getEntityInfo($recordModel->getModuleName());
		$dbCommand = Db::getInstance()->createCommand();
		if (!$metaInfo || (empty($metaInfo['fieldnameArr']) && empty($metaInfo['searchcolumnArr']))) {
			$dbCommand->delete('u_#__crmentity_label', ['crmid' => $recordModel->getId()])->execute();
			$dbCommand->delete('u_#__crmentity_search_label', ['crmid' => $recordModel->getId()])->execute();
		} else {
			$separator = $metaInfo['separator'] ?? ' ';
			$labelName = [];
			foreach ($metaInfo['fieldnameArr'] as $columnName) {
				$fieldModel = $recordModel->getModule()->getFieldByColumn($columnName);
				$labelName[] = $fieldModel->getDisplayValue($recordModel->get($fieldModel->getName()), $recordModel->getId(), $recordModel, true);
			}
			$labelSearch = [];
			foreach ($metaInfo['searchcolumnArr'] as $columnName) {
				$fieldModel = $recordModel->getModule()->getFieldByColumn($columnName);
				$labelSearch[] = $fieldModel->getDisplayValue($recordModel->get($fieldModel->getName()), $recordModel->getId(), $recordModel, true);
			}
			$label = TextParser::textTruncate(trim(implode($separator, $labelName)), 250, false) ?: '';
			$search = TextParser::textTruncate(trim(implode($separator, $labelSearch)), 250, false) ?: '';
			if ($recordModel->isNew()) {
				$dbCommand->insert('u_#__crmentity_label', ['crmid' => $recordModel->getId(), 'label' => $label])->execute();
				$dbCommand->insert('u_#__crmentity_search_label', ['crmid' => $recordModel->getId(), 'searchlabel' => $search, 'tabid' => $recordModel->getModuleName()])->execute();
			} else {
				$dbCommand->update('u_#__crmentity_label', ['label' => $label], ['crmid' => $recordModel->getId()])->execute();
				$dbCommand->update('u_#__crmentity_search_label', ['searchlabel' => $search], ['crmid' => $recordModel->getId()])->execute();
			}
		}
		$recordModel->label = \App\Purifier::encodeHtml($label);
		Cache::save('recordLabel', $recordModel->getId(), $label);
	}

	/**
	 * Function checks if record exists.
	 *
	 * @param int    $recordId   - Record ID
	 * @param string $moduleName
	 *
	 * @return bool
	 */
	public static function isExists($recordId, $moduleName = false)
	{
		$recordMetaData = Functions::getCRMRecordMetadata($recordId);
		return (isset($recordMetaData) && 1 !== $recordMetaData['deleted'] && ($moduleName ? $recordMetaData['setype'] === $moduleName : true)) ? true : false;
	}

	/**
	 * Get record module name.
	 *
	 * @param int $recordId
	 *
	 * @return string|null
	 */
	public static function getType($recordId)
	{
		$metadata = Functions::getCRMRecordMetadata($recordId);
		return $metadata ? $metadata['setype'] : null;
	}

	/**
	 * Get the currency ID for the inventory record.
	 *
	 * @param int    $recordId
	 * @param string $moduleName
	 *
	 * @return int|null
	 */
	public static function getCurrencyIdFromInventory(int $recordId, string $moduleName): ?int
	{
		$invData = \Vtiger_Inventory_Model::getInventoryDataById($recordId, $moduleName);
		return current($invData)['currency'] ?? Fields\Currency::getDefault()['id'] ?? null;
	}

	/**
	 * Get record state.
	 *
	 * @param int $recordId
	 *
	 * @return string
	 */
	public static function getState($recordId)
	{
		$metadata = Functions::getCRMRecordMetadata($recordId);
		switch ($metadata['deleted'] ?? 3) {
			case 0:
				$state = 'Active';
				break;
			case 1:
				$state = 'Trash';
				break;
			case 2:
				$state = 'Archived';
				break;
			default:
				$state = null;
		}
		return $state;
	}

	/**
	 * Get parent record.
	 *
	 * @param int         $recordId
	 * @param bool|string $moduleName
	 *
	 * @return bool|int
	 */
	public static function getParentRecord($recordId, $moduleName = false)
	{
		if (Cache::has(__METHOD__, $recordId)) {
			return Cache::get(__METHOD__, $recordId);
		}
		if (!$moduleName) {
			$moduleName = static::getType($recordId);
		}
		$parentId = false;
		if ($parentModules = ModuleHierarchy::getModulesMap1M($moduleName)) {
			foreach ($parentModules as $parentModule) {
				if ($field = Field::getRelatedFieldForModule($moduleName, $parentModule)) {
					$entity = \CRMEntity::getInstance($moduleName);
					$index = $entity->tab_name_index[$field['tablename']];
					$parentId = (new \App\Db\Query())->select(["{$field['tablename']}.{$field['columnname']}"])
						->from($field['tablename'])
						->innerJoin('vtiger_crmentity', "{$field['tablename']}.{$index} = vtiger_crmentity.crmid")
						->where(["{$field['tablename']}.{$index}" => $recordId, 'vtiger_crmentity.deleted' => 0])
						->scalar();
				}
			}
		}
		Cache::save(__METHOD__, $recordId, $parentId);
		return $parentId;
	}

	/**
	 * Get record id by record number .
	 *
	 * @param string $recordNumber
	 * @param string $moduleName
	 *
	 * @return int|bool
	 */
	public static function getIdByRecordNumber(string $recordNumber, string $moduleName)
	{
		if (Cache::staticHas(__METHOD__, $recordNumber)) {
			return Cache::staticGet(__METHOD__, $recordNumber);
		}
		$field = Fields\RecordNumber::getSequenceNumberField(Module::getModuleId($moduleName));
		$entity = \CRMEntity::getInstance($moduleName);
		$index = $entity->tab_name_index[$field['tablename']];
		$id = (new \App\Db\Query())->select(['vtiger_crmentity.crmid'])
			->from($field['tablename'])
			->innerJoin('vtiger_crmentity', "{$field['tablename']}.{$index} = vtiger_crmentity.crmid")
			->where(["{$field['tablename']}.{$field['columnname']}" => $recordNumber, 'vtiger_crmentity.deleted' => 0])
			->scalar();
		Cache::staticSave(__METHOD__, $recordNumber, $id);
		return $id;
	}
}
