<?php

namespace App;

use vtlib\Functions;

/**
 * Record basic class.
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
	 *
	 * @return mixed
	 */
	public static function getLabel($mixedId)
	{
		$multiMode = is_array($mixedId);
		$ids = $multiMode ? $mixedId : [$mixedId];
		$missing = [];
		foreach ($ids as $id) {
			if ($id && !Cache::has('recordLabel', $id)) {
				$missing[] = $id;
			}
		}
		if (!empty($missing)) {
			$query = (new \App\Db\Query())->select(['crmid', 'label'])->from('u_#__crmentity_label')->where(['crmid' => $missing]);
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				Cache::save('recordLabel', $row['crmid'], $row['label']);
			}
			foreach ($ids as $id) {
				if ($id && !Cache::has('recordLabel', $id)) {
					$metainfo = Functions::getCRMRecordMetadata($id);
					if (!empty($metainfo['setype'])) {
						$computeLabel = static::computeLabels($metainfo['setype'], $id);
						$recordLabel = TextParser::textTruncate(Purifier::encodeHtml($computeLabel[$id] ?? ''), 254, false);
						Cache::save('recordLabel', $id, $recordLabel);
					}
				}
			}
		}
		$result = [];
		foreach ($ids as $id) {
			if (Cache::has('recordLabel', $id)) {
				$result[$id] = Cache::get('recordLabel', $id);
			} else {
				$result[$id] = null;
			}
		}
		return $multiMode ? $result : array_shift($result);
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
	 * @param int|array $ids
	 * @param bool      $search
	 *
	 * @return string[]
	 */
	public static function computeLabels($moduleName, $ids, $search = false)
	{
		if (empty($moduleName) || empty($ids)) {
			return [];
		}
		if (!is_array($ids)) {
			$ids = [$ids];
		}
		$entityDisplay = [];
		$cacheName = 'computeLabelsQuery';
		if (!\App\Cache::staticHas($cacheName, $moduleName)) {
			$metainfo = \App\Module::getEntityInfo($moduleName);
			if (empty($metainfo)) {
				return $entityDisplay;
			}
			$table = $metainfo['tablename'];
			$idColumn = $table . '.' . $metainfo['entityidfield'];
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
					$otherTable = $moduleInfoExtend[$column]['tablename'];

					$paramsCol[] = $otherTable . '.' . $column;
					if ($otherTable !== $table && !in_array($otherTable, $leftJoinTables)) {
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
				if (in_array($moduleInfoExtend[$columnName]['uitype'], [10, 51, 59, 75, 81, 66, 67, 68])) {
					$labelName[] = static::getLabel($row[$columnName]);
				} elseif (in_array($moduleInfoExtend[$columnName]['uitype'], [53])) {
					$labelName[] = \App\Fields\Owner::getLabel($row[$columnName]);
				} else {
					$labelName[] = $row[$columnName];
				}
			}
			if ($search) {
				$labelSearch = [];
				foreach ($columnsSearch as $columnName) {
					if (in_array($moduleInfoExtend[$columnName]['uitype'], [10, 51, 59, 75, 81, 66, 67, 68])) {
						$labelSearch[] = static::getLabel($row[$columnName]);
					} elseif (in_array($moduleInfoExtend[$columnName]['uitype'], [53])) {
						$labelSearch[] = \App\Fields\Owner::getLabel($row[$columnName]);
					} else {
						$labelSearch[] = $row[$columnName];
					}
				}
				$entityDisplay[$row['id']] = ['name' => implode(' ', $labelName), 'search' => implode(' ', $labelSearch)];
			} else {
				$entityDisplay[$row['id']] = trim(implode(' ', $labelName));
			}
		}
		return $entityDisplay;
	}

	public static function updateLabel($moduleName, $id, $insertMode = false, $updater = false)
	{
		$labelInfo = static::computeLabels($moduleName, $id, true);
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (empty($labelInfo)) {
			$dbCommand->delete('u_#__crmentity_label', ['crmid' => $id])->execute();
			$dbCommand->delete('u_#__crmentity_search_label', ['crmid' => $id])->execute();
		} else {
			$label = TextParser::textTruncate(Purifier::decodeHtml($labelInfo[$id]['name']), 254, false);
			$search = TextParser::textTruncate(Purifier::decodeHtml($labelInfo[$id]['search']), 254, false);
			if (!is_numeric($label) && empty($label)) {
				$label = '';
			}
			if (!is_numeric($search) && empty($search)) {
				$search = '';
			}
			if (!$insertMode) {
				$labelRowCount = $dbCommand->update('u_#__crmentity_label', ['label' => $label], ['crmid' => $id])->execute();
				if (!$labelRowCount) {
					$labelRowCount = (new Db\Query())->from('u_#__crmentity_label')->where(['crmid' => $id])->count();
				}
				$searchRowCount = $dbCommand->update('u_#__crmentity_search_label', ['searchlabel' => $search], ['crmid' => $id])->execute();
				if (!$searchRowCount) {
					$searchRowCount = (new Db\Query())->from('u_#__crmentity_search_label')->where(['crmid' => $id])->count();
				}
			}
			if (($insertMode || !$labelRowCount) && $updater !== 'searchlabel') {
				$dbCommand->insert('u_#__crmentity_label', ['crmid' => $id, 'label' => $label])->execute();
			}
			if (($insertMode || !$searchRowCount) && $updater !== 'label') {
				$dbCommand->insert('u_#__crmentity_search_label', ['crmid' => $id, 'searchlabel' => $search, 'setype' => $moduleName])->execute();
			}
			Cache::save('recordLabel', $id, $labelInfo[$id]['name']);
		}
	}

	/**
	 * Update record label on save.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function updateLabelOnSave(\Vtiger_Record_Model $recordModel)
	{
		$metaInfo = \App\Module::getEntityInfo($recordModel->getModuleName());
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
		$label = Purifier::encodeHtml(TextParser::textTruncate(Purifier::decodeHtml(implode(' ', $labelName)), 250, false));
		if (empty($label)) {
			$label = '';
		}
		$search = Purifier::encodeHtml(TextParser::textTruncate(Purifier::decodeHtml(implode(' ', $labelSearch)), 250, false));
		if (empty($search)) {
			$search = '';
		}
		$db = \App\Db::getInstance();
		if ($recordModel->isNew()) {
			$db->createCommand()->insert('u_#__crmentity_label', ['crmid' => $recordModel->getId(), 'label' => $label])->execute();
			$db->createCommand()->insert('u_#__crmentity_search_label', ['crmid' => $recordModel->getId(), 'searchlabel' => $search, 'setype' => $recordModel->getModuleName()])->execute();
		} else {
			$db->createCommand()
				->update('u_#__crmentity_label', ['label' => $label], ['crmid' => $recordModel->getId()])
				->execute();
			$db->createCommand()
				->update('u_#__crmentity_search_label', ['searchlabel' => $search], ['crmid' => $recordModel->getId()])
				->execute();
		}
		Cache::save('recordLabel', $recordModel->getId(), $label);
	}

	/**
	 * Function checks if record exists.
	 *
	 * @param int    $recordId   - Rekord ID
	 * @param string $moduleName
	 *
	 * @return bool
	 */
	public static function isExists($recordId, $moduleName = false)
	{
		$recordMetaData = Functions::getCRMRecordMetadata($recordId);
		return (isset($recordMetaData) && $recordMetaData['deleted'] === 0 && ($moduleName ? $recordMetaData['setype'] === $moduleName : true)) ? true : false;
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
	 * Get record state.
	 *
	 * @param int $recordId
	 *
	 * @return string
	 */
	public static function getState($recordId)
	{
		$metadata = Functions::getCRMRecordMetadata($recordId);
		switch ($metadata['deleted']) {
			default:
			case 0:
				return 'Active';
			case 1:
				return 'Trash';
			case 2:
				return 'Archived';
		}
	}

	/**
	 * Get parent record.
	 *
	 * @param int         $recordId
	 * @param string|bool $moduleName
	 *
	 * @return int|bool
	 */
	public static function getParentRecord($recordId, $moduleName = false)
	{
		if (Cache::has('getParentRecord', $recordId)) {
			return Cache::get('getParentRecord', $recordId);
		}
		if (!$moduleName) {
			$moduleName = static::getType($recordId);
		}
		$parentId = false;
		if ($parentModules = ModuleHierarchy::getModulesMap1M($moduleName)) {
			foreach ($parentModules as $parentModule) {
				if ($fields = Field::getRelatedFieldForModule($moduleName, $parentModule)) {
					$entity = \CRMEntity::getInstance($moduleName);
					$index = $entity->tab_name_index[$fields['tablename']];
					$parentId = (new \App\Db\Query())->select(["{$fields['tablename']}.{$fields['columnname']}"])
						->from($fields['tablename'])
						->innerJoin('vtiger_crmentity', "{$fields['tablename']}.{$index} = vtiger_crmentity.crmid")
						->where(["{$fields['tablename']}.{$index}" => $recordId, 'vtiger_crmentity.deleted' => 0])
						->scalar();
				}
			}
		}
		Cache::save('getParentRecord', $recordId, $parentId);
		return $parentId;
	}
}
