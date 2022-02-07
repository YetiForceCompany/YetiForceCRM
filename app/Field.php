<?php

namespace App;

/**
 * Field basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Field
{
	/** @var string[] Help info views. */
	const HELP_INFO_VIEWS = ['LBL_EDIT_VIEW' => 'Edit', 'LBL_DETAIL_VIEW' => 'Detail', 'LBL_QUICK_CREATE_VIEW' => 'QuickCreateAjax'];

	/** @var array System fields */
	const SYSTEM_FIELDS = [
		'assigned_user_id' => [
			'validationConditions' => ['name'],
			'name' => 'assigned_user_id',	'column' => 'smownerid',	'label' => 'Assigned To',	'table' => 'vtiger_crmentity',
			'uitype' => 53,	'typeofdata' => 'V~M',	'maximumlength' => 65535,
		],
		'createdtime' => [
			'validationConditions' => ['name'],
			'name' => 'createdtime',	'column' => 'createdtime',	'label' => 'Created Time',	'table' => 'vtiger_crmentity',
			'uitype' => 70,	'typeofdata' => 'DT~O',	'displaytype' => 2,	'maximumlength' => 65535,
		],
		'modifiedtime' => [
			'validationConditions' => ['name'],
			'name' => 'modifiedtime',	'column' => 'modifiedtime',	'label' => 'Modified Time',	'table' => 'vtiger_crmentity',
			'uitype' => 70,	'typeofdata' => 'DT~O',	'displaytype' => 2,	'maximumlength' => 65535,
		],
		'created_user_id' => [
			'validationConditions' => ['column'],
			'name' => 'created_user_id',	'column' => 'smcreatorid',	'label' => 'Created By',	'table' => 'vtiger_crmentity',
			'uitype' => 52,	'typeofdata' => 'V~O',	'displaytype' => 2,	'quickcreate' => 3, 'masseditable' => 0, 'maximumlength' => 65535,
		],
		'modifiedby' => [
			'validationConditions' => ['name'],
			'name' => 'modifiedby',	'column' => 'modifiedby',	'label' => 'Last Modified By',	'table' => 'vtiger_crmentity',
			'uitype' => 52,	'typeofdata' => 'V~O',	'displaytype' => 2,	'quickcreate' => 3, 'masseditable' => 0, 'maximumlength' => 65535,
		],
		'shownerid' => [
			'validationConditions' => ['name'],
			'name' => 'shownerid',	'column' => 'shownerid',	'label' => 'Share with users',	'table' => 'vtiger_crmentity',
			'uitype' => 120,	'typeofdata' => 'V~O',	'columntype' => 'int(11)', 'maximumlength' => 65535,
		],
		'private' => [
			'validationConditions' => ['name'],
			'name' => 'private',	'column' => 'private',	'label' => 'FL_IS_PRIVATE',	'table' => 'vtiger_crmentity',
			'uitype' => 56,	'typeofdata' => 'C~O',	'columntype' => 'int(11)', 'maximumlength' => '-128,127', 'presence' => 2, 'generatedtype' => 2,
		],
		'share_externally' => [
			'validationConditions' => ['uitype', 'fieldparams'],
			'name' => 'share_externally',	'column' => 'share_externally',	'label' => 'FL_SHARE_EXTERNALLY',	'defaultvalue' => 0,	'fieldparams' => 1,
			'uitype' => 318,	'typeofdata' => 'C~O',	'columntype' => 'tinyint(1)', 'maximumlength' => '-128,127',
		],
	];

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
					'vtiger_profile2field.visible',
				])
				->from('vtiger_field')
				->innerJoin('vtiger_profile2field', 'vtiger_profile2field.fieldid = vtiger_field.fieldid')
				->where([
					'vtiger_field.tabid' => (int) $tabId,
					'vtiger_profile2field.visible' => 0,
					'vtiger_field.visible' => 0,
					'vtiger_field.presence' => [0, 2],
				]);
			$profileList = \App\User::getCurrentUserModel()->getProfiles();
			if ($profileList) {
				$query->andWhere(['vtiger_profile2field.profileid' => $profileList]);
			}
			$fields = [];
			$dataReader = $query->distinct()->createCommand()->query();
			while ($row = $dataReader->read()) {
				if (isset($fields[$row['fieldname']])) {
					$old = $fields[$row['fieldname']];
					$row['readonly'] = $old['readonly'] > 0 ? $row['readonly'] : $old['readonly'];
					$row['visible'] = $old['visible'] > 0 ? $row['visible'] : $old['visible'];
				}
				$fields[$row['fieldname']] = $row;
			}
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
		}
		if (!$readOnly && isset(self::$fieldPermissionCacheWrite[$tabId][$fieldMix])) {
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
		}
		if (!$readOnly && isset(self::$columnPermissionCacheWrite[$tabId][$columnName])) {
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
	 * @param bool|string $moduleName
	 * @param bool|string $forModule
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
			$wsQuery = (new Db\Query())->select(['vtiger_field.fieldid', 'vtiger_field.uitype', 'vtiger_field.tabid', 'vtiger_field.columnname', 'vtiger_field.fieldname', 'vtiger_field.tablename', 'vtiger_field.fieldlabel', 'vtiger_tab.name', 'relmod' => 'vtiger_ws_referencetype.type', 'type' => new \yii\db\Expression($db->quoteValue(2))])
				->from('vtiger_field')
				->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
				->innerJoin('vtiger_ws_fieldtype', 'vtiger_field.uitype = vtiger_ws_fieldtype.uitype')
				->innerJoin('vtiger_ws_referencetype', 'vtiger_ws_fieldtype.fieldtypeid = vtiger_ws_referencetype.fieldtypeid')
				->where(['vtiger_tab.presence' => 0]);
			$fmrQuery = (new Db\Query())->select(['vtiger_field.fieldid', 'vtiger_field.uitype', 'vtiger_field.tabid', 'vtiger_field.columnname', 'vtiger_field.fieldname', 'vtiger_field.tablename', 'vtiger_field.fieldlabel', 'vtiger_tab.name', 'relmod' => 'vtiger_fieldmodulerel.relmodule', 'type' => new \yii\db\Expression($db->quoteValue(1))])
				->from('vtiger_field')
				->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
				->innerJoin('vtiger_fieldmodulerel', 'vtiger_field.fieldid = vtiger_fieldmodulerel.fieldid')
				->where(['vtiger_tab.presence' => 0]);
			$fields = [];
			$dataReader = $wsQuery->union($fmrQuery)->createCommand()->query();
			while ($row = $dataReader->read()) {
				$fields[$row['name']][$row['relmod']] = $row;
			}
			$query = (new Db\Query())->select(['vtiger_field.fieldid', 'vtiger_field.uitype', 'vtiger_field.tabid', 'vtiger_field.columnname', 'vtiger_field.fieldname', 'vtiger_field.tablename', 'vtiger_field.fieldlabel', 'vtiger_tab.name'])
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
		}
		if ($forModule) {
			$rfields = [];
			foreach ($fields as $moduleName => $forModules) {
				if (isset($forModules[$forModule])) {
					$rfields[$moduleName] = $forModules[$forModule];
				}
			}
			return $rfields;
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
			$fields = (new \App\Db\Query())->select(['vtiger_relatedlists_fields.fieldid', 'vtiger_field.fieldname'])->from('vtiger_relatedlists_fields')
				->innerJoin('vtiger_field', 'vtiger_field.fieldid = vtiger_relatedlists_fields.fieldid')
				->where(['relation_id' => $relationId, 'vtiger_field.presence' => [0, 2]])->orderBy(['vtiger_relatedlists_fields.relation_id' => SORT_ASC, 'vtiger_relatedlists_fields.sequence' => SORT_ASC])
				->createCommand()->queryAllByGroup();
			Cache::save('getFieldsFromRelation', $relationId, $fields, Cache::LONG);
		}
		return $fields;
	}

	/**
	 * Function to gets module field info.
	 *
	 * @param int|string $mixed
	 * @param int|string $module
	 *
	 * @return array|null
	 */
	public static function getFieldInfo($mixed, $module = false)
	{
		$fieldInfo = false;
		if (is_numeric($mixed)) {
			if (Cache::has('FieldInfoById', $mixed)) {
				return Cache::get('FieldInfoById', $mixed);
			}
			$fieldInfo = (new \App\Db\Query())
				->from('vtiger_field')
				->leftJoin('s_#__fields_anonymization', 'vtiger_field.fieldid = s_#__fields_anonymization.field_id')
				->where(['vtiger_field.fieldid' => $mixed])->one();
			Cache::save('FieldInfoById', $mixed, $fieldInfo, Cache::LONG);
		} else {
			$fieldsInfo = self::getModuleFieldInfos($module);
			if ($fieldsInfo && isset($fieldsInfo[$mixed])) {
				$fieldInfo = $fieldsInfo[$mixed];
				Cache::save('FieldInfoById', $fieldInfo['fieldid'], $fieldInfo, Cache::LONG);
			}
		}
		return $fieldInfo;
	}

	/**
	 * Function get module field infos.
	 *
	 * @param int|string $module
	 * @param bool       $returnByColumn
	 *
	 * @return array
	 */
	public static function getModuleFieldInfos($module, bool $returnByColumn = false): array
	{
		if (is_numeric($module)) {
			$module = Module::getModuleName($module);
		}
		$cacheName = 'ModuleFieldInfosByName';
		if (!Cache::has($cacheName, $module)) {
			$dataReader = (new Db\Query())
				->from('vtiger_field')
				->leftJoin('s_#__fields_anonymization', 'vtiger_field.fieldid = s_#__fields_anonymization.field_id')
				->where(['tabid' => Module::getModuleId($module)])
				->createCommand()->query();
			$fieldInfoByName = $fieldInfoByColumn = [];
			while ($row = $dataReader->read()) {
				$fieldInfoByName[$row['fieldname']] = $row;
				$fieldInfoByColumn[$row['columnname']] = $row;
			}
			Cache::save($cacheName, $module, $fieldInfoByName);
			Cache::save('ModuleFieldInfosByColumn', $module, $fieldInfoByColumn);
		}
		if ($returnByColumn) {
			return Cache::get('ModuleFieldInfosByColumn', $module);
		}
		return Cache::get($cacheName, $module);
	}

	/**
	 * Function get module field infos by presence.
	 *
	 * @param int|string $module
	 * @param array      $presence
	 *
	 * @return array
	 */
	public static function getModuleFieldInfosByPresence($module, array $presence = ['0', '2']): array
	{
		$moduleFields = [];
		$fieldsInfo = self::getModuleFieldInfos($module);
		foreach ($fieldsInfo as $fieldInfo) {
			if (\in_array($fieldInfo['presence'], $presence)) {
				$moduleFields[$fieldInfo['fieldname']] = $fieldInfo;
			}
		}
		return $moduleFields;
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

	/**
	 * Get quick changer fields.
	 *
	 * @param int $tabId
	 *
	 * @return array
	 */
	public static function getQuickChangerFields(int $tabId): array
	{
		if (Cache::has('getQuickChangerFields', $tabId)) {
			return Cache::get('getQuickChangerFields', $tabId);
		}
		$dataReader = (new Db\Query())->from('s_#__record_quick_changer')->where(['tabid' => $tabId])->createCommand()->query();
		$rows = [];
		while ($row = $dataReader->read()) {
			$row['conditions'] = Json::decode($row['conditions']);
			$row['values'] = Json::decode($row['values']);
			$rows[$row['id']] = $row;
		}
		Cache::save('getQuickChangerFields', $tabId, $rows, Cache::LONG);
		return $rows;
	}

	/**
	 * Check quick changer conditions.
	 *
	 * @param array                $field
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	public static function checkQuickChangerConditions(array $field, \Vtiger_Record_Model $recordModel)
	{
		$return = false;
		foreach ($field['conditions'] as $fieldName => $value) {
			if ($recordModel->get($fieldName) !== $value) {
				$status = 1;
			}
		}
		if (!isset($status)) {
			$fields = $recordModel->getModule()->getFields();
			foreach ($field['values'] as $fieldName => $value) {
				if (isset($fields[$fieldName]) && $fields[$fieldName]->isEditable()) {
					$return = true;
				}
			}
		}
		return $return;
	}

	/**
	 * Get a list of custom default values for a given field type in the WebservicePremium API.
	 *
	 * @param \Vtiger_Field_Model $fieldModel
	 *
	 * @return string[]
	 */
	public static function getCustomListForDefaultValue(\Vtiger_Field_Model $fieldModel): array
	{
		if ($fieldModel->isReferenceField()) {
			return [
				'loggedContact' => \App\Language::translate('LBL_LOGGED_CONTACT', 'Settings:LayoutEditor'),
				'accountOnContact' => \App\Language::translate('LBL_ACCOUNT_ON_CONTACT', 'Settings:LayoutEditor'),
				'accountLoggedContact' => \App\Language::translate('LBL_ACCOUNT_LOGGED_CONTACT', 'Settings:LayoutEditor'),
			];
		}
		return [];
	}
}
