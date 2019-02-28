<?php

namespace App;

/**
 * Field basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Field
{
	/**
	 * Help info views.
	 */
	const HELP_INFO_VIEWS = ['LBL_EDIT_VIEW' => 'Edit', 'LBL_DETAIL_VIEW' => 'Detail', 'LBL_QUICK_CREATE_VIEW' => 'QuickCreateAjax'];

	/**
	 * Function gets the list of fields that the user has permissions to.
	 *
	 * @param int  $tabId    Module ID
	 * @param type $readOnly Read/preview only fields
	 *
	 * @return array
	 */
	public static function getFieldsPermissions($tabId, $readOnly = true)
	{
		Log::trace('Entering ' . __METHOD__ . ": $tabId");
		if (Cache::has(__METHOD__ . User::getCurrentUserId(), $tabId)) {
			$fields = Cache::get(__METHOD__ . User::getCurrentUserId(), $tabId);
		} else {
			$query = (new \App\Db\Query())
				->select([
					'vtiger_field.fieldid',
					'vtiger_field.fieldname',
					'vtiger_field.columnname',
					'vtiger_profile2field.readonly',
					'vtiger_profile2field.visible'
				])
				->from('vtiger_field')
				->innerJoin('vtiger_profile2field', 'vtiger_profile2field.fieldid = vtiger_field.fieldid')
				->where([
					'vtiger_field.tabid' => (int) $tabId,
					'vtiger_profile2field.visible' => 0,
					'vtiger_field.visible' => 0,
					'vtiger_field.presence' => [0, 2]
				]);
			$profileList = \App\User::getCurrentUserModel()->getProfiles();
			if ($profileList) {
				$query->andWhere(['vtiger_profile2field.profileid' => $profileList]);
			}
			$fields = $query->distinct()->indexBy('fieldname')->all();
			Cache::save(__METHOD__ . User::getCurrentUserId(), $tabId, $fields);
		}

		if ($readOnly) {
			return $fields;
		}
		foreach ($fields as $key => $field) {
			if ($field['readonly']) {
				unset($fields[$key]);
			}
		}
		return $fields;
	}

	private static $fieldPermissionCacheRead = [];
	private static $fieldPermissionCacheWrite = [];

	/**
	 * Function checks field permissions by field name or field id.
	 *
	 * @param int|string $tabMix   Module ID or module name
	 * @param int|string $fieldMix Field ID or field name
	 * @param bool       $readOnly Read/preview only fields
	 *
	 * @return bool
	 */
	public static function getFieldPermission($tabMix, $fieldMix, $readOnly = true)
	{
		$tabId = $tabMix;
		if (!is_numeric($tabMix)) {
			$tabId = Module::getModuleId($tabMix);
		}
		Log::trace('Entering ' . __METHOD__ . ": $tabId,$fieldMix");
		if ($readOnly && isset(self::$fieldPermissionCacheRead[$tabId][$fieldMix])) {
			return self::$fieldPermissionCacheRead[$tabId][$fieldMix];
		} elseif (!$readOnly && isset(self::$fieldPermissionCacheWrite[$tabId][$fieldMix])) {
			return self::$fieldPermissionCacheWrite[$tabId][$fieldMix];
		}
		$fields = static::getFieldsPermissions($tabId, $readOnly);
		if (is_numeric($fieldMix)) {
			$key = 'fieldid';
			$fieldMix = (int) $fieldMix;
		} else {
			$key = 'fieldname';
		}
		foreach ($fields as &$field) {
			if ($field[$key] === $fieldMix) {
				$permission = !($field['visible']);
				if ($readOnly) {
					self::$fieldPermissionCacheRead[$tabId][$fieldMix] = $permission;
					self::$columnPermissionCacheRead[$tabId][$field['columnname']] = $permission;
				} else {
					self::$fieldPermissionCacheWrite[$tabId][$fieldMix] = $permission;
					self::$columnPermissionCacheWrite[$tabId][$field['columnname']] = $permission;
				}

				return $permission;
			}
		}
		if ($readOnly) {
			self::$fieldPermissionCacheRead[$tabId][$fieldMix] = false;
		} else {
			self::$fieldPermissionCacheWrite[$tabId][$fieldMix] = false;
		}
		return false;
	}

	private static $columnPermissionCacheRead = [];
	private static $columnPermissionCacheWrite = [];

	/**
	 * Function checks field permissions by column name.
	 *
	 * @param int|string $tabMix     Module ID or module name
	 * @param string     $columnName Field ID or field name
	 * @param bool       $readOnly   Read/preview only fields
	 *
	 * @return bool
	 */
	public static function getColumnPermission($tabMix, $columnName, $readOnly = true)
	{
		$tabId = $tabMix;
		if (!is_numeric($tabMix)) {
			$tabId = Module::getModuleId($tabMix);
		}
		Log::trace('Entering ' . __METHOD__ . ": $tabId,$columnName");
		if ($readOnly && isset(self::$columnPermissionCacheRead[$tabId][$columnName])) {
			return self::$columnPermissionCacheRead[$tabId][$columnName];
		} elseif (!$readOnly && isset(self::$columnPermissionCacheWrite[$tabId][$columnName])) {
			return self::$columnPermissionCacheWrite[$tabId][$columnName];
		}
		$fields = static::getFieldsPermissions($tabId, $readOnly);
		foreach ($fields as &$field) {
			if ($field['columnname'] === $columnName) {
				$permission = !($field['visible']);
				if ($readOnly) {
					self::$columnPermissionCacheRead[$tabId][$columnName] = $permission;
					self::$fieldPermissionCacheRead[$tabId][$field['fieldname']] = $permission;
				} else {
					self::$columnPermissionCacheWrite[$tabId][$columnName] = $permission;
					self::$fieldPermissionCacheWrite[$tabId][$field['fieldname']] = $permission;
				}

				return $permission;
			}
		}
		if ($readOnly) {
			self::$columnPermissionCacheRead[$tabId][$columnName] = false;
		} else {
			self::$columnPermissionCacheWrite[$tabId][$columnName] = false;
		}
		return false;
	}

	/**
	 * Get related field for module.
	 *
	 * @param string|bool $moduleName
	 * @param string|bool $forModule
	 *
	 * @return array
	 */
	public static function getRelatedFieldForModule($moduleName = false, $forModule = false)
	{
		$key = 'all';
		if (Cache::has('getRelatedFieldForModule', $key)) {
			$fields = Cache::get('getRelatedFieldForModule', $key);
		} else {
			$db = Db::getInstance();
			$wsQuery = (new Db\Query())->select(['vtiger_field.fieldid', 'vtiger_field.uitype', 'vtiger_field.tabid', 'vtiger_field.columnname', 'vtiger_field.fieldname', 'vtiger_field.tablename', 'vtiger_tab.name', 'relmod' => 'vtiger_ws_referencetype.type', 'type' => new \yii\db\Expression($db->quoteValue(2))])
				->from('vtiger_field')
				->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
				->innerJoin('vtiger_ws_fieldtype', 'vtiger_field.uitype = vtiger_ws_fieldtype.uitype')
				->innerJoin('vtiger_ws_referencetype', 'vtiger_ws_fieldtype.fieldtypeid = vtiger_ws_referencetype.fieldtypeid')
				->where(['vtiger_tab.presence' => 0]);
			$fmrQuery = (new Db\Query())->select(['vtiger_field.fieldid', 'vtiger_field.uitype', 'vtiger_field.tabid', 'vtiger_field.columnname', 'vtiger_field.fieldname', 'vtiger_field.tablename', 'vtiger_tab.name', 'relmod' => 'vtiger_fieldmodulerel.relmodule', 'type' => new \yii\db\Expression($db->quoteValue(1))])
				->from('vtiger_field')
				->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
				->innerJoin('vtiger_fieldmodulerel', 'vtiger_field.fieldid = vtiger_fieldmodulerel.fieldid')
				->where(['vtiger_tab.presence' => 0]);
			$fields = [];
			$dataReader = $wsQuery->union($fmrQuery)->createCommand()->query();
			while ($row = $dataReader->read()) {
				$fields[$row['name']][$row['relmod']] = $row;
			}
			$query = (new Db\Query())->select(['vtiger_field.fieldid', 'vtiger_field.uitype', 'vtiger_field.tabid', 'vtiger_field.columnname', 'vtiger_field.fieldname', 'vtiger_field.tablename', 'vtiger_tab.name'])
				->from('vtiger_field')
				->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
				->where(['vtiger_tab.presence' => 0, 'vtiger_field.uitype' => [64, 65, 66, 67, 68]]);
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				foreach (ModuleHierarchy::getModulesByUitype($row['uitype']) as $module => $value) {
					$row['relmod'] = $module;
					$row['type'] = 3;
					$fields[$row['name']][$row['relmod']] = $row;
				}
			}
			Cache::save('getRelatedFieldForModule', $key, $fields, Cache::LONG);
		}
		if ($moduleName) {
			if (isset($fields[$moduleName])) {
				if ($forModule) {
					return $fields[$moduleName][$forModule] ?? [];
				}

				return $fields[$moduleName];
			}

			return [];
		} else {
			if ($forModule) {
				$rfields = [];
				foreach ($fields as $moduleName => $forModules) {
					if (isset($forModules[$forModule])) {
						$rfields[$moduleName] = $forModules[$forModule];
					}
				}
				return $rfields;
			}
		}
		return $fields;
	}

	/**
	 * Get fields from relation by relation Id.
	 *
	 * @param int $relationId
	 *
	 * @return string[]
	 */
	public static function getFieldsFromRelation($relationId)
	{
		if (empty($relationId)) {
			return [];
		}
		if (Cache::has('getFieldsFromRelation', $relationId)) {
			$fields = Cache::get('getFieldsFromRelation', $relationId);
		} else {
			$fields = (new \App\Db\Query())->select(['fieldname'])->from('vtiger_relatedlists_fields')
				->where(['relation_id' => $relationId])->column();
			Cache::save('getFieldsFromRelation', $relationId, $fields, Cache::LONG);
		}
		return $fields;
	}

	/**
	 * Function to gets module field info.
	 *
	 * @param string|int $mixed
	 * @param string|int $module
	 *
	 * @return null|array
	 */
	public static function getFieldInfo($mixed, $module = false)
	{
		$fieldInfo = false;
		if (is_numeric($mixed)) {
			if (Cache::has('FieldInfoById', $mixed)) {
				return Cache::get('FieldInfoById', $mixed);
			}
			$fieldInfo = (new \App\Db\Query())->from('vtiger_field')->where(['fieldid' => $mixed])->one();
			Cache::save('FieldInfoById', $mixed, $fieldInfo, Cache::LONG);
		} else {
			$fieldsInfo = \vtlib\Functions::getModuleFieldInfos($module);
			if ($fieldsInfo && isset($fieldsInfo[$mixed])) {
				$fieldInfo = $fieldsInfo[$mixed];
				Cache::save('FieldInfoById', $fieldInfo['fieldid'], $fieldInfo, Cache::LONG);
			}
		}
		return $fieldInfo;
	}

	/**
	 * Get fields type from uitype.
	 *
	 * @return array
	 */
	public static function getFieldsTypeFromUIType()
	{
		if (Cache::has('getFieldsTypeFromUIType', '')) {
			return Cache::get('getFieldsTypeFromUIType', '');
		}
		$fieldTypeMapping = (new Db\Query())->from('vtiger_ws_fieldtype')->indexBy('uitype')->all();
		Cache::save('getFieldsTypeFromUIType', '', $fieldTypeMapping, Cache::LONG);

		return $fieldTypeMapping;
	}
}
