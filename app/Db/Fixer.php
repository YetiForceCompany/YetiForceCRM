<?php

/**
 * File that repaire structure and data in database.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Db;

/**
 * Class that repair structure and data in database.
 */
class Fixer
{
	/**
	 * Add missing entries in vtiger_profile2field.
	 *
	 * @param bool $showMissingElements
	 *
	 * @return int|array
	 */
	public static function profileField(bool $showMissingElements = false)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$i = 0;
		$missingData = [];
		$profileIds = \vtlib\Profile::getAllIds();
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($profileIds as $profileId) {
			$subQuery = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_profile2field')->where(['profileid' => $profileId]);
			$query = (new \App\Db\Query())->select(['tabid', 'fieldid'])->from('vtiger_field')->where(['not in', 'vtiger_field.fieldid', $subQuery]);
			$data = $query->createCommand()->queryAllByGroup(2);
			foreach ($data as $tabId => $fieldIds) {
				foreach ($fieldIds as $fieldId) {
					$isExists = (new \App\Db\Query())->from('vtiger_profile2field')->where(['profileid' => $profileId, 'fieldid' => $fieldId])->exists();
					if (!$isExists) {
						if ($showMissingElements) {
							$instanceOfField = \Vtiger_Field_Model::getInstanceFromFieldId($fieldId);
							$missingData[] = 'Field: ' . $instanceOfField->getName() . ', ProfileId: ' . $profileId . ', TabId :' . $tabId;
						}

						$dbCommand->insert('vtiger_profile2field', ['profileid' => $profileId, 'tabid' => $tabId, 'fieldid' => $fieldId, 'visible' => 0, 'readonly' => 0])->execute();
						++$i;
					}
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);

		return $showMissingElements ? ['count' => $i, 'names' => $missingData] : $i;
	}

	/**
	 * Add missing entries in vtiger_profile2utility.
	 *
	 * @param bool $showMissingElements
	 *
	 * @return int|array
	 */
	public static function baseModuleTools(bool $showMissingElements = false)
	{
		$i = 0;
		$allUtility = $missing = $currentProfile2utility = $missingData = [];
		foreach ((new \App\Db\Query())->from('vtiger_profile2utility')->all() as $row) {
			$currentProfile2utility[$row['profileid']][$row['tabid']][$row['activityid']] = true;
			$allUtility[$row['tabid']][$row['activityid']] = true;
		}
		$profileIds = \vtlib\Profile::getAllIds();
		$moduleIds = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['isentitytype' => 1])->column();
		$baseActionIds = array_map('App\Module::getActionId', \Settings_ModuleManager_Module_Model::$baseModuleTools);
		$exceptions = \Settings_ModuleManager_Module_Model::getBaseModuleToolsExceptions();
		foreach ($profileIds as $profileId) {
			foreach ($moduleIds as $moduleId) {
				foreach ($baseActionIds as $actionId) {
					if (!isset($currentProfile2utility[$profileId][$moduleId][$actionId])) {
						$missing["$profileId:$moduleId:$actionId"] = ['profileid' => $profileId, 'tabid' => $moduleId, 'activityid' => $actionId];
					}
				}
				if (isset($allUtility[$moduleId])) {
					foreach ($allUtility[$moduleId] as $actionId => $value) {
						if (!isset($currentProfile2utility[$profileId][$moduleId][$actionId])) {
							$missing["$profileId:$moduleId:$actionId"] = ['profileid' => $profileId, 'tabid' => $moduleId, 'activityid' => $actionId];
						}
					}
				}
			}
		}

		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($missing as $row) {
			if (isset($exceptions[$row['tabid']]['allowed'])) {
				if (!isset($exceptions[$row['tabid']]['allowed'][$row['activityid']])) {
					continue;
				}
			} elseif (isset($exceptions[$row['tabid']]['notAllowed']) && (false === $exceptions[$row['tabid']]['notAllowed'] || isset($exceptions[$row['tabid']]['notAllowed'][$row['activityid']]))) {
				continue;
			}

			if ($showMissingElements) {
				$nameOfModule = \App\Module::getModuleName($row['tabid']);
				$missingData[] = 'Module: ' . $nameOfModule . ', ProfileId: ' . $row['profileid'] . ', ActivityId:' . $row['activityid'];
			}

			$dbCommand->insert('vtiger_profile2utility', ['profileid' => $row['profileid'], 'tabid' => $row['tabid'], 'activityid' => $row['activityid'], 'permission' => 1])->execute();
			++$i;
		}

		return $showMissingElements ? ['count' => $i, 'names' => $missingData] : $i;
	}

	/**
	 * Add missing entries in vtiger_profile2standardpermissions.
	 *
	 * @param bool $showMissingElements
	 *
	 * @return int|array
	 */
	public static function baseModuleActions(bool $showMissingElements = false)
	{
		$i = 0;
		$curentProfile = $missingData = [];
		foreach ((new \App\Db\Query())->from('vtiger_profile2standardpermissions')->all() as $row) {
			$curentProfile[$row['profileid']][$row['tabid']][$row['operation']] = $row['permissions'];
		}
		$moduleIds = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['isentitytype' => 1])->column();
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach (\vtlib\Profile::getAllIds() as $profileId) {
			foreach ($moduleIds as $moduleId) {
				foreach (\Vtiger_Action_Model::$standardActions as $actionId => $actionName) {
					if (!isset($curentProfile[$profileId][$moduleId][$actionId])) {
						if ($showMissingElements) {
							$nameOfAction = \Vtiger_Action_Model::getInstanceWithIdOrName($actionId);
							$missingData[] = 'Action: ' . $nameOfAction . ', ProfileId: ' . $profileId . ', TabId: ' . $moduleId . ', ActionId: ' . $actionId;
						}

						$dbCommand->insert('vtiger_profile2standardpermissions', ['profileid' => $profileId, 'tabid' => $moduleId, 'operation' => $actionId, 'permissions' => 1])->execute();
						++$i;
					}
				}
			}
		}

		return $showMissingElements ? ['count' => $i, 'names' => $missingData] : $i;
	}

	/**
	 * Fixes the maximum value allowed for fields.
	 *
	 * @param array $conditions Additional query conditions
	 *
	 * @return int[]
	 */
	public static function maximumFieldsLength(array $conditions = []): array
	{
		$typesNotSupported = ['datetime', 'date', 'year', 'timestamp', 'time'];
		$uiTypeNotSupported = [30];
		$updatedInfo = $requiresVerificationInfo = [];
		$updated = $requiresVerification = $typeNotFound = $notSupported = 0;
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$schema = $db->getSchema();
		$query = (new \App\Db\Query())->select(['tabid', 'tablename', 'columnname', 'fieldid', 'fieldname', 'maximumlength', 'uitype'])->from('vtiger_field');
		if ($conditions) {
			$query->andWhere($conditions);
		}
		$dataReader = $query->createCommand()->query();
		while ($field = $dataReader->read()) {
			$column = $schema->getTableSchema($field['tablename'])->columns[$field['columnname']];
			preg_match('/^([\w\-]+)/i', $column->dbType, $matches);
			$type = $matches[1] ?? $column->type;
			if (\in_array($type, $typesNotSupported) || \in_array($field['uitype'], $uiTypeNotSupported)) {
				++$notSupported;
				continue;
			}
			if (isset(\Vtiger_Field_Model::$uiTypeMaxLength[$field['uitype']])) {
				$range = \Vtiger_Field_Model::$uiTypeMaxLength[$field['uitype']];
			} elseif (isset(\Vtiger_Field_Model::$typesMaxLength[$type])) {
				$range = \Vtiger_Field_Model::$typesMaxLength[$type];
			} else {
				switch ($type) {
					case 'binary':
					case 'string':
					case 'varchar':
					case 'varbinary':
						$range = (int) $column->size;
						break;
					case 'bigint':
					case 'mediumint':
						\App\Log::error("Type not allowed: {$field['tablename']}.{$field['columnname']} |uitype: {$field['uitype']} |maximumlength: {$field['maximumlength']} |type:{$type}|{$column->type}|{$column->dbType}", __METHOD__);
						break;
					case 'integer':
					case 'int':
						if ($column->unsigned) {
							$range = '4294967295';
							if (7 == $field['uitype'] || 1 == $field['uitype']) {
								$range = '0,' . $range;
							}
						} else {
							$range = '-2147483648,2147483647';
						}
						break;
					case 'smallint':
						if ($column->unsigned) {
							$range = '65535';
							if (7 == $field['uitype'] || 1 == $field['uitype']) {
								$range = '0,' . $range;
							}
						} else {
							$range = '-32768,32767';
						}
						break;
					case 'tinyint':
						if ($column->unsigned) {
							$range = '255';
							if (7 == $field['uitype'] || 1 == $field['uitype']) {
								$range = '0,' . $range;
							}
						} else {
							$range = '-128,127';
						}
						break;
					case 'decimal':
						$range = 10 ** (((int) $column->size) - ((int) $column->scale)) - 1;
						if ($column->unsigned) {
							$range = '0,' . $range;
						}
						break;
					default:
						$range = false;
						break;
				}
			}
			$update = false;
			if (false === $range) {
				\App\Log::warning("Type not found: {$field['tablename']}.{$field['columnname']} |uitype: {$field['uitype']} |maximumlength: {$field['maximumlength']} |type:{$type}|{$column->type}|{$column->dbType}", __METHOD__);
				++$typeNotFound;
			} elseif ($field['maximumlength'] != $range) {
				if (\in_array($field['uitype'], [1, 2, 7, 9, 10, 16, 52, 53, 56, 71, 72, 120, 156, 300, 308, 317, 327])) {
					$update = true;
				} else {
					\App\Log::warning("Requires verification: {$field['tablename']}.{$field['columnname']} |uitype: {$field['uitype']} |maximumlength: {$field['maximumlength']} <> {$range} |type:{$type}|{$column->type}|{$column->dbType}", __METHOD__);
					$requiresVerificationInfo['fields'][] = 'FieldId: ' . $field['fieldid'] . ', FieldName: ' . $field['fieldname'] . ', TabId: ' . $field['tabid'] . ', TabName: ' . \App\Module::getModuleName($field['tabid']) . ", Diff: {$field['maximumlength']} <> {$range} | {$type}|{$column->type}|{$column->dbType}";
					++$requiresVerification;
				}
			}
			if ($update && false !== $range) {
				$dbCommand->update('vtiger_field', ['maximumlength' => $range], ['fieldid' => $field['fieldid']])->execute();
				$updatedInfo['fields'][] = 'FieldId: ' . $field['fieldid'] . ', FieldName: ' . $field['fieldname'] . ', TabId: ' . $field['tabid'] . ', TabName: ' . \App\Module::getModuleName($field['tabid']);
				++$updated;
				\App\Log::trace("Updated: {$field['tablename']}.{$field['columnname']} |maximumlength:  before:{$field['maximumlength']} after: $range |type:{$type}|{$column->type}|{$column->dbType}", __METHOD__);
			}
		}
		return ['NotSupported' => $notSupported, 'TypeNotFound' => $typeNotFound, 'RequiresVerification' => $requiresVerification, 'RequiresVerificationInfo' => $requiresVerificationInfo,  'Updated' => $updated, 'UpdatedInfo' => $updatedInfo];
	}

	/**
	 * Add missing entries in vtiger_def_org_share and vtiger_org_share_action2tab.
	 *
	 * @return int
	 */
	public static function share(): int
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$i = 0;
		$dbCommand = \App\Db::getInstance()->createCommand();
		$query = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['isentitytype' => 1])
			->andWhere(['not in', 'tabid', (new \App\Db\Query())->select(['tabid'])->from('vtiger_def_org_share')]);
		foreach ($query->column() as $tabId) {
			$dbCommand->insert('vtiger_def_org_share', ['tabid' => $tabId, 'permission' => 3, 'editstatus' => 0])->execute();
			++$i;
		}
		$actionIds = (new \App\Db\Query())->select(['share_action_id'])->from('vtiger_org_share_action_mapping')
			->where(['share_action_name' => ['Public: Read Only', 'Public: Read, Create/Edit', 'Public: Read, Create/Edit, Delete', 'Private']])
			->column();
		$query = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['isentitytype' => 1])
			->andWhere(['not in', 'tabid', (new \App\Db\Query())->select(['tabid'])->from('vtiger_org_share_action2tab')]);
		foreach ($query->column() as $tabId) {
			$insertedData = [];
			foreach ($actionIds as $id) {
				$insertedData[] = [$id, $tabId];
			}
			$dbCommand->batchInsert('vtiger_org_share_action2tab', ['share_action_id', 'tabid'], $insertedData)->execute();
			++$i;
		}
		\App\Log::trace('Exiting ' . __METHOD__);
		return $i;
	}
}
