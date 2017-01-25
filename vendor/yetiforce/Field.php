<?php
namespace App;

/**
 * Field basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Field
{

	/**
	 * Function gets the list of fields that the user has permissions to
	 * @param int $tabId Module ID
	 * @param type $readOnly Read/preview only fields
	 * @return array
	 */
	public static function getFieldsPermissions($tabId, $readOnly = true)
	{
		Log::trace('Entering ' . __METHOD__ . ": $tabId");
		if (Cache::has(__METHOD__ . User::getCurrentUserId(), $tabId)) {
			$fields = Cache::get(__METHOD__ . User::getCurrentUserId(), $tabId);
		} else {
			$query = (new \App\Db\Query())
				->select('vtiger_field.*, vtiger_profile2field.readonly,vtiger_profile2field.visible')
				->from('vtiger_field')
				->innerJoin('vtiger_profile2field', 'vtiger_profile2field.fieldid = vtiger_field.fieldid')
				->innerJoin('vtiger_def_org_field', 'vtiger_def_org_field.fieldid = vtiger_field.fieldid')
				->where([
					'vtiger_field.tabid' => (int) $tabId,
					'vtiger_profile2field.visible' => 0,
					'vtiger_def_org_field.visible' => 0,
					'vtiger_field.presence' => [0, 2]])
				->groupBy('vtiger_field.fieldid,vtiger_profile2field.readonly,vtiger_profile2field.visible');
			$profileList = \App\User::getCurrentUserModel()->getProfiles();
			if ($profileList) {
				$query->andWhere(['vtiger_profile2field.profileid' => $profileList]);
			}
			$fields = $query->all();
			Cache::save(__METHOD__ . User::getCurrentUserId(), $tabId, $fields);
		}
		if ($readOnly) {
			return $fields;
		}
		foreach ($fields as $key => &$field) {
			if ($field['readonly']) {
				unset($fields[$key]);
			}
		}
		return $fields;
	}

	private static $fieldPermissionCacheRead = [];
	private static $fieldPermissionCacheWrite = [];

	/**
	 * Function checks field permissions by field name or field id
	 * @param int|string $tabMix Module ID or module name
	 * @param int|string $fieldMix Field ID or field name
	 * @param boolean $readOnly Read/preview only fields
	 * @return boolean
	 */
	public static function getFieldPermission($tabMix, $fieldMix, $readOnly = true)
	{
		$tabId = $tabMix;
		if (!is_numeric($tabMix)) {
			$tabId = Module::getModuleId($tabMix);
		}
		Log::trace('Entering ' . __METHOD__ . ": $tabId,$fieldMix");
		if ($readOnly && isset(static::$fieldPermissionCacheRead[$tabId][$fieldMix])) {
			return static::$fieldPermissionCacheRead[$tabId][$fieldMix];
		} elseif (!$readOnly && isset(static::$fieldPermissionCacheWrite[$tabId][$fieldMix])) {
			return static::$fieldPermissionCacheWrite[$tabId][$fieldMix];
		}
		$fields = static::getFieldsPermissions($tabId, $readOnly);
		$key = is_numeric($fieldMix) ? 'fieldid' : 'fieldname';
		foreach ($fields as &$field) {
			if ($field[$key] === $fieldMix) {
				$permission = !($field['visible']);
				if ($readOnly) {
					static::$fieldPermissionCacheRead[$tabId][$fieldMix] = $permission;
					static::$columnPermissionCacheRead[$tabId][$field['columnname']] = $permission;
				} else {
					static::$fieldPermissionCacheWrite[$tabId][$fieldMix] = $permission;
					static::$columnPermissionCacheWrite[$tabId][$field['columnname']] = $permission;
				}
				return $permission;
			}
		}
		if ($readOnly) {
			static::$fieldPermissionCacheRead[$tabId][$fieldMix] = false;
		} else {
			static::$fieldPermissionCacheWrite[$tabId][$fieldMix] = false;
		}
		return false;
	}

	private static $columnPermissionCacheRead = [];
	private static $columnPermissionCacheWrite = [];

	/**
	 * Function checks field permissions by column name
	 * @param int|string $tabMix Module ID or module name
	 * @param string $columnName Field ID or field name
	 * @param boolean $readOnly Read/preview only fields
	 * @return boolean
	 */
	public static function getColumnPermission($tabMix, $columnName, $readOnly = true)
	{
		$tabId = $tabMix;
		if (!is_numeric($tabMix)) {
			$tabId = Module::getModuleId($tabMix);
		}
		Log::trace('Entering ' . __METHOD__ . ": $tabId,$columnName");
		if ($readOnly && isset(static::$columnPermissionCacheRead[$tabId][$columnName])) {
			return static::$columnPermissionCacheRead[$tabId][$columnName];
		} elseif (!$readOnly && isset(static::$columnPermissionCacheWrite[$tabId][$columnName])) {
			return static::$columnPermissionCacheWrite[$tabId][$columnName];
		}
		$fields = static::getFieldsPermissions($tabId, $readOnly);
		foreach ($fields as &$field) {
			if ($field['columnname'] === $columnName) {
				$permission = !($field['visible']);
				if ($readOnly) {
					static::$columnPermissionCacheRead[$tabId][$columnName] = $permission;
					static::$fieldPermissionCacheRead[$tabId][$field['fieldname']] = $permission;
				} else {
					static::$columnPermissionCacheWrite[$tabId][$columnName] = $permission;
					static::$fieldPermissionCacheWrite[$tabId][$field['fieldname']] = $permission;
				}
				return $permission;
			}
		}
		if ($readOnly) {
			static::$columnPermissionCacheRead[$tabId][$columnName] = false;
		} else {
			static::$columnPermissionCacheWrite[$tabId][$columnName] = false;
		}
		return false;
	}

	/**
	 * Get releted field for module
	 * @param string|boolean $moduleName
	 * @param string|boolean $forModule
	 * @return array
	 */
	public static function getReletedFieldForModule($moduleName = false, $forModule = false)
	{
		if (Cache::has('getReletedFieldForModule', $moduleName)) {
			$fileds = Cache::get('getReletedFieldForModule', $moduleName);
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
			$fileds = [];
			$dataReader = $wsQuery->union($fmrQuery)->createCommand()->query();
			while ($row = $dataReader->read()) {
				$fileds[$row['name']][$row['relmod']] = $row;
			}
			$query = (new Db\Query())->select(['vtiger_field.fieldid', 'vtiger_field.uitype', 'vtiger_field.tabid', 'vtiger_field.columnname', 'vtiger_field.fieldname', 'vtiger_field.tablename', 'vtiger_tab.name'])
				->from('vtiger_field')
				->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
				->where(['vtiger_tab.presence' => 0, 'vtiger_field.uitype' => [66, 67, 68]]);
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				foreach (ModuleHierarchy::getModulesByUitype($row['uitype']) as $module => $value) {
					$row['relmod'] = $module;
					$row['type'] = 3;
					$fileds[$row['name']][$row['relmod']] = $row;
				}
			}
			Cache::save('getReletedFieldForModule', '', $fileds, Cache::LONG);
		}
		if ($moduleName) {
			if (isset($fileds[$moduleName])) {
				if ($forModule) {
					return isset($fileds[$moduleName][$forModule]) ? $fileds[$moduleName][$forModule] : [];
				}
				return $fileds[$moduleName];
			}
			return [];
		} else {
			if ($forModule) {
				$rfileds = [];
				foreach ($fileds as $moduleName => $forModules) {
					if (isset($forModules[$forModule])) {
						$rfileds[$moduleName] = $forModules[$forModule];
					}
				}
				return $rfileds;
			}
		}
		return $fileds;
	}

	/**
	 * Get fields from relation by relation Id
	 * @param int $relationId
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
}
