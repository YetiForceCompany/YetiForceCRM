<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

/**
 * Vtiger Field Model Class.
 */
class Vtiger_Field_Model extends vtlib\Field
{
	const REFERENCE_TYPE = 'reference';
	const OWNER_TYPE = 'owner';
	const CURRENCY_LIST = 'currencyList';
	const QUICKCREATE_MANDATORY = 0;
	const QUICKCREATE_NOT_ENABLED = 1;
	const QUICKCREATE_ENABLED = 2;
	const QUICKCREATE_NOT_PERMITTED = 3;

	public static $referenceTypes = ['reference', 'referenceLink', 'referenceProcess', 'referenceSubProcess', 'referenceExtend', 'referenceSubProcessSL'];

	/** @var array Field maximum length by UiType. */
	public static $uiTypeMaxLength = [
		99 => 100,
		120 => 65535,
		106 => '3,64',
		156 => '3',
		360 => '0,99999999',
	];

	/** @var int[] Field maximum length by db type. */
	public static $typesMaxLength = [
		'tinytext' => 255,
		'text' => 65535,
		'mediumtext' => 16777215,
		'longtext' => 4294967295,
		'blob' => 65535,
		'mediumblob' => 16777215,
		'longblob' => 4294967295,
	];

	/** @var Vtiger_Field_Model[] Cache by field id */
	protected static $instanceCacheById = [];

	/** @var Vtiger_Field_Model[][] Cache by module id and field id */
	protected static $instanceCacheByName = [];

	/**
	 * @var array
	 */
	protected $fieldInfo;
	protected $fieldType;
	protected $fieldDataTypeShort;
	protected $uitype_instance;

	/** @var string[] List of modules the field referenced to. */
	public $referenceList;

	/** @var string[] Picklist values only for custom fields;. */
	public $picklistValues;

	/** @var bool Is calculate field */
	protected $isCalculateField = true;

	/** @var bool|null Field visibility permissions */
	protected $permissions;

	/** @var Vtiger_Base_UIType Vtiger_Base_UIType or UI Type specific model instance */
	protected $uitypeModel;

	/** @var bool[] Permissions cache */
	protected $permissionsCache = [];

	/**
	 * Initialize.
	 *
	 * @param string     $module
	 * @param array      $data
	 * @param mixed|null $name
	 *
	 * @return \Vtiger_Field_Model
	 */
	public static function init($module = 'Vtiger', $data = [], $name = '')
	{
		if (\App\Module::getModuleId($module)) {
			$moduleModel = Vtiger_Module_Model::getInstance($module);
		} else {
			$modelClassName = \Vtiger_Loader::getComponentClassName('Model', 'Module', $module);
			$moduleModel = new $modelClassName();
		}
		$modelClassName = \Vtiger_Loader::getComponentClassName('Model', 'Field', $module);
		$instance = new $modelClassName();
		$instance->setModule($moduleModel);
		$instance->setData(array_merge([
			'uitype' => 1,
			'column' => $name,
			'name' => $name,
			'label' => $name,
			'displaytype' => 1,
			'typeofdata' => 'V~O',
			'presence' => 0,
			'isReadOnly' => false,
			'isEditableReadOnly' => false,
		], $data));
		return $instance;
	}

	/**
	 * Function to get the value of a given property.
	 *
	 * @param string $propertyName
	 *
	 * @return mixed|null
	 */
	public function get(string $propertyName)
	{
		if (property_exists($this, $propertyName)) {
			return $this->{$propertyName};
		}
		return null;
	}

	/**
	 * Function which sets value for given name.
	 *
	 * @param string $name  - name for which value need to be assinged
	 * @param mixed  $value - values that need to be assigned
	 *
	 * @return Vtiger_Field_Model
	 */
	public function set(string $name, $value)
	{
		$this->{$name} = $value;
		return $this;
	}

	/**
	 * Function to get the Field Id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get full name.
	 *
	 * @return string
	 */
	public function getFullName()
	{
		return $this->get('source_field_name') ? "{$this->getName()}:{$this->getModuleName()}:{$this->get('source_field_name')}" : $this->getName();
	}

	/**
	 * Get full label translation.
	 *
	 * @param Vtiger_Module_Model|null $module
	 *
	 * @return string
	 */
	public function getFullLabelTranslation(?Vtiger_Module_Model $module = null): string
	{
		$translation = '';
		if ($this->get('source_field_name') && !$this->get('isLabelCustomized')) {
			if (!$module) {
				throw new \App\Exceptions\AppException('ERR_ARGUMENT_DOES_NOT_EXIST');
			}
			$translation = \App\Language::translate($module->getFieldByName($this->get('source_field_name'))->getFieldLabel(), $module->getName()) . ' - ';
		}
		return $translation .= \App\Language::translate($this->getFieldLabel(), $this->getModuleName());
	}

	/**
	 * Get field name.
	 *
	 * @deprecated Use $this->getName()
	 *
	 * @return string
	 */
	public function getFieldName()
	{
		return $this->name;
	}

	/**
	 * Get field label.
	 *
	 * @return string
	 */
	public function getFieldLabel()
	{
		return $this->label;
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return $this->table;
	}

	/**
	 * Get column label.
	 *
	 * @return string
	 */
	public function getColumnName()
	{
		return $this->column;
	}

	/**
	 * Get ui type.
	 *
	 * @return int
	 */
	public function getUIType()
	{
		return $this->uitype;
	}

	/**
	 * Function to retrieve full data.
	 *
	 * @return <array>
	 */
	public function getData()
	{
		return get_object_vars($this);
	}

	/**
	 * Get module model.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
			if (isset($this->block->module)) {
				$moduleObj = $this->block->module;
			}
			//fix for opensource emailTemplate listview break
			if (empty($moduleObj)) {
				return false;
			}
			$this->module = Vtiger_Module_Model::getInstanceFromModuleObject($moduleObj);
		}
		return $this->module;
	}

	public function setModule($moduleInstance)
	{
		$this->module = $moduleInstance;
		return $this;
	}

	/**
	 * Function to retieve display value for a value.
	 *
	 * @param mixed                    $value          value which need to be converted to display value
	 * @param bool|int                 $record
	 * @param bool|Vtiger_Record_Model $recordInstance
	 * @param bool                     $rawText
	 * @param bool|int                 $length         Length of the text
	 * @param mixed                    $recordModel
	 *
	 * @return mixed converted display value
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		return $this->getUITypeModel()->getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}

	/**
	 * Function to retrieve display type of a field.
	 *
	 * @return int display type of the field
	 */
	public function getDisplayType()
	{
		return (int) $this->get('displaytype');
	}

	/**
	 * Function to get the Webservice Field data type.
	 *
	 * @return string Data type of the field
	 */
	public function getFieldDataType()
	{
		if (!isset($this->fieldDataType)) {
			$uiType = $this->getUIType();
			if (55 === $uiType) {
				$cacheName = $uiType . '-' . $this->getName();
			} else {
				$cacheName = $uiType . '-' . $this->get('typeofdata');
			}
			if (App\Cache::has('FieldDataType', $cacheName)) {
				$fieldDataType = App\Cache::get('FieldDataType', $cacheName);
			} else {
				switch ($uiType) {
					case 4:
						$fieldDataType = 'recordNumber';
						break;
					case 8:
						$fieldDataType = 'totalTime';
						break;
					case 9:
						$fieldDataType = 'percentage';
						break;
					case 12:
						$fieldDataType = 'accountName';
						break;
					case 27:
						$fieldDataType = 'fileLocationType';
						break;
					case 28:
						$fieldDataType = 'documentsFileUpload';
						break;
					case 31:
						$fieldDataType = 'theme';
						break;
					case 32:
						$fieldDataType = 'languages';
						break;
					case 35:
						$fieldDataType = 'country';
						break;
					case 54:
						$fieldDataType = 'multiowner';
						break;
					case 64:
						$fieldDataType = 'referenceSubProcessSL';
						break;
					case 65:
						$fieldDataType = 'referenceExtend';
						break;
					case 66:
						$fieldDataType = 'referenceProcess';
						break;
					case 67:
						$fieldDataType = 'referenceLink';
						break;
					case 68:
						$fieldDataType = 'referenceSubProcess';
						break;
					case 69:
						$fieldDataType = 'image';
						break;
					case 79:
					case 80:
						$fieldDataType = 'datetime';
						break;
					case 98:
						$fieldDataType = 'userRole';
						break;
					case 99:
						$fieldDataType = 'password';
						break;
					case 101:
						$fieldDataType = 'userReference';
						break;
					case 115:
						$fieldDataType = 'picklist';
						break;
					case 117:
						$fieldDataType = 'currencyList';
						break;
					case 120:
						$fieldDataType = 'sharedOwner';
						break;
					case 301:
						$fieldDataType = 'modules';
						break;
					case 302:
						$fieldDataType = 'tree';
						break;
					case 303:
						$fieldDataType = 'taxes';
						break;
					case 304:
						$fieldDataType = 'inventoryLimit';
						break;
					case 305:
						$fieldDataType = 'multiReferenceValue';
						break;
					case 308:
						$fieldDataType = 'rangeTime';
						break;
					case 309:
						$fieldDataType = 'categoryMultipicklist';
						break;
					case 311:
						$fieldDataType = 'multiImage';
						break;
					case 312:
						$fieldDataType = 'authySecretTotp';
						break;
					case 313:
						$fieldDataType = 'twitter';
						break;
					case 314:
						$fieldDataType = 'multiEmail';
						break;
					case 315:
						$fieldDataType = 'multiDependField';
						break;
					case 316:
						$fieldDataType = 'smtp';
						break;
					case 317:
						$fieldDataType = 'currencyInventory';
						break;
					case 318:
						$fieldDataType = 'serverAccess';
						break;
					case 319:
						$fieldDataType = 'multiDomain';
						break;
					case 320:
						$fieldDataType = 'multiListFields';
						break;
					case 321:
						$fieldDataType = 'multiReference';
						break;
					case 322:
						$fieldDataType = 'mailScannerActions';
						break;
					case 323:
						$fieldDataType = 'mailScannerFields';
						break;
					case 324:
						$fieldDataType = 'token';
						break;
					case 325:
						$fieldDataType = 'magentoServer';
						break;
					case 326:
						$fieldDataType = 'meetingUrl';
						break;
					case 327:
						$fieldDataType = 'barcode';
						break;
					case 328:
						$fieldDataType = 'changesJson';
						break;
					case 329:
						$fieldDataType = 'iban';
						break;
					case 330:
						$fieldDataType = 'multiAttachment';
						break;
					default:
						$fieldsDataType = App\Field::getFieldsTypeFromUIType();
						if (isset($fieldsDataType[$uiType])) {
							$fieldDataType = $fieldsDataType[$uiType]['fieldtype'];
						} else {
							$fieldTypeArray = explode('~', $this->get('typeofdata'));
							switch ($fieldTypeArray[0]) {
								case 'T':
									$fieldDataType = 'time';
									break;
								case 'D':
									$fieldDataType = 'date';
									break;
								case 'DT':
									$fieldDataType = 'datetime';
									break;
								case 'E':
									$fieldDataType = 'email';
									break;
								case 'N':
								case 'NN':
									$fieldDataType = 'double';
									break;
								case 'P':
									$fieldDataType = 'password';
									break;
								case 'I':
									$fieldDataType = 'integer';
									break;
								case 'V':
								default:
									$fieldDataType = 'string';
									break;
							}
						}
						break;
				}
				App\Cache::save('FieldDataType', $cacheName, $fieldDataType);
			}
			$this->fieldDataType = $fieldDataType;
		}
		return $this->fieldDataType;
	}

	/**
	 * Function to get list of modules the field refernced to.
	 *
	 * @return string[] list of modules for which field is refered to
	 */
	public function getReferenceList()
	{
		if (isset($this->referenceList)) {
			return $this->referenceList;
		}
		if (\App\Cache::has('getReferenceList', $this->getId())) {
			return \App\Cache::get('getReferenceList', $this->getId());
		}
		if (method_exists($this->getUITypeModel(), 'getReferenceList')) {
			$list = $this->getUITypeModel()->getReferenceList();
		} else {
			if (10 === $this->getUIType()) {
				$query = (new \App\Db\Query())->select(['module' => 'relmodule'])
					->from('vtiger_fieldmodulerel')
					->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_fieldmodulerel.relmodule')
					->where(['fieldid' => $this->getId()])
					->andWhere(['<>', 'vtiger_tab.presence', 1])
					->orderBy(['sequence' => SORT_ASC]);
			} else {
				$query = (new \App\Db\Query())->select(['module' => 'vtiger_ws_referencetype.type'])
					->from('vtiger_ws_referencetype')
					->innerJoin('vtiger_ws_fieldtype', 'vtiger_ws_referencetype.fieldtypeid = vtiger_ws_fieldtype.fieldtypeid')
					->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_ws_referencetype.type')
					->where(['vtiger_ws_fieldtype.uitype' => $this->getUIType()])
					->andWhere(['<>', 'vtiger_tab.presence', 1]);
			}
			$list = [];
			foreach ($query->column() as $moduleName) {
				if (\App\Privilege::isPermitted($moduleName)) {
					$list[] = $moduleName;
				}
			}
		}
		\App\Cache::save('getReferenceList', $this->getId(), $list);
		return $list;
	}

	/**
	 * Function to check if the field is named field of the module.
	 *
	 * @return bool
	 */
	public function isNameField(): bool
	{
		$moduleModel = $this->getModule();
		return $moduleModel && !$this->isReferenceField() && !\in_array($this->getFieldDataType(), ['email', 'url', 'phone']) && \in_array($this->getName(), $moduleModel->getNameFields());
	}

	/**
	 * Function to get the UI Type model for the uitype of the current field.
	 *
	 * @return Vtiger_Base_UIType Vtiger_Base_UIType or UI Type specific model instance
	 */
	public function getUITypeModel(): Vtiger_Base_UIType
	{
		if (isset($this->uitypeModel)) {
			return $this->uitypeModel;
		}
		return $this->uitypeModel = Vtiger_Base_UIType::getInstanceFromField($this);
	}

	public function isRoleBased()
	{
		return 15 === $this->get('uitype') || 33 === $this->get('uitype');
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @param bool $skipCheckingRole
	 *
	 * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise
	 */
	public function getPicklistValues($skipCheckingRole = false)
	{
		if (isset($this->picklistValues)) {
			return $this->picklistValues;
		}
		$fieldDataType = $this->getFieldDataType();
		$fieldPickListValues = [];
		if ('picklist' === $fieldDataType || 'multipicklist' === $fieldDataType) {
			if ($this->isRoleBased() && !$skipCheckingRole) {
				$picklistValues = \App\Fields\Picklist::getRoleBasedValues($this->getName(), \App\User::getCurrentUserModel()->getRole());
			} else {
				$picklistValues = App\Fields\Picklist::getValuesName($this->getName());
			}
			foreach ($picklistValues as $value) {
				$fieldPickListValues[$value] = \App\Language::translate($value, $this->getModuleName(), false, false);
			}
			// Protection against deleting a value that does not exist on the list
			if ('picklist' === $fieldDataType) {
				$fieldValue = $this->get('fieldvalue');
				if (!empty($fieldValue) && !isset($fieldPickListValues[$fieldValue])) {
					$fieldPickListValues[$fieldValue] = \App\Language::translate($fieldValue, $this->getModuleName(), false, false);
					$this->set('isEditableReadOnly', true);
				}
			}
		} elseif (method_exists($this->getUITypeModel(), 'getPicklistValues')) {
			$fieldPickListValues = $this->getUITypeModel()->getPicklistValues();
		}
		return $fieldPickListValues;
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise
	 */
	public function getModulesListValues()
	{
		$allModules = \vtlib\Functions::getAllModules(true, false, 0);
		$modules = [];
		foreach ($allModules as $module) {
			$modules[$module['tabid']] = [
				'name' => $module['name'],
				'label' => App\Language::translate($module['name'], $module['name']),
			];
		}
		return $modules;
	}

	public static function showDisplayTypeList()
	{
		return [
			1 => 'LBL_DISPLAY_TYPE_1',
			2 => 'LBL_DISPLAY_TYPE_2',
			3 => 'LBL_DISPLAY_TYPE_3',
			4 => 'LBL_DISPLAY_TYPE_4',
			//5 => 'LBL_DISPLAY_TYPE_5',
			9 => 'LBL_DISPLAY_TYPE_9',
			10 => 'LBL_DISPLAY_TYPE_10',
			6 => 'LBL_DISPLAY_TYPE_6',
		];
	}

	/**
	 * Function to check if the current field is mandatory or not.
	 *
	 * @return bool
	 */
	public function isMandatory()
	{
		if ($this->get('isMandatory')) {
			return $this->get('isMandatory');
		}
		$typeOfData = explode('~', $this->get('typeofdata'));
		return isset($typeOfData[1]) && 'M' === $typeOfData[1];
	}

	/**
	 * Function to get the field type.
	 *
	 * @return string type of the field
	 */
	public function getFieldType()
	{
		if (isset($this->fieldType)) {
			return $this->fieldType;
		}
		$fieldTypeArray = explode('~', $this->get('typeofdata'));
		$fieldTypeArray = array_shift($fieldTypeArray);
		if ('reference' === $this->getFieldDataType()) {
			$fieldTypeArray = 'V';
		} else {
			$fieldTypeArray = \vtlib\Functions::transformFieldTypeOfData($this->get('table'), $this->get('column'), $fieldTypeArray);
		}
		return $this->fieldType = $fieldTypeArray;
	}

	/**
	 * Function to check if the field is shown in detail view.
	 *
	 * @return bool
	 */
	public function isViewEnabled()
	{
		if (4 === $this->getDisplayType() || 6 === $this->getDisplayType() || 1 === $this->get('presence') || 3 === $this->get('presence')) {
			return false;
		}
		return $this->getPermissions();
	}

	/**
	 * Function to check if the field is shown in detail view.
	 *
	 * @return bool
	 */
	public function isViewable()
	{
		$return = true;
		if (isset($this->permissionsCache['isViewable'])) {
			return $this->permissionsCache['isViewable'];
		}
		if (
			!$this->isViewEnabled() || !$this->isActiveReference()
			|| ((306 === $this->get('uitype') || 307 === $this->get('uitype') || 311 === $this->get('uitype') || 312 === $this->get('uitype')) && 2 === $this->getDisplayType())
		) {
			$return = false;
		}
		return $this->permissionsCache['isViewable'] = $return;
	}

	/**
	 * Function to check if the field is exportable.
	 *
	 * @return bool
	 */
	public function isExportable(): bool
	{
		return $this->isViewable();
	}

	/**
	 * Function to check if the field is shown in detail view.
	 *
	 * @return bool
	 */
	public function isViewableInDetailView()
	{
		if (!$this->isViewable() || 3 === $this->getDisplayType() || 5 === $this->getDisplayType()) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check whether the current field is writable.
	 *
	 * @return bool
	 */
	public function isWritable()
	{
		$return = true;
		if (isset($this->permissionsCache['isWritable'])) {
			return $this->permissionsCache['isWritable'];
		}
		$displayType = $this->get('displaytype');
		if (!$this->isViewEnabled() || 4 === $displayType || 5 === $displayType
			|| 0 === strcasecmp($this->getFieldDataType(), 'autogenerated')
			|| 0 === strcasecmp($this->getFieldDataType(), 'id')
			|| true === $this->isReadOnly()
			|| !$this->getUITypeModel()->isWritable()) {
			$return = false;
		}
		return $this->permissionsCache['isWritable'] = $return;
	}

	/**
	 * Function to check whether the current field is editable.
	 *
	 * @return bool
	 */
	public function isEditable(): bool
	{
		$return = true;
		if (isset($this->permissionsCache['isEditable'])) {
			return $this->permissionsCache['isEditable'];
		}
		$displayType = $this->get('displaytype');
		if (!$this->isWritable() || (1 !== $displayType && 10 !== $displayType) || true === $this->isReadOnly()) {
			$return = false;
		}
		return $this->permissionsCache['isEditable'] = $return;
	}

	/**
	 * Function to check whether field is ajax editable.
	 *
	 * @return bool
	 */
	public function isAjaxEditable()
	{
		$ajaxRestrictedFields = [72, 12, 101];
		return !(10 === (int) $this->get('displaytype') || $this->isReferenceField() || !$this->getUITypeModel()->isAjaxEditable() || !$this->isEditable() || \in_array($this->get('uitype'), $ajaxRestrictedFields));
	}

	public function isEditableReadOnly()
	{
		if (null !== $this->get('isEditableReadOnly')) {
			return $this->get('isEditableReadOnly');
		}
		if (10 === (int) $this->get('displaytype')) {
			return true;
		}
		return false;
	}

	/**
	 * Function to check whether the current field is read-only.
	 *
	 * @return bool
	 */
	public function isReadOnly(): bool
	{
		if (isset($this->isReadOnly)) {
			return $this->isReadOnly;
		}
		return $this->isReadOnly = !$this->getProfileReadWritePermission();
	}

	public function isQuickCreateEnabled()
	{
		$moduleModel = $this->getModule();
		$quickCreate = $this->get('quickcreate');
		if ((self::QUICKCREATE_MANDATORY == $quickCreate || self::QUICKCREATE_ENABLED == $quickCreate || $this->isMandatory()) && method_exists($moduleModel, 'isQuickCreateSupported') && $moduleModel->isQuickCreateSupported()) {
			return true;
		}
		return false;
	}

	/**
	 * Function to check whether summary field or not.
	 *
	 * @return bool true/false
	 */
	public function isSummaryField()
	{
		return ($this->get('summaryfield')) ? true : false;
	}

	/**
	 * Function to check whether the current reference field is active.
	 *
	 * @return bool
	 */
	public function isActiveReference()
	{
		if ('reference' === $this->getFieldDataType() && empty($this->getReferenceList())) {
			return false;
		}
		return true;
	}

	/**
	 * If the field is sortable in ListView.
	 */
	public function isListviewSortable()
	{
		return !$this->get('fromOutsideList') && $this->getUITypeModel()->isListviewSortable();
	}

	/**
	 * Static Function to get the instance fo Vtiger Field Model from a given vtlib\Field object.
	 *
	 * @param vtlib\Field $fieldObj - vtlib field object
	 *
	 * @return Vtiger_Field_Model instance
	 */
	public static function getInstanceFromFieldObject(vtlib\Field $fieldObj)
	{
		$objectProperties = get_object_vars($fieldObj);
		$className = Vtiger_Loader::getComponentClassName('Model', 'Field', $fieldObj->getModuleName());
		$fieldModel = new $className();
		foreach ($objectProperties as $properName => $propertyValue) {
			$fieldModel->{$properName} = $propertyValue;
		}
		return $fieldModel;
	}

	/**
	 * Function to get the custom view column name transformation of the field for a date field used in date filters.
	 *
	 * @return string - tablename:columnname:fieldname:module_fieldlabel
	 */
	public function getCVDateFilterColumnName()
	{
		$moduleName = $this->getModuleName();
		$tableName = $this->get('table');
		$columnName = $this->get('column');
		$fieldName = $this->get('name');
		$fieldLabel = $this->get('label');

		$escapedFieldLabel = str_replace(' ', '_', $fieldLabel);
		$moduleFieldLabel = $moduleName . '_' . $escapedFieldLabel;

		return $tableName . ':' . $columnName . ':' . $fieldName . ':' . $moduleFieldLabel;
	}

	/**
	 * Function to get value for customview.
	 *
	 * @param string $sourceFieldName
	 *
	 * @throws \Exception
	 *
	 * @return string
	 */
	public function getCustomViewSelectColumnName(string $sourceFieldName = '')
	{
		return "{$this->get('name')}:{$this->getModuleName()}" . ($sourceFieldName ? ":{$sourceFieldName}" : '');
	}

	/**
	 * Function to get the custom view column name transformation of the field.
	 *
	 * @return string - tablename:columnname:fieldname:module_fieldlabel:fieldtype
	 */
	public function getCustomViewColumnName()
	{
		$moduleName = $this->getModuleName();
		$tableName = $this->get('table');
		$columnName = $this->get('column');
		$fieldName = $this->get('name');
		$fieldLabel = $this->get('label');
		$typeOfData = $this->get('typeofdata');
		$fieldTypeOfData = explode('~', $typeOfData);
		$fieldTypeOfData = $fieldTypeOfData[0];
		//Special condition need for reference field as they should be treated as string field
		if ('reference' === $this->getFieldDataType()) {
			$fieldTypeOfData = 'V';
		} else {
			$fieldTypeOfData = \vtlib\Functions::transformFieldTypeOfData($tableName, $columnName, $fieldTypeOfData);
		}
		$escapedFieldLabel = str_replace(' ', '_', $fieldLabel);
		$moduleFieldLabel = "{$moduleName}_{$escapedFieldLabel}";

		return "$tableName:$columnName:$fieldName:$moduleFieldLabel:$fieldTypeOfData";
	}

	/**
	 * This is set from Workflow Record Structure, since workflow expects the field name
	 * in a different format in its filter. Eg: for module field its fieldname and for reference
	 * fields its reference_field_name : (reference_module_name) field - salesorder_id: (SalesOrder) subject.
	 *
	 * @return string
	 */
	public function getWorkFlowFilterColumnName()
	{
		return $this->get('workflow_columnname');
	}

	/**
	 * Function to get the field details.
	 *
	 * @return array - array of field values
	 */
	public function getFieldInfo(): array
	{
		return $this->getUITypeModel()->getFieldInfo();
	}

	/**
	 * Load field info.
	 *
	 * @return array
	 */
	public function loadFieldInfo(): array
	{
		if (null !== $this->fieldInfo) {
			return $this->fieldInfo;
		}
		$this->fieldInfo['name'] = $this->get('name');
		$this->fieldInfo['label'] = App\Language::translate($this->get('label'), $this->getModuleName(), false, false);
		$fieldDataType = $this->getFieldDataType();
		$this->fieldInfo['type'] = $fieldDataType;
		$this->fieldInfo['mandatory'] = $this->isMandatory();
		$this->fieldInfo['defaultvalue'] = $this->getDefaultFieldValue();
		$this->fieldInfo['presence'] = $this->isActiveField();
		$this->fieldInfo['quickcreate'] = $this->isQuickCreateEnabled();
		$this->fieldInfo['masseditable'] = $this->isMassEditable();
		$this->fieldInfo['header_field'] = $this->getHeaderField();
		$this->fieldInfo['maxlengthtext'] = $this->get('maxlengthtext');
		$this->fieldInfo['maximumlength'] = $this->get('maximumlength');
		$this->fieldInfo['maxwidthcolumn'] = $this->get('maxwidthcolumn');
		$this->fieldInfo['tabindex'] = $this->get('tabindex');
		$this->fieldInfo['fieldtype'] = explode('~', $this->get('typeofdata'))[0] ?? '';
		$currentUser = \App\User::getCurrentUserModel();
		switch ($fieldDataType) {
			case 'picklist':
			case 'multipicklist':
			case 'multiowner':
			case 'multiReferenceValue':
			case 'inventoryLimit':
			case 'languages':
			case 'currencyList':
			case 'fileLocationType':
			case 'taxes':
			case 'multiListFields':
			case 'mailScannerFields':
			case 'country':
				$this->fieldInfo['picklistvalues'] = $this->getPicklistValues() ?: [];
				break;
			case 'date':
			case 'datetime':
				$this->fieldInfo['date-format'] = $currentUser->getDetail('date_format');
				break;
			case 'time':
				$this->fieldInfo['time-format'] = $currentUser->getDetail('hour_format');
				break;
			case 'currency':
				$this->fieldInfo['currency_symbol'] = $currentUser->getDetail('currency_symbol');
				$this->fieldInfo['decimal_separator'] = $currentUser->getDetail('currency_decimal_separator');
				$this->fieldInfo['group_separator'] = $currentUser->getDetail('currency_grouping_separator');
				break;
			case 'owner':
			case 'userCreator':
			case 'sharedOwner':
				if (!App\Config::performance('SEARCH_OWNERS_BY_AJAX') || \in_array(\App\Request::_get('module'), ['CustomView', 'Workflows', 'PDF', 'MappedFields']) || 'showAdvancedSearch' === \App\Request::_get('mode')) {
					$userList = \App\Fields\Owner::getInstance($this->getModuleName(), $currentUser)->getAccessibleUsers('', $fieldDataType);
					$groupList = \App\Fields\Owner::getInstance($this->getModuleName(), $currentUser)->getAccessibleGroups('', $fieldDataType);
					$pickListValues = [];
					$pickListValues[\App\Language::translate('LBL_USERS', $this->getModuleName())] = $userList;
					$pickListValues[\App\Language::translate('LBL_GROUPS', $this->getModuleName())] = $groupList;
					$this->fieldInfo['picklistvalues'] = $pickListValues;
					if (App\Config::performance('SEARCH_OWNERS_BY_AJAX')) {
						$this->fieldInfo['searchOperator'] = 'e';
					}
				} else {
					if ('owner' === $fieldDataType) {
						$this->fieldInfo['searchOperator'] = 'e';
					}
				}
				break;
			case 'modules':
				foreach ($this->getModulesListValues() as $module) {
					$modulesList[$module['name']] = $module['label'];
				}
				$this->fieldInfo['picklistvalues'] = $modulesList;
				break;
			case 'categoryMultipicklist':
			case 'tree':
				$this->fieldInfo['picklistvalues'] = \App\Fields\Tree::getPicklistValue($this->getFieldParams(), $this->getModuleName());
				$this->fieldInfo['treetemplate'] = $this->getFieldParams();
				$this->fieldInfo['modulename'] = $this->getModuleName();
				break;
			case 'email':
				if (\App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_ACTIVE') && !empty(\App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_VALUES'))) {
					$validate = false;
					if (empty(\App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_ALLOWED')) || \in_array($this->getModuleName(), \App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_ALLOWED'))) {
						$validate = true;
					}
					if (\in_array($this->getModuleName(), \App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_EXCLUDED'))) {
						$validate = false;
					}
					if ($validate) {
						$this->fieldInfo['restrictedDomains'] = \App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_VALUES');
					}
				}
				break;
			default:
				break;
		}

		return $this->fieldInfo;
	}

	/**
	 * Set field info.
	 *
	 * @param array $fieldInfo
	 *
	 * @return $this
	 */
	public function setFieldInfo(array $fieldInfo)
	{
		$this->fieldInfo = $fieldInfo;

		return $this;
	}

	/**
	 * Function to get the advanced filter option names by Field type.
	 *
	 * @return <Array>
	 */
	public static function getAdvancedFilterOpsByFieldType()
	{
		return [
			'V' => ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny', 'om', 'wr', 'nwr'],
			'N' => ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'],
			'T' => ['e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a', 'y', 'ny'],
			'I' => ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'],
			'C' => ['e', 'n', 'y', 'ny'],
			'D' => ['e', 'n', 'bw', 'b', 'a', 'y', 'ny'],
			'DT' => ['e', 'n', 'bw', 'b', 'a', 'y', 'ny'],
			'NN' => ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'],
			'E' => ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny'],
		];
	}

	/**
	 * Function to retrieve field model for specific block and module.
	 *
	 * @param vtlib\ModuleBasic $moduleModel
	 *
	 * @return Vtiger_Field_Model[][]
	 */
	public static function getAllForModule(vtlib\ModuleBasic $moduleModel)
	{
		if (\App\Cache::staticHas('ModuleFields', $moduleModel->id)) {
			return \App\Cache::staticGet('ModuleFields', $moduleModel->id);
		}
		$fieldModelList = [];
		$fieldObjects = parent::getAllForModule($moduleModel);
		$fieldModelList = [];
		if (!\is_array($fieldObjects)) {
			$fieldObjects = [];
		}
		foreach ($fieldObjects as &$fieldObject) {
			$fieldModel = self::getInstanceFromFieldObject($fieldObject);
			$block = $fieldModel->get('block') ? $fieldModel->get('block')->id : 0;
			$fieldModelList[$block][] = $fieldModel;
			self::$instanceCacheById[$fieldModel->getId()] = $fieldModel;
			self::$instanceCacheByName[$moduleModel->getId()][$fieldModel->getName()] = $fieldModel;
		}
		\App\Cache::staticSave('ModuleFields', $moduleModel->id, $fieldModelList);
		return $fieldModelList;
	}

	/**
	 * Function to get new field model instance, the function creates a new object and does not pass a reference.
	 *
	 * @param string|int                $value  fieldname or field id
	 * @param Vtiger_Module_Model|false $module optional - module instance
	 *
	 * @return Vtiger_Field_Model|false
	 */
	public static function getInstance($value, $module = false)
	{
		if (\is_numeric($value)) {
			if (isset(self::$instanceCacheById[$value])) {
				return clone self::$instanceCacheById[$value];
			}
		} elseif ($module) {
			if (isset(self::$instanceCacheByName[$module->getId()][$value])) {
				return clone self::$instanceCacheByName[$module->getId()][$value];
			}
		} else {
			throw new \App\Exceptions\AppException("ERR_NOT_MODULE||$value||$module");
		}
		if ($fieldInstance = parent::getInstance($value, $module)) {
			$fieldModel = self::getInstanceFromFieldObject($fieldInstance);
			self::$instanceCacheById[$fieldModel->getId()] = $fieldModel;
			self::$instanceCacheByName[$fieldModel->get('tabid')][$value] = $fieldModel;
			return $fieldModel;
		}
		return false;
	}

	/**
	 * Returns instance of field.
	 *
	 * @param array|string $fieldInfo
	 *
	 * @return bool|\Vtiger_Field_Model|\vtlib\Field|null
	 */
	public static function getInstanceFromFilter($fieldInfo)
	{
		if (\is_string($fieldInfo)) {
			$fieldInfo = array_combine(['field_name', 'module_name', 'source_field_name'], array_pad(explode(':', $fieldInfo), 3, false));
		}
		return static::getInstance($fieldInfo['field_name'], Vtiger_Module_Model::getInstance($fieldInfo['module_name']));
	}

	/**
	 * Function checks if the current Field is Read/Write.
	 *
	 * @return bool
	 */
	public function getProfileReadWritePermission()
	{
		return $this->getPermissions(false);
	}

	/**
	 * Gets default validator.
	 *
	 * @return array
	 */
	public function getDefaultValidator(): array
	{
		$validator = [];
		$fieldName = $this->getName();
		switch ($fieldName) {
			case 'birthday':
				$funcName = ['name' => 'lessThanToday'];
				$validator[] = $funcName;
				break;
			case 'targetenddate':
			case 'actualenddate':
			case 'enddate':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['startdate'], ];
				$validator[] = $funcName;
				break;
			case 'startdate':
				if ('Project' === $this->getModule()->get('name')) {
					$params = ['targetenddate'];
				} else {
					//for project task
					$params = ['enddate'];
				}
				$funcName = ['name' => 'lessThanDependentField',
					'params' => $params, ];
				$validator[] = $funcName;
				break;
			case 'expiry_date':
			case 'due_date':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['start_date'], ];
				$validator[] = $funcName;
				break;
			case 'sales_end_date':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['sales_start_date'], ];
				$validator[] = $funcName;
				break;
			case 'sales_start_date':
				$funcName = ['name' => 'lessThanDependentField',
					'params' => ['sales_end_date'], ];
				$validator[] = $funcName;
				break;
			case 'qty_per_unit':
			case 'qtyindemand':
			case 'hours':
			case 'days':
				$funcName = ['name' => 'PositiveNumber'];
				$validator[] = $funcName;
				break;
			case 'employees':
				$funcName = ['name' => 'WholeNumber'];
				$validator[] = $funcName;
				break;
			case 'related_to':
				$funcName = ['name' => 'ReferenceField'];
				$validator[] = $funcName;
				break;
			//SRecurringOrders field sepecial validators
			case 'end_period':
				$funcName1 = ['name' => 'greaterThanDependentField',
					'params' => ['start_period'], ];
				$validator[] = $funcName1;
				$funcName2 = ['name' => 'lessThanDependentField',
					'params' => ['duedate'], ];
				$validator[] = $funcName2;

			// no break
			case 'start_period':
				$funcName = ['name' => 'lessThanDependentField',
					'params' => ['end_period'], ];
				$validator[] = $funcName;
				break;
			default:
				break;
		}
		return $validator;
	}

	/**
	 * Function returns Client Side Validators name.
	 *
	 * @return array [name=>Name of the Validator, params=>Extra Parameters]
	 */
	public function getValidator()
	{
		return method_exists($this->getUITypeModel(), 'getValidator') ? $this->getUITypeModel()->getValidator() : $this->getDefaultValidator();
	}

	/**
	 * Function to retrieve display value in edit view.
	 *
	 * @param mixed               $value
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $this->getUITypeModel()->getEditViewDisplayValue($value, $recordModel);
	}

	/**
	 * Function to retrieve user value in edit view.
	 *
	 * @param mixed               $value
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function getEditViewValue($value, $recordModel = false)
	{
		return $this->getUITypeModel()->getEditViewValue($value, $recordModel);
	}

	/**
	 * Function to get Display value for RelatedList.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function getRelatedListDisplayValue($value)
	{
		return $this->getUITypeModel()->getRelatedListDisplayValue($value);
	}

	/**
	 * Function to get Default Field Value.
	 *
	 * @throws \Exception
	 *
	 * @return mixed
	 */
	public function getDefaultFieldValue()
	{
		return $this->getUITypeModel()->getDefaultValue();
	}

	/**
	 * Function whcih will get the databse insert value format from user format.
	 *
	 * @param type  $value       in user format
	 * @param mixed $recordModel
	 *
	 * @return type
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return $this->getUITypeModel()->getDBValue($value, $recordModel);
	}

	/**
	 * Function to get visibilty permissions of a Field.
	 *
	 * @param bool $readOnly
	 *
	 * @return bool
	 */
	public function getPermissions($readOnly = true)
	{
		if (isset($this->permissions)) {
			return $this->permissions;
		}
		return \App\Field::getFieldPermission($this->getModuleId(), $this->getName(), $readOnly);
	}

	public function __update()
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		1 === $this->get('generatedtype') ? $generatedType = 1 : $generatedType = 2;
		$dbCommand->update('vtiger_field', ['typeofdata' => $this->get('typeofdata'), 'presence' => $this->get('presence'), 'quickcreate' => $this->get('quickcreate'),
			'masseditable' => $this->get('masseditable'), 'header_field' => $this->get('header_field'), 'maxlengthtext' => $this->get('maxlengthtext'),
			'maxwidthcolumn' => $this->get('maxwidthcolumn'), 'tabindex' => $this->get('tabindex'), 'defaultvalue' => $this->get('defaultvalue'), 'summaryfield' => $this->get('summaryfield'),
			'displaytype' => $this->get('displaytype'), 'helpinfo' => $this->get('helpinfo'), 'generatedtype' => $generatedType,
			'fieldparams' => $this->get('fieldparams'), 'quickcreatesequence' => $this->get('quicksequence'), 'icon' => $this->get('icon'), 'fieldlabel' => $this->get('label'),
		], ['fieldid' => $this->get('id')])->execute();
		if ($anonymizationTarget = $this->get('anonymizationTarget')) {
			$anonymizationTarget = \App\Json::encode($anonymizationTarget);
			$execute = $dbCommand->update('s_#__fields_anonymization', ['anonymization_target' => $anonymizationTarget], ['field_id' => $this->getId()])->execute();
			if (!$execute) {
				$dbCommand->insert('s_#__fields_anonymization', ['field_id' => $this->getId(), 'anonymization_target' => $anonymizationTarget])->execute();
			}
		} else {
			$dbCommand->delete('s_#__fields_anonymization', ['field_id' => $this->getId()])->execute();
		}
		App\Cache::clear();
	}

	/**
	 * Change the mandatory field.
	 *
	 * @param string $mandatoryValue
	 *
	 * @return $this
	 */
	public function updateTypeofDataFromMandatory($mandatoryValue = 'O')
	{
		$mandatoryValue = strtoupper($mandatoryValue);
		$supportedMandatoryLiterals = ['O', 'M'];
		if (!\in_array($mandatoryValue, $supportedMandatoryLiterals)) {
			return $this;
		}
		$typeOfData = $this->get('typeofdata');
		$components = explode('~', $typeOfData);
		$components[1] = $mandatoryValue;
		$this->set('typeofdata', implode('~', $components));

		return $this;
	}

	public function isCustomField()
	{
		return 2 == $this->generatedtype;
	}

	public function hasDefaultValue()
	{
		return !empty($this->defaultvalue);
	}

	public function isActiveField()
	{
		return \in_array($this->get('presence'), [0, 2]);
	}

	public function isMassEditable()
	{
		return 1 == $this->masseditable;
	}

	public function isHeaderField()
	{
		return !empty($this->header_field);
	}

	/**
	 * Gets header field data.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return mixed
	 */
	public function getHeaderField()
	{
		return !empty($this->header_field) ? \App\Json::decode($this->header_field) : [];
	}

	/**
	 * Gets header field value.
	 *
	 * @param string $type
	 * @param mixed  $default
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return mixed
	 */
	public function getHeaderValue(string $type, $default = '')
	{
		return $this->getHeaderField()[$type] ?? $default;
	}

	/**
	 * Function which will check if empty piclist option should be given.
	 *
	 * @return bool
	 */
	public function isEmptyPicklistOptionAllowed()
	{
		if (method_exists($this->getUITypeModel(), 'isEmptyPicklistOptionAllowed')) {
			return $this->getUITypeModel()->isEmptyPicklistOptionAllowed();
		}
		return true;
	}

	/**
	 * Check if it is a tree field.
	 *
	 * @return bool
	 */
	public function isTreeField(): bool
	{
		return \in_array($this->getFieldDataType(), ['tree', 'categoryMultipicklist']);
	}

	public function isReferenceField()
	{
		return \in_array($this->getFieldDataType(), self::$referenceTypes);
	}

	public function isOwnerField()
	{
		return self::OWNER_TYPE == $this->getFieldDataType();
	}

	/**
	 * Function determines whether the field value can be duplicated.
	 *
	 * @return bool
	 */
	public function isDuplicable(): bool
	{
		return $this->getUITypeModel()->isDuplicable();
	}

	/**
	 * Is summation field.
	 *
	 * @return bool
	 */
	public function isCalculateField()
	{
		return $this->isCalculateField && !$this->get('fromOutsideList') && (\in_array($this->getUIType(), [71, 7, 317, 8]) || \in_array($this->getFieldDataType(), ['integer', 'double']));
	}

	/**
	 * Function returns field instance for field ID.
	 *
	 * @param int $fieldId
	 * @param int $moduleTabId
	 *
	 * @return \Vtiger_Field_Model
	 */
	public static function getInstanceFromFieldId($fieldId, $moduleTabId = false)
	{
		if (isset(self::$instanceCacheById[$fieldId])) {
			return self::$instanceCacheById[$fieldId];
		}
		$field = \App\Field::getFieldInfo($fieldId);
		$className = Vtiger_Loader::getComponentClassName('Model', 'Field', \App\Module::getModuleName($field['tabid']));
		$fieldModel = new $className();
		$fieldModel->initialize($field, $field['tabid']);
		self::$instanceCacheById[$fieldModel->getId()] = $fieldModel;
		self::$instanceCacheByName[$fieldModel->get('tabid')][$fieldModel->getName()] = $fieldModel;
		return $fieldModel;
	}

	public function getWithDefaultValue()
	{
		$defaultValue = $this->getDefaultFieldValue();
		$recordValue = $this->get('fieldvalue');
		if (empty($recordValue) && !$defaultValue) {
			$this->set('fieldvalue', $defaultValue);
		}
		return $this;
	}

	/**
	 * Get field params.
	 *
	 * @return array
	 */
	public function getFieldParams()
	{
		if (!\is_array($this->get('fieldparams')) && \App\Json::isJson($this->get('fieldparams'))) {
			return \App\Json::decode($this->get('fieldparams'));
		}
		return $this->get('fieldparams') ?: [];
	}

	/**
	 * Get field icon.
	 *
	 * @param string $place
	 *
	 * @return array
	 */
	public function getIcon(string $place = ''): array
	{
		$icon = [];
		if (\is_array($this->get('icon'))) {
			$icon = $this->get('icon');
		} elseif ($this->get('icon') && \App\Json::isJson($this->get('icon'))) {
			$icon = \App\Json::decode($this->get('icon'));
		}
		if ($place && isset($icon['place']) && !\in_array($place, $icon['place'])) {
			$icon = [];
		}
		return $icon;
	}

	/**
	 * Get anonymization target.
	 *
	 * @see \App\Anonymization::getTypes()
	 *
	 * @return int[]
	 */
	public function getAnonymizationTarget(): array
	{
		if (\is_string($this->get('anonymizationTarget'))) {
			$this->set('anonymizationTarget', \App\Json::decode($this->get('anonymizationTarget')));
		}
		return $this->get('anonymizationTarget');
	}

	/**
	 * Get maximum value.
	 *
	 * @return int
	 */
	public function getMaxValue(): int
	{
		if (($maximumLength = $this->get('maximumlength')) && false !== strpos($maximumLength, ',')) {
			return (int) explode(',', $maximumLength)[1];
		}
		if (empty($maximumLength)) {
			$maximumLength = $this->getDbValueLength();
		}

		return (int) $maximumLength;
	}

	/**
	 * Get length value form database.
	 *
	 * @return int
	 */
	public function getDbValueLength(): int
	{
		$db = \App\Db::getInstance();
		$tableSchema = $db->getSchema()->getTableSchema($this->getTableName());
		if (empty($tableSchema)) {
			throw new \App\Exceptions\AppException('ERR_TABLE_DOES_NOT_EXISTS||' . $this->getTableName());
		}
		return $tableSchema->getColumn($this->getColumnName())->size ?: 0;
	}

	public function isActiveSearchView()
	{
		if ($this->get('fromOutsideList') || $this->get('searchLockedFields')) {
			return false;
		}
		return $this->getUITypeModel()->isActiveSearchView();
	}

	/**
	 * Empty value search in view.
	 *
	 * @return bool
	 */
	public function searchLockedEmptyFields(): bool
	{
		return empty($this->get('searchLockedEmptyFields'));
	}

	/**
	 * Function returns info about field structure in database.
	 *
	 * @param bool $returnString
	 *
	 * @return array|string
	 */
	public function getDBColumnType($returnString = true)
	{
		$db = \App\Db::getInstance();
		$tableSchema = $db->getSchema()->getTableSchema($this->getTableName(), true);
		if (empty($tableSchema)) {
			return false;
		}
		$columnSchema = $tableSchema->getColumn($this->getColumnName());
		$data = get_object_vars($columnSchema);
		if ($returnString) {
			$string = $data['type'];
			if ($data['size']) {
				if ('decimal' === $data['type']) {
					$string .= '(' . $data['size'] . ',' . $data['scale'] . ')';
				} else {
					$string .= '(' . $data['size'] . ')';
				}
			}
			return $string;
		}
		return $data;
	}

	/**
	 * Function to get range of values.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string|null
	 */
	public function getRangeValues()
	{
		$uiTypeModel = $this->getUITypeModel();
		if (method_exists($uiTypeModel, 'getRangeValues')) {
			return $uiTypeModel->getRangeValues();
		}
		$allowedTypes = $uiTypeModel->getAllowedColumnTypes();
		if (null === $allowedTypes) {
			return;
		}
		$data = $this->getDBColumnType(false);
		if (!\in_array($data['type'], $allowedTypes)) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_TYPE||' . $data['type'] . '||' . print_r($allowedTypes, true));
		}
		preg_match('/^([\w\-]+)/i', $data['dbType'], $matches);
		$type = $matches[1] ?? $data['type'];
		$uitype = $this->getUIType();
		if (isset(self::$uiTypeMaxLength[$uitype])) {
			$range = self::$uiTypeMaxLength[$uitype];
		} elseif (isset(self::$typesMaxLength[$type])) {
			$range = self::$typesMaxLength[$type];
		} else {
			switch ($type) {
				case 'binary':
				case 'string':
				case 'varchar':
				case 'varbinary':
					$range = (int) $data['size'];
					break;
				case 'bigint':
				case 'mediumint':
					throw new \App\Exceptions\AppException("ERR_NOT_ALLOWED_TYPE||$type||integer,smallint,tinyint");
				case 'integer':
				case 'int':
					if ($data['unsigned']) {
						$range = '4294967295';
					} else {
						$range = '-2147483648,2147483647';
					}
					break;
				case 'smallint':
					if ($data['unsigned']) {
						$range = '65535';
					} else {
						$range = '-32768,32767';
					}
					break;
				case 'tinyint':
					if ($data['unsigned']) {
						$range = '255';
					} else {
						$range = '-128,127';
					}
					break;
				case 'decimal':
					$range = 10 ** (((int) $data['size']) - ((int) $data['scale'])) - 1;
					break;
				default:
					$range = null;
					break;
			}
		}
		return $range;
	}

	/**
	 * Return allowed query operators for field.
	 *
	 * @return string[]
	 */
	public function getQueryOperators(): array
	{
		$operators = $this->getUITypeModel()->getQueryOperators();
		$oper = [];
		foreach ($operators as $op) {
			$label = '';
			if (isset(\App\Condition::STANDARD_OPERATORS[$op])) {
				$label = \App\Condition::STANDARD_OPERATORS[$op];
			} elseif (isset(\App\Condition::DATE_OPERATORS[$op])) {
				$label = \App\Condition::DATE_OPERATORS[$op]['label'];
			}
			$oper[$op] = $label;
		}
		return $oper;
	}

	/**
	 * Return allowed record operators for field.
	 *
	 * @return string[]
	 */
	public function getRecordOperators(): array
	{
		$operators = $this->getUITypeModel()->getRecordOperators();
		$oper = [];
		foreach ($operators as $op) {
			$label = '';
			if (isset(\App\Condition::STANDARD_OPERATORS[$op])) {
				$label = \App\Condition::STANDARD_OPERATORS[$op];
			} elseif (isset(\App\Condition::DATE_OPERATORS[$op])) {
				$label = \App\Condition::DATE_OPERATORS[$op]['label'];
			}
			$oper[$op] = $label;
		}
		return $oper;
	}

	/**
	 * Returns template for operator.
	 *
	 * @param string $operator
	 *
	 * @return string
	 */
	public function getOperatorTemplateName(string $operator)
	{
		if (\in_array($operator, App\Condition::OPERATORS_WITHOUT_VALUES)) {
			return;
		}
		if (\in_array($operator, \App\Condition::FIELD_COMPARISON_OPERATORS)) {
			return 'ConditionBuilder/FieldsListUitype.tpl';
		}
		return $this->getUITypeModel()->getOperatorTemplateName($operator);
	}

	/**
	 * Function to get the field model for condition builder.
	 *
	 * @param string $operator
	 *
	 * @return self
	 */
	public function getConditionBuilderField(string $operator): self
	{
		return $this->getUITypeModel()->getConditionBuilderField($operator);
	}

	/**
	 * Sets data.
	 *
	 * @param array $data
	 *
	 * @return self
	 */
	public function setData(array $data = [])
	{
		foreach ($data as $key => $value) {
			$this->set($key, $value);
		}
		return $this;
	}

	/**
	 * TabIndex last sequence number.
	 *
	 * @var int
	 */
	public static $tabIndexLastSeq = 0;
	/**
	 * TabIndex default sequence number.
	 *
	 * @var int
	 */
	public static $tabIndexDefaultSeq = 0;

	/**
	 * Get TabIndex.
	 *
	 * @return int
	 */
	public function getTabIndex(): int
	{
		$tabindex = 0;
		if (0 !== $this->get('tabindex')) {
			$tabindex = $this->get('tabindex');
		} elseif (self::$tabIndexLastSeq) {
			$tabindex = self::$tabIndexLastSeq;
		}
		return $tabindex + self::$tabIndexDefaultSeq;
	}

	/** {@inheritdoc} */
	public function delete()
	{
		$this->getUITypeModel()->delete();
		Settings_FieldsDependency_Module_Model::removeField($this->getModuleName(), $this->getName());
		\App\Utils\Kanban::deleteField($this->getModuleName(), $this->getName());
		\App\Fields\Picklist::removeDependencyConditionField($this->getModuleName(), $this->getName());
		$this->getModule()->clearCache();
		parent::delete();
	}
}
