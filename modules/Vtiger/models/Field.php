<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

/**
 * Vtiger Field Model Class.
 */
class Vtiger_Field_Model extends vtlib\Field
{
	protected $fieldType;
	protected $fieldDataTypeShort;
	protected $uitype_instance;
	public $picklistValues;
	/**
	 * @var bool
	 */
	protected $isCalculateField = true;
	/**
	 * @var Vtiger_Base_UIType Vtiger_Base_UIType or UI Type specific model instance
	 */
	protected $uitypeModel;

	public static $referenceTypes = ['reference', 'referenceLink', 'referenceProcess', 'referenceSubProcess', 'referenceExtend', 'referenceSubProcessSL'];

	const REFERENCE_TYPE = 'reference';
	const OWNER_TYPE = 'owner';
	const CURRENCY_LIST = 'currencyList';
	const QUICKCREATE_MANDATORY = 0;
	const QUICKCREATE_NOT_ENABLED = 1;
	const QUICKCREATE_ENABLED = 2;
	const QUICKCREATE_NOT_PERMITTED = 3;

	/**
	 * Function to get the value of a given property.
	 *
	 * @param string $propertyName
	 *
	 * @throws Exception
	 *
	 * @return <Object>
	 */
	public function get($propertyName)
	{
		if (property_exists($this, $propertyName)) {
			return $this->$propertyName;
		}
		return null;
	}

	/**
	 * Function which sets value for given name.
	 *
	 * @param string $name  - name for which value need to be assinged
	 * @param <type> $value - values that need to be assigned
	 *
	 * @return Vtiger_Field_Model
	 */
	public function set($name, $value)
	{
		$this->$name = $value;

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
	 * Get field name.
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
	}

	/**
	 * Function to retieve display value for a value.
	 *
	 * @param mixed                    $value          value which need to be converted to display value
	 * @param bool|int                 $record
	 * @param bool|Vtiger_Record_Model $recordInstance
	 * @param bool                     $rawText
	 * @param int|bool                 $length         Length of the text
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
			$uiType = $this->get('uitype');
			if ($uiType === 55) {
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
					case 55:
						if ($this->getName() === 'salutationtype') {
							$fieldDataType = 'picklist';
						} elseif ($this->getName() === 'firstname') {
							$fieldDataType = 'salutation';
						}
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
		if (\App\Cache::has('getReferenceList', $this->getId())) {
			return \App\Cache::get('getReferenceList', $this->getId());
		}
		if (method_exists($this->getUITypeModel(), 'getReferenceList')) {
			$list = $this->getUITypeModel()->getReferenceList();
		} else {
			if ($this->getUIType() === 10) {
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
	 * @return bool - True/False
	 */
	public function isNameField()
	{
		$moduleModel = $this->getModule();
		if (!$moduleModel) {
			return false;
		}
		if (in_array($this->getFieldName(), $moduleModel->getNameFields())) {
			return true;
		}
		return false;
	}

	/**
	 * Function to check whether the current field is read-only.
	 *
	 * @return bool - true/false
	 */
	public function isReadOnly()
	{
		if (isset($this->isReadOnly)) {
			return $this->isReadOnly;
		}
		return $this->isReadOnly = !$this->getProfileReadWritePermission();
	}

	/**
	 * Function to get the UI Type model for the uitype of the current field.
	 *
	 * @return Vtiger_Base_UIType Vtiger_Base_UIType or UI Type specific model instance
	 */
	public function getUITypeModel()
	{
		if (isset($this->uitypeModel)) {
			return $this->uitypeModel;
		}
		return $this->uitypeModel = Vtiger_Base_UIType::getInstanceFromField($this);
	}

	public function isRoleBased()
	{
		if ($this->get('uitype') === 15 || $this->get('uitype') === 33 || ($this->get('uitype') === 55 && $this->getFieldName() === 'salutationtype')) {
			return true;
		}
		return false;
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
		if (!isset($this->picklistValues)) {
			$fieldDataType = $this->getFieldDataType();
			if ($fieldDataType === 'picklist' || $fieldDataType === 'multipicklist') {
				if ($this->isRoleBased() && !$skipCheckingRole) {
					$userModel = Users_Record_Model::getCurrentUserModel();
					$picklistValues = \App\Fields\Picklist::getRoleBasedPicklistValues($this->getName(), $userModel->get('roleid'));
				} else {
					$picklistValues = App\Fields\Picklist::getValuesName($this->getName());
				}
				$fieldPickListValues = [];
				foreach ($picklistValues as $value) {
					$fieldPickListValues[$value] = \App\Language::translate($value, $this->getModuleName());
				}
				// Protection against deleting a value that does not exist on the list
				if ($fieldDataType === 'picklist') {
					$fieldValue = $this->get('fieldvalue');
					if (!empty($fieldValue) && !isset($fieldPickListValues[$fieldValue])) {
						$fieldPickListValues[$fieldValue] = \App\Purifier::decodeHtml($fieldValue);
						$this->set('isEditableReadOnly', true);
					}
				}
				$this->picklistValues = $fieldPickListValues;
			} elseif (method_exists($this->getUITypeModel(), 'getPicklistValues')) {
				$this->picklistValues = $this->getUITypeModel()->getPicklistValues();
			}
		}
		return $this->picklistValues;
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
			10 => 'LBL_DISPLAY_TYPE_10',
		];
	}

	/**
	 * Function to check if the current field is mandatory or not.
	 *
	 * @return bool - true/false
	 */
	public function isMandatory()
	{
		$typeOfData = explode('~', $this->get('typeofdata'));
		return (isset($typeOfData[1]) && $typeOfData[1] === 'M') ? true : false;
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
		if ($this->getFieldDataType() === 'reference') {
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
		if ($this->getDisplayType() === 4 || in_array($this->get('presence'), [1, 3])) {
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
		if (!$this->isViewEnabled() || !$this->isActiveReference() || (($this->get('uitype') === 306 || $this->get('uitype') === 307 || $this->get('uitype') === 311 || $this->get('uitype') === 312) && $this->getDisplayType() === 2)) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check if the field is export table.
	 *
	 * @return bool
	 */
	public function isExportTable()
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
		if (!$this->isViewable() || $this->getDisplayType() === 3 || $this->getDisplayType() === 5) {
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
		$displayType = $this->get('displaytype');
		if (!$this->isViewEnabled() || $displayType === 4 || $displayType === 5 ||
			strcasecmp($this->getFieldDataType(), 'autogenerated') === 0 ||
			strcasecmp($this->getFieldDataType(), 'id') === 0 ||
			$this->isReadOnly() === true) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check whether the current field is editable.
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		$displayType = $this->get('displaytype');
		if (!$this->isWritable() || ($displayType !== 1 && $displayType !== 10) || $this->isReadOnly() === true || $this->get('uitype') === 4) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check whether field is ajax editable.
	 *
	 * @return bool
	 */
	public function isAjaxEditable()
	{
		$ajaxRestrictedFields = ['4', '72', '10', '300', '51', '59'];
		if (!$this->isEditable() || in_array($this->get('uitype'), $ajaxRestrictedFields) || !$this->getUITypeModel()->isAjaxEditable() || (int) $this->get('displaytype') === 10) {
			return false;
		}
		return true;
	}

	public function isEditableReadOnly()
	{
		if ($this->get('isEditableReadOnly') !== null) {
			return $this->get('isEditableReadOnly');
		}
		if ((int) $this->get('displaytype') === 10) {
			return true;
		}
		return false;
	}

	public function isQuickCreateEnabled()
	{
		$moduleModel = $this->getModule();
		$quickCreate = $this->get('quickcreate');
		if (($quickCreate == self::QUICKCREATE_MANDATORY || $quickCreate == self::QUICKCREATE_ENABLED || $this->isMandatory()) && method_exists($moduleModel, 'isQuickCreateSupported') && $moduleModel->isQuickCreateSupported()) {
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
		if ($this->getFieldDataType() === 'reference' && empty($this->getReferenceList())) {
			return false;
		}
		return true;
	}

	/**
	 * If the field is sortable in ListView.
	 */
	public function isListviewSortable()
	{
		return $this->getUITypeModel()->isListviewSortable();
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
			$fieldModel->$properName = $propertyValue;
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
		return "{$this->getModuleName()}:{$this->get('name')}" . ($sourceFieldName ? ":$sourceFieldName" : '');
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
		if ($this->getFieldDataType() === 'reference') {
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
	 * @return <Array> - array of field values
	 */
	public function getFieldInfo()
	{
		$fieldDataType = $this->getFieldDataType();
		$this->fieldInfo['mandatory'] = $this->isMandatory();
		$this->fieldInfo['presence'] = $this->isActiveField();
		$this->fieldInfo['quickcreate'] = $this->isQuickCreateEnabled();
		$this->fieldInfo['masseditable'] = $this->isMassEditable();
		$this->fieldInfo['header_field'] = $this->getHeaderField();
		$this->fieldInfo['maxlengthtext'] = $this->get('maxlengthtext');
		$this->fieldInfo['maximumlength'] = $this->get('maximumlength');
		$this->fieldInfo['maxwidthcolumn'] = $this->get('maxwidthcolumn');
		$this->fieldInfo['defaultvalue'] = $this->getDefaultFieldValue();
		$this->fieldInfo['type'] = $fieldDataType;
		$this->fieldInfo['name'] = $this->get('name');
		$this->fieldInfo['label'] = App\Language::translate($this->get('label'), $this->getModuleName());

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
				$pickListValues = $this->getPicklistValues();
				if (!empty($pickListValues)) {
					$this->fieldInfo['picklistvalues'] = $pickListValues;
				} else {
					$this->fieldInfo['picklistvalues'] = [];
				}
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
				if (!AppConfig::performance('SEARCH_OWNERS_BY_AJAX') || in_array(\App\Request::_get('module'), ['CustomView', 'Workflows', 'PDF', 'MappedFields']) || \App\Request::_get('mode') === 'showAdvancedSearch') {
					$userList = \App\Fields\Owner::getInstance($this->getModuleName(), $currentUser)->getAccessibleUsers('', $fieldDataType);
					$groupList = \App\Fields\Owner::getInstance($this->getModuleName(), $currentUser)->getAccessibleGroups('', $fieldDataType);
					$pickListValues = [];
					$pickListValues[\App\Language::translate('LBL_USERS', $this->getModuleName())] = $userList;
					$pickListValues[\App\Language::translate('LBL_GROUPS', $this->getModuleName())] = $groupList;
					$this->fieldInfo['picklistvalues'] = $pickListValues;
					if (AppConfig::performance('SEARCH_OWNERS_BY_AJAX')) {
						$this->fieldInfo['searchOperator'] = 'e';
					}
				} else {
					if ($fieldDataType === 'owner') {
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
				break;
			case 'email':
				if (\App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_ACTIVE') && !empty(\App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_VALUES'))) {
					$validate = false;
					if (empty(\App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_ALLOWED')) || in_array($this->getModuleName(), \App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_ALLOWED'))) {
						$validate = true;
					}
					if (in_array($this->getModuleName(), \App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_EXCLUDED'))) {
						$validate = false;
					}
					if ($validate) {
						$this->fieldInfo['restrictedDomains'] = \App\Config::security('EMAIL_FIELD_RESTRICTED_DOMAINS_VALUES');
					}
				}
				break;
			case 'multiImage':
				$params = $this->getFieldParams();
				$this->fieldInfo['limit'] = $params['limit'] ?? Vtiger_MultiImage_File::$defaultLimit;
				$this->fieldInfo['formats'] = $params['formats'] ?? \App\Fields\File::$allowedFormats['image'];
				break;
			case 'image':
				$params = $this->getFieldParams();
				$this->fieldInfo['limit'] = $params['limit'] ?? Vtiger_Image_UIType::$defaultLimit;
				$this->fieldInfo['formats'] = $params['formats'] ?? \App\Fields\File::$allowedFormats['image'];
				break;
			default:
				break;
		}
		return $this->fieldInfo;
	}

	public function setFieldInfo($fieldInfo)
	{
		$this->fieldInfo = $fieldInfo;
	}

	/**
	 * Function to get the advanced filter option names by Field type.
	 *
	 * @return <Array>
	 */
	public static function getAdvancedFilterOpsByFieldType()
	{
		return [
			'V' => ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny', 'om', 'wr', 'nwr', 'd'],
			'N' => ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny', 'd'],
			'T' => ['e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a', 'y', 'ny', 'd'],
			'I' => ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny', 'd'],
			'C' => ['e', 'n', 'y', 'ny', 'd'],
			'D' => ['e', 'n', 'bw', 'b', 'a', 'y', 'ny', 'd'],
			'DT' => ['e', 'n', 'bw', 'b', 'a', 'y', 'ny', 'd'],
			'NN' => ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny', 'd'],
			'E' => ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny', 'd'],
		];
	}

	/**
	 * Function to retrieve field model for specific block and module.
	 *
	 * @param Vtiger_Module_Model $blockModel - block instance
	 *
	 * @return <array> List of field model
	 */
	public static function getAllForModule(vtlib\ModuleBasic $moduleModel)
	{
		if (\App\Cache::has('ModuleFields', $moduleModel->id)) {
			return \App\Cache::get('ModuleFields', $moduleModel->id);
		}
		$fieldModelList = [];
		$fieldObjects = parent::getAllForModule($moduleModel);
		$fieldModelList = [];
		//if module dont have any fields
		if (!is_array($fieldObjects)) {
			$fieldObjects = [];
		}
		foreach ($fieldObjects as &$fieldObject) {
			$fieldModelObject = self::getInstanceFromFieldObject($fieldObject);
			$block = $fieldModelObject->get('block') ? $fieldModelObject->get('block')->id : 0;
			$fieldModelList[$block][] = $fieldModelObject;
			Vtiger_Cache::set('field-' . $moduleModel->getId(), $fieldModelObject->getId(), $fieldModelObject);
			Vtiger_Cache::set('field-' . $moduleModel->getId(), $fieldModelObject->getName(), $fieldModelObject);
		}
		\App\Cache::save('ModuleFields', $moduleModel->id, $fieldModelList);
		return $fieldModelList;
	}

	/**
	 * Function to get instance.
	 *
	 * @param string $value  - fieldname or fieldid
	 * @param <type> $module - optional - module instance
	 *
	 * @return <Vtiger_Field_Model>
	 */
	public static function getInstance($value, $module = false)
	{
		$fieldObject = null;
		if ($module) {
			$fieldObject = Vtiger_Cache::get('field-' . $module->getId(), $value);
		}
		if (!$fieldObject) {
			$fieldObject = parent::getInstance($value, $module);
			if ($module) {
				Vtiger_Cache::set('field-' . $module->getId(), $value, $fieldObject);
			}
		}

		if ($fieldObject) {
			return self::getInstanceFromFieldObject($fieldObject);
		}
		return false;
	}

	/**
	 * Returns instance of field.
	 *
	 * @param string|array $fieldInfo
	 *
	 * @return bool|null|\Vtiger_Field_Model|\vtlib\Field
	 */
	public static function getInstanceFromFilter($fieldInfo)
	{
		if (is_string($fieldInfo)) {
			$fieldInfo = array_combine(['module_name', 'field_name', 'source_field_name'], array_pad(explode(':', $fieldInfo), 3, false));
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
	 * Function returns Client Side Validators name.
	 *
	 * @return <Array> [name=>Name of the Validator, params=>Extra Parameters]
	 */
	public function getValidator()
	{
		$validator = [];
		$fieldName = $this->getName();
		switch ($fieldName) {
			case 'birthday':
				$funcName = ['name' => 'lessThanToday'];
				array_push($validator, $funcName);
				break;
			case 'support_end_date':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['support_start_date'], ];
				array_push($validator, $funcName);
				break;
			case 'support_start_date':
				$funcName = ['name' => 'lessThanDependentField',
					'params' => ['support_end_date'], ];
				array_push($validator, $funcName);
				break;
			case 'targetenddate':
			case 'actualenddate':
			case 'enddate':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['startdate'], ];
				array_push($validator, $funcName);
				break;
			case 'startdate':
				if ($this->getModule()->get('name') === 'Project') {
					$params = ['targetenddate'];
				} else {
					//for project task
					$params = ['enddate'];
				}
				$funcName = ['name' => 'lessThanDependentField',
					'params' => $params, ];
				array_push($validator, $funcName);
				break;
			case 'expiry_date':
			case 'due_date':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['start_date'], ];
				array_push($validator, $funcName);
				break;
			case 'sales_end_date':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['sales_start_date'], ];
				array_push($validator, $funcName);
				break;
			case 'sales_start_date':
				$funcName = ['name' => 'lessThanDependentField',
					'params' => ['sales_end_date'], ];
				array_push($validator, $funcName);
				break;
			case 'qty_per_unit':
			case 'qtyindemand':
			case 'hours':
			case 'days':
				$funcName = ['name' => 'PositiveNumber'];
				array_push($validator, $funcName);
				break;
			case 'employees':
				$funcName = ['name' => 'WholeNumber'];
				array_push($validator, $funcName);
				break;
			case 'related_to':
				$funcName = ['name' => 'ReferenceField'];
				array_push($validator, $funcName);
				break;
			//SRecurringOrders field sepecial validators
			case 'end_period':
				$funcName1 = ['name' => 'greaterThanDependentField',
					'params' => ['start_period'], ];
				array_push($validator, $funcName1);
				$funcName2 = ['name' => 'lessThanDependentField',
					'params' => ['duedate'], ];
				array_push($validator, $funcName2);

			// no break
			case 'start_period':
				$funcName = ['name' => 'lessThanDependentField',
					'params' => ['end_period'], ];
				array_push($validator, $funcName);
				break;
			default:
				break;
		}
		return $validator;
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
	 * Function returns list of Currencies available in the system.
	 *
	 * @return array
	 */
	public function getCurrencyList()
	{
		if (\App\Cache::has('Currency', 'List')) {
			return \App\Cache::get('Currency', 'List');
		}
		$currencies = (new \App\Db\Query())->select(['id', 'currency_name'])
			->from('vtiger_currency_info')
			->where(['currency_status' => 'Active', 'deleted' => 0])
			->createCommand()->queryAllByGroup();
		asort($currencies);
		\App\Cache::save('Currency', 'List', $currencies, \App\Cache::LONG);

		return $currencies;
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
	 * @param type $value in user format
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
		return \App\Field::getFieldPermission($this->getModuleId(), $this->getName(), $readOnly);
	}

	public function __update()
	{
		$db = \App\Db::getInstance();
		$this->get('generatedtype') === 1 ? $generatedType = 1 : $generatedType = 2;
		$db->createCommand()->update('vtiger_field', ['typeofdata' => $this->get('typeofdata'), 'presence' => $this->get('presence'), 'quickcreate' => $this->get('quickcreate'),
			'masseditable' => $this->get('masseditable'), 'header_field' => $this->get('header_field'), 'maxlengthtext' => $this->get('maxlengthtext'),
			'maxwidthcolumn' => $this->get('maxwidthcolumn'), 'defaultvalue' => $this->get('defaultvalue'), 'summaryfield' => $this->get('summaryfield'),
			'displaytype' => $this->get('displaytype'), 'helpinfo' => $this->get('helpinfo'), 'generatedtype' => $generatedType,
			'fieldparams' => $this->get('fieldparams'),
		], ['fieldid' => $this->get('id')])->execute();
		if ($this->isMandatory()) {
			$db->createCommand()->update('vtiger_blocks_hide', ['enabled' => 0], ['blockid' => $this->getBlockId()])->execute();
		}
		App\Cache::clear();
	}

	public function updateTypeofDataFromMandatory($mandatoryValue = 'O')
	{
		$mandatoryValue = strtoupper($mandatoryValue);
		$supportedMandatoryLiterals = ['O', 'M'];
		if (!in_array($mandatoryValue, $supportedMandatoryLiterals)) {
			return;
		}
		$typeOfData = $this->get('typeofdata');
		$components = explode('~', $typeOfData);
		$components[1] = $mandatoryValue;
		$this->set('typeofdata', implode('~', $components));

		return $this;
	}

	public function isCustomField()
	{
		return ($this->generatedtype == 2) ? true : false;
	}

	public function hasDefaultValue()
	{
		return $this->defaultvalue == '' ? false : true;
	}

	public function isActiveField()
	{
		$presence = $this->get('presence');

		return in_array($presence, [0, 2]);
	}

	public function isMassEditable()
	{
		return $this->masseditable == 1 ? true : false;
	}

	public function isHeaderField()
	{
		return !empty($this->header_field) ? true : false;
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
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return mixed
	 */
	public function getHeaderValue(string $type)
	{
		return $this->getHeaderField()[$type] ?? '';
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

	public function isReferenceField()
	{
		return in_array($this->getFieldDataType(), self::$referenceTypes);
	}

	public function isOwnerField()
	{
		return ($this->getFieldDataType() == self::OWNER_TYPE) ? true : false;
	}

	/**
	 * Is summation field.
	 *
	 * @return bool
	 */
	public function isCalculateField()
	{
		return $this->isCalculateField && ($this->getUIType() === 71 || $this->getUIType() === 7);
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
		$fieldModel = Vtiger_Cache::get('FieldModel', $fieldId);
		if ($fieldModel) {
			return $fieldModel;
		}
		$field = \App\Field::getFieldInfo($fieldId);
		$className = Vtiger_Loader::getComponentClassName('Model', 'Field', \App\Module::getModuleName($field['tabid']));
		$fieldModel = new $className();
		$fieldModel->initialize($field, $field['tabid']);
		Vtiger_Cache::set('FieldModel', $fieldId, $fieldModel);

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
		$data = \App\Json::decode($this->get('fieldparams'));
		if (!is_array($data)) {
			$data = $this->get('fieldparams');
			if (empty($data)) {
				return [];
			}
		}
		return $data;
	}

	public function isActiveSearchView()
	{
		if ($this->get('fromOutsideList')) {
			return false;
		}
		return $this->getUITypeModel()->isActiveSearchView();
	}

	/**
	 * Function returns info about field structure in database.
	 *
	 * @param bool $returnString
	 *
	 * @return string|array
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
				if ($data['type'] === 'decimal') {
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
	 * @return string
	 */
	public function getRangeValues()
	{
		$uiTypeModel = $this->getUITypeModel();
		if (method_exists($uiTypeModel, 'getRangeValues')) {
			return $uiTypeModel->getRangeValues();
		}
		$allowedTypes = $uiTypeModel->getAllowedColumnTypes();
		if ($allowedTypes === null) {
			return;
		}
		$data = $this->getDBColumnType(false);
		if (!in_array($data['type'], $allowedTypes)) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_TYPE');
		}
		switch ($data['type']) {
			case 'binary':
			case 'string':
				return $data['size'];
			case 'integer':
				if ($data['unsigned']) {
					return '4294967295';
				} else {
					return '-2147483648,2147483647';
				}
			// no break
			case 'smallint':
				if ($data['unsigned']) {
					return '65535';
				} else {
					return '-32768,32767';
				}
			// no break
			case 'tinyint':
				if ($data['unsigned']) {
					return '255';
				} else {
					return '-128,127';
				}
			// no break
			case 'decimal':
				return pow(10, $data['size'] - $data['scale']) - 1;
			default:
				return null;
		}
	}

	/**
	 * Return allowed operators for field.
	 *
	 * @return string[]
	 */
	public function getOperators()
	{
		$operators = $this->getUITypeModel()->getOperators();
		$oper = [];
		foreach ($operators as $op) {
			$label = '';
			if (isset(\App\CustomView::ADVANCED_FILTER_OPTIONS[$op])) {
				$label = \App\CustomView::ADVANCED_FILTER_OPTIONS[$op];
			}
			if (isset(\App\CustomView::DATE_FILTER_CONDITIONS[$op])) {
				$label = \App\CustomView::DATE_FILTER_CONDITIONS[$op]['label'];
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
		if (in_array($operator, App\CustomView::FILTERS_WITHOUT_VALUES + array_keys(App\CustomView::DATE_FILTER_CONDITIONS))) {
			return;
		}
		return $this->getUITypeModel()->getOperatorTemplateName($operator);
	}
}
