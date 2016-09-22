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
 * Vtiger Field Model Class
 */
class Vtiger_Field_Model extends vtlib\Field
{

	var $webserviceField = false;

	const REFERENCE_TYPE = 'reference';

	public static $REFERENCE_TYPES = ['reference', 'referenceLink', 'referenceProcess', 'referenceSubProcess'];

	const OWNER_TYPE = 'owner';
	const CURRENCY_LIST = 'currencyList';
	const QUICKCREATE_MANDATORY = 0;
	const QUICKCREATE_NOT_ENABLED = 1;
	const QUICKCREATE_ENABLED = 2;
	const QUICKCREATE_NOT_PERMITTED = 3;

	/**
	 * Function to get the value of a given property
	 * @param <String> $propertyName
	 * @return <Object>
	 * @throws Exception
	 */
	public function get($propertyName)
	{
		if (property_exists($this, $propertyName)) {
			return $this->$propertyName;
		}
		return null;
	}

	/**
	 * Function which sets value for given name
	 * @param <String> $name - name for which value need to be assinged
	 * @param <type> $value - values that need to be assigned
	 * @return Vtiger_Field_Model
	 */
	public function set($name, $value)
	{
		$this->$name = $value;
		return $this;
	}

	/**
	 * Function to get the Field Id
	 * @return <Number>
	 */
	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getFieldName()
	{
		return $this->name;
	}

	public function getFieldLabel()
	{
		return $this->label;
	}

	/**
	 * Function to retrieve full data
	 * @return <array>
	 */
	public function getData()
	{
		return get_object_vars($this);
	}

	public function getModule()
	{
		if (!isset($this->module)) {
			$moduleObj = $this->block->module;
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
	 * Function to retieve display value for a value
	 * @param <String> $value - value which need to be converted to display value
	 * @return <String> - converted display value
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		return $this->getUITypeModel()->getDisplayValue($value, $record, $recordInstance, $rawText);
	}

	/**
	 * Function to retrieve display type of a field
	 * @return <String> - display type of the field
	 */
	public function getDisplayType()
	{
		return $this->get('displaytype');
	}

	/**
	 * Function to get the Webservice Field Object for the current Field Object
	 * @return WebserviceField instance
	 */
	public function getWebserviceFieldObject()
	{
		if ($this->webserviceField == false) {
			$db = PearDatabase::getInstance();

			$row = [];
			$row['uitype'] = $this->get('uitype');
			$row['block'] = $this->get('block');
			$row['tablename'] = $this->get('table');
			$row['columnname'] = $this->get('column');
			$row['fieldname'] = $this->get('name');
			$row['fieldlabel'] = $this->get('label');
			$row['displaytype'] = $this->get('displaytype');
			$row['masseditable'] = $this->get('masseditable');
			$row['typeofdata'] = $this->get('typeofdata');
			$row['presence'] = $this->get('presence');
			$row['tabid'] = $this->getModuleId();
			$row['fieldid'] = $this->get('id');
			$row['readonly'] = !$this->getProfileReadWritePermission();
			$row['defaultvalue'] = $this->get('defaultvalue');
			$row['fieldparams'] = $this->get('fieldparams');
			$this->webserviceField = WebserviceField::fromArray($db, $row);
		}
		return $this->webserviceField;
	}

	/**
	 * Function to get the Webservice Field data type
	 * @return <String> Data type of the field
	 */
	public function getFieldDataType()
	{
		if (!isset($this->fieldDataType)) {
			$uiType = $this->get('uitype');
			switch ($uiType) {
				case 9: $fieldDataType = 'percentage';
					break;
				case 26: $fieldDataType = 'documentsFolder';
					break;
				case 27: $fieldDataType = 'fileLocationType';
					break;
				case 28: $fieldDataType = 'documentsFileUpload';
					break;
				case 32: $fieldDataType = 'languages';
					break;
				case 54: $fieldDataType = 'multiowner';
					break;
				case 55:
					if ($this->getName() === 'salutationtype') {
						$fieldDataType = 'picklist';
					} else if ($this->getName() === 'firstname') {
						$fieldDataType = 'salutation';
					}
					break;
				case 66: $fieldDataType = 'referenceProcess';
					break;
				case 67: $fieldDataType = 'referenceLink';
					break;
				case 68: $fieldDataType = 'referenceSubProcess';
					break;
				case 69: $fieldDataType = 'image';
					break;
				case 83: $fieldDataType = 'productTax';
					break;
				case 117: $fieldDataType = 'currencyList';
					break;
				case 120: $fieldDataType = 'sharedOwner';
					break;
				case 301: $fieldDataType = 'modules';
					break;
				case 302: $fieldDataType = 'tree';
					break;
				case 303: $fieldDataType = 'taxes';
					break;
				case 304: $fieldDataType = 'inventoryLimit';
					break;
				case 305: $fieldDataType = 'multiReferenceValue';
					break;
				case 308: $fieldDataType = 'rangeTime';
					break;
				case 309: $fieldDataType = 'categoryMultipicklist';
					break;
				default:
					$webserviceField = $this->getWebserviceFieldObject();
					$fieldDataType = $webserviceField->getFieldDataType();
					break;
			}
			$this->fieldDataType = $fieldDataType;
		}
		return $this->fieldDataType;
	}

	/**
	 * Function to get list of modules the field refernced to
	 * @return <Array> -  list of modules for which field is refered to
	 */
	public function getReferenceList()
	{
		if (method_exists($this->getUITypeModel(), 'getReferenceList')) {
			return $this->getUITypeModel()->getReferenceList();
		}

		$webserviceField = $this->getWebserviceFieldObject();
		return $webserviceField->getReferenceList();
	}

	/**
	 * Function to check if the field is named field of the module
	 * @return <Boolean> - True/False
	 */
	public function isNameField()
	{

		$nameFieldObject = Vtiger_Cache::get('EntityField', $this->getModuleName());
		if (!$nameFieldObject) {
			$moduleModel = $this->getModule();
			if (!empty($moduleModel)) {
				$moduleEntityNameFields = $moduleModel->getNameFields();
			} else {
				$moduleEntityNameFields = [];
			}
		} else {
			$moduleEntityNameFields = explode(',', $nameFieldObject->fieldname);
		}

		if (in_array($this->get('column'), $moduleEntityNameFields)) {
			return true;
		}
		return false;
	}

	/**
	 * Function to check whether the current field is read-only
	 * @return <Boolean> - true/false
	 */
	public function isReadOnly()
	{
		$webserviceField = $this->getWebserviceFieldObject();
		return $webserviceField->isReadOnly();
	}

	/**
	 * Function to get the UI Type model for the uitype of the current field
	 * @return Vtiger_Base_UIType or UI Type specific model instance
	 */
	public function getUITypeModel()
	{
		if (!$this->get('uitypeModel')) {
			$this->set('uitypeModel', Vtiger_Base_UIType::getInstanceFromField($this));
		}
		return $this->get('uitypeModel');
	}

	public function isRoleBased()
	{
		if ($this->get('uitype') == '15' || $this->get('uitype') == '33' || ($this->get('uitype') == '55' && $this->getFieldName() == 'salutationtype')) {
			return true;
		}
		return false;
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @param <Boolean> $skipCheckingRole
	 * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
	 */
	public function getPicklistValues($skipCheckingRole = false)
	{
		$fieldDataType = $this->getFieldDataType();
		if ($this->getName() == 'hdnTaxType')
			return null;

		if ($fieldDataType == 'picklist' || $fieldDataType == 'multipicklist') {
			if ($this->isRoleBased() && !$skipCheckingRole) {
				$userModel = Users_Record_Model::getCurrentUserModel();
				$picklistValues = \includes\fields\Picklist::getRoleBasedPicklistValues($this->getName(), $userModel->get('roleid'));
			} else {
				$picklistValues = Vtiger_Util_Helper::getPickListValues($this->getName());
			}

			// Protection against deleting a value that does not exist on the list
			if ($fieldDataType == 'picklist') {
				$fieldValue = $this->get('fieldvalue');
				if (!empty($fieldValue) && !in_array($this->get('fieldvalue'), $picklistValues)) {
					$picklistValues[] = $this->get('fieldvalue');
					$this->set('isEditableReadOnly', true);
				}
			}

			$fieldPickListValues = [];
			foreach ($picklistValues as $value) {
				$fieldPickListValues[$value] = vtranslate($value, $this->getModuleName());
			}
			return $fieldPickListValues;
		} else if (method_exists($this->getUITypeModel(), 'getPicklistValues')) {
			return $this->getUITypeModel()->getPicklistValues();
		}
		return null;
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
	 */
	public function getModulesListValues($onlyActive = true)
	{
		$adb = PearDatabase::getInstance();
		$modules = [];
		$params = [];
		if ($onlyActive) {
			$where .= ' WHERE presence = ? && isentitytype = ?';
			array_push($params, 0);
			array_push($params, 1);
		}
		$result = $adb->pquery(sprintf('SELECT tabid, name, ownedby FROM vtiger_tab %s', $where), $params);
		while ($row = $adb->fetch_array($result)) {
			$modules[$row['tabid']] = array('name' => $row['name'], 'label' => vtranslate($row['name'], $row['name']));
		}
		return $modules;
	}

	public static function showDisplayTypeList()
	{
		$displayType = array(
			1 => 'LBL_DISPLAY_TYPE_1',
			2 => 'LBL_DISPLAY_TYPE_2',
			3 => 'LBL_DISPLAY_TYPE_3',
			4 => 'LBL_DISPLAY_TYPE_4',
			//5 => 'LBL_DISPLAY_TYPE_5',
			10 => 'LBL_DISPLAY_TYPE_10'
		);
		return $displayType;
	}

	/**
	 * Function to check if the current field is mandatory or not
	 * @return <Boolean> - true/false
	 */
	public function isMandatory()
	{
		$typeOfData = explode('~', $this->get('typeofdata'));
		return (isset($typeOfData[1]) && $typeOfData[1] == 'M') ? true : false;
	}

	/**
	 * Function to get the field type
	 * @return <String> type of the field
	 */
	public function getFieldType()
	{
		$webserviceField = $this->getWebserviceFieldObject();
		return $webserviceField->getFieldType();
	}

	/**
	 * Function to check if the field is shown in detail view
	 * @return <Boolean> - true/false
	 */
	public function isViewEnabled()
	{
		$permision = $this->getPermissions();
		if ($this->getDisplayType() == '4' || in_array($this->get('presence'), array(1, 3))) {
			return false;
		}
		return $permision;
	}

	/**
	 * Function to check if the field is shown in detail view
	 * @return <Boolean> - true/false
	 */
	public function isViewable()
	{
		if (!$this->isViewEnabled() || !$this->isActiveReference()) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check if the field is shown in detail view
	 * @return <Boolean> - true/false
	 */
	public function isViewableInDetailView()
	{
		if (!$this->isViewable() || $this->getDisplayType() == '3' || $this->getDisplayType() == '5') {
			return false;
		}
		return true;
	}

	public function isEditEnabled()
	{
		$displayType = (int) $this->get('displaytype');
		$editEnabledDisplayTypes = [1, 3, 9, 10];
		if (!$this->isViewEnabled() ||
			!in_array($displayType, $editEnabledDisplayTypes) ||
			strcasecmp($this->getFieldDataType(), 'autogenerated') === 0 ||
			strcasecmp($this->getFieldDataType(), 'id') === 0) {

			return false;
		}
		return true;
	}

	public function isEditableReadOnly()
	{
		$isEditableReadOnly = $this->get('isEditableReadOnly');
		if ($isEditableReadOnly !== null) {
			return $isEditableReadOnly;
		}
		$displayType = (int) $this->get('displaytype');
		if ($displayType == 10) {
			return true;
		}
		return false;
	}

	public function isQuickCreateEnabled()
	{
		$moduleModel = $this->getModule();
		$quickCreate = $this->get('quickcreate');
		if (($quickCreate == self::QUICKCREATE_MANDATORY || $quickCreate == self::QUICKCREATE_ENABLED || $this->isMandatory()) && $this->get('uitype') != 69) {
			//isQuickCreateSupported will not be there for settings
			if (method_exists($moduleModel, 'isQuickCreateSupported') && $moduleModel->isQuickCreateSupported()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Function to check whether summary field or not
	 * @return <Boolean> true/false
	 */
	public function isSummaryField()
	{
		return ($this->get('summaryfield')) ? true : false;
	}

	public function isActiveReference()
	{
		if ($this->getFieldDataType() == 'reference') {
			$webserviceField = $this->getWebserviceFieldObject();
			$referenceList = $webserviceField->getReferenceList();
			foreach ($referenceList as $key => $module) {
				if (!\includes\Modules::isModuleActive($module)) {
					unset($referenceList[$key]);
				}
			}
			$webserviceField->setReferenceList($referenceList);
			if (count($referenceList) == 0) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Function to check whether the current field is editable
	 * @return <Boolean> - true/false
	 */
	public function isEditable()
	{
		if (!$this->isEditEnabled() ||
			( ((int) $this->get('displaytype')) != 1 && ((int) $this->get('displaytype')) != 10 ) ||
			$this->isReadOnly() == true || $this->get('uitype') == 4) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check whether field is ajax editable'
	 * @return <Boolean>
	 */
	public function isAjaxEditable()
	{
		$ajaxRestrictedFields = array('4', '72', '10', '300', '51', '59');
		if (!$this->isEditable() || in_array($this->get('uitype'), $ajaxRestrictedFields) || !$this->getUITypeModel()->isAjaxEditable() || (int) $this->get('displaytype') == 10) {
			return false;
		}
		return true;
	}

	/**
	 * Static Function to get the instance fo Vtiger Field Model from a given vtlib\Field object
	 * @param vtlib\Field $fieldObj - vtlib field object
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
	 * Function to get the custom view column name transformation of the field for a date field used in date filters
	 * @return <String> - tablename:columnname:fieldname:module_fieldlabel
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
	 * Function to get the custom view column name transformation of the field
	 * @return <String> - tablename:columnname:fieldname:module_fieldlabel:fieldtype
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
		$fieldType = $fieldTypeOfData[0];

		//Special condition need for reference field as they should be treated as string field
		if ($this->getFieldDataType() == 'reference') {
			$fieldType = 'V';
		} else {
			$fieldType = \vtlib\Functions::transformFieldTypeOfData($tableName, $columnName, $fieldType);
		}

		$escapedFieldLabel = str_replace(' ', '_', $fieldLabel);
		$moduleFieldLabel = $moduleName . '_' . $escapedFieldLabel;

		return $tableName . ':' . $columnName . ':' . $fieldName . ':' . $moduleFieldLabel . ':' . $fieldType;
	}

	/**
	 * Function to get the Report column name transformation of the field
	 * @return <String> - tablename:columnname:module_fieldlabel:fieldname:fieldtype
	 */
	public function getReportFilterColumnName()
	{
		$moduleName = $this->getModuleName();
		$tableName = $this->get('table');
		$columnName = $this->get('column');
		$fieldName = $this->get('name');
		$fieldLabel = $this->get('label');
		$typeOfData = $this->get('typeofdata');

		$fieldTypeOfData = explode('~', $typeOfData);
		$fieldType = $fieldTypeOfData[0];
		if ($this->getFieldDataType() == 'reference') {
			$fieldType = 'V';
		} else {
			$fieldType = \vtlib\Functions::transformFieldTypeOfData($tableName, $columnName, $fieldType);
		}
		$escapedFieldLabel = str_replace(' ', '_', $fieldLabel);
		$moduleFieldLabel = $moduleName . '_' . $escapedFieldLabel;

		if ($tableName == 'vtiger_crmentity' && $columnName != 'smownerid') {
			$tableName = 'vtiger_crmentity' . $moduleName;
		} elseif ($columnName == 'smownerid') {
			$tableName = 'vtiger_users' . $moduleName;
			$columnName = 'user_name';
		}

		return $tableName . ':' . $columnName . ':' . $moduleFieldLabel . ':' . $fieldName . ':' . $fieldType;
	}

	/**
	 * This is set from Workflow Record Structure, since workflow expects the field name
	 * in a different format in its filter. Eg: for module field its fieldname and for reference
	 * fields its reference_field_name : (reference_module_name) field - salesorder_id: (SalesOrder) subject
	 * @return <String>
	 */
	public function getWorkFlowFilterColumnName()
	{
		return $this->get('workflow_columnname');
	}

	/**
	 * Function to get the field details
	 * @return <Array> - array of field values
	 */
	public function getFieldInfo()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$fieldDataType = $this->getFieldDataType();

		$this->fieldInfo['mandatory'] = $this->isMandatory();
		$this->fieldInfo['presence'] = $this->isActiveField();
		$this->fieldInfo['quickcreate'] = $this->isQuickCreateEnabled();
		$this->fieldInfo['masseditable'] = $this->isMassEditable();
		$this->fieldInfo['header_field'] = $this->isHeaderField();
		$this->fieldInfo['maxlengthtext'] = $this->get('maxlengthtext');
		$this->fieldInfo['maxwidthcolumn'] = $this->get('maxwidthcolumn');
		$this->fieldInfo['defaultvalue'] = $this->hasDefaultValue();
		$this->fieldInfo['type'] = $fieldDataType;
		$this->fieldInfo['name'] = $this->get('name');
		$this->fieldInfo['label'] = vtranslate($this->get('label'), $this->getModuleName());

		switch ($fieldDataType) {
			case 'picklist' :
			case 'multipicklist':
			case 'multiowner':
			case 'multiReferenceValue':
			case 'posList':
				$pickListValues = $this->getPicklistValues();
				if (!empty($pickListValues)) {
					$this->fieldInfo['picklistvalues'] = $pickListValues;
				} else {
					$this->fieldInfo['picklistvalues'] = [];
				}
				break;
			case 'taxes':
				$taxs = $this->getUITypeModel()->getTaxes();
				if (!empty($taxs)) {
					$this->fieldInfo['picklistvalues'] = $taxs;
				} else {
					$this->fieldInfo['picklistvalues'] = [];
				}
				break;
			case 'inventoryLimit':
				$limits = $this->getUITypeModel()->getLimits();
				if (!empty($limits)) {
					$this->fieldInfo['picklistvalues'] = $limits;
				} else {
					$this->fieldInfo['picklistvalues'] = [];
				}
				break;
			case 'date':
			case 'datetime':
				$this->fieldInfo['date-format'] = $currentUser->get('date_format');
				break;
			case 'time':
				$this->fieldInfo['time-format'] = $currentUser->get('hour_format');
				break;
			case 'currency':
				$this->fieldInfo['currency_symbol'] = $currentUser->get('currency_symbol');
				$this->fieldInfo['decimal_seperator'] = $currentUser->get('currency_decimal_separator');
				$this->fieldInfo['group_seperator'] = $currentUser->get('currency_grouping_separator');
				break;
			case 'owner':
			case 'sharedOwner':
				if (!AppConfig::performance('SEARCH_OWNERS_BY_AJAX') || AppRequest::get('module') == 'CustomView') {
					if ($fieldDataType == 'owner') {
						$userList = \includes\fields\Owner::getInstance($this->getModuleName(), $currentUser)->getAccessibleUsers('', $fieldDataType);
						$groupList = \includes\fields\Owner::getInstance($this->getModuleName(), $currentUser)->getAccessibleGroups('', $fieldDataType);
						$pickListValues = [];
						$pickListValues[vtranslate('LBL_USERS', $this->getModuleName())] = $userList;
						$pickListValues[vtranslate('LBL_GROUPS', $this->getModuleName())] = $groupList;
						$this->fieldInfo['picklistvalues'] = $pickListValues;
						if (AppConfig::performance('SEARCH_OWNERS_BY_AJAX')) {
							$this->fieldInfo['searchOperator'] = 'e';
						}
					}
					if ($fieldDataType == 'sharedOwner') {
						$userList = \includes\fields\Owner::getInstance($this->getModuleName(), $currentUser)->getAccessibleUsers('', $fieldDataType);
						$pickListValues = [];
						$this->fieldInfo['picklistvalues'] = $userList;
					}
				} else {
					if ($fieldDataType == 'owner') {
						$this->fieldInfo['searchOperator'] = 'e';
					}
				}
				break;
			case 'modules':
				foreach ($this->getModulesListValues() as $moduleId => $module) {
					$modulesList[$module['name']] = $module['label'];
				}
				$this->fieldInfo['picklistvalues'] = $modulesList;
				break;
			case 'tree':
				$tree = $this->getUITypeModel()->getAllValue();
				$pickListValues = [];
				foreach ($tree as $key => $labels) {
					$pickListValues[$key] = $labels[0];
				}
				$this->fieldInfo['picklistvalues'] = $pickListValues;
				break;
			case 'email':
				if (AppConfig::security('RESTRICTED_DOMAINS_ACTIVE') && !empty(AppConfig::security('RESTRICTED_DOMAINS_VALUES'))) {
					$validate = false;
					if (empty(AppConfig::security('RESTRICTED_DOMAINS_ALLOWED')) || in_array($this->getModuleName(), AppConfig::security('RESTRICTED_DOMAINS_ALLOWED'))) {
						$validate = true;
					}
					if (in_array($this->getModuleName(), AppConfig::security('RESTRICTED_DOMAINS_EXCLUDED'))) {
						$validate = false;
					}
					if ($validate) {
						$this->fieldInfo['restrictedDomains'] = AppConfig::security('RESTRICTED_DOMAINS_VALUES');
					}
				}
				break;
		}

		if (in_array($fieldDataType, Vtiger_Field_Model::$REFERENCE_TYPES) && AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			$this->fieldInfo['searchOperator'] = 'e';
		}
		return $this->fieldInfo;
	}

	public function setFieldInfo($fieldInfo)
	{
		$this->fieldInfo = $fieldInfo;
	}

	/**
	 * Function to get the date values for the given type of Standard filter
	 * @param <String> $type
	 * @return <Array> - 2 date values representing the range for the given type of Standard filter
	 */
	protected static function getDateForStdFilterBytype($type)
	{
		return DateTimeRange::getDateRangeByType($type);
	}

	/**
	 * Function to get all the date filter type informations
	 * @return <Array>
	 */
	public static function getDateFilterTypes()
	{
		$dateFilters = [
			'custom' => ['label' => 'LBL_CUSTOM'],
			'prevfy' => ['label' => 'LBL_PREVIOUS_FY'],
			'thisfy' => ['label' => 'LBL_CURRENT_FY'],
			'nextfy' => ['label' => 'LBL_NEXT_FY'],
			'prevfq' => ['label' => 'LBL_PREVIOUS_FQ'],
			'thisfq' => ['label' => 'LBL_CURRENT_FQ'],
			'nextfq' => ['label' => 'LBL_NEXT_FQ'],
			'yesterday' => ['label' => 'LBL_YESTERDAY'],
			'today' => ['label' => 'LBL_TODAY'],
			'tomorrow' => ['label' => 'LBL_TOMORROW'],
			'lastweek' => ['label' => 'LBL_LAST_WEEK'],
			'thisweek' => ['label' => 'LBL_CURRENT_WEEK'],
			'nextweek' => ['label' => 'LBL_NEXT_WEEK'],
			'lastmonth' => ['label' => 'LBL_LAST_MONTH'],
			'thismonth' => ['label' => 'LBL_CURRENT_MONTH'],
			'nextmonth' => ['label' => 'LBL_NEXT_MONTH'],
			'last7days' => ['label' => 'LBL_LAST_7_DAYS'],
			'last30days' => ['label' => 'LBL_LAST_30_DAYS'],
			'last60days' => ['label' => 'LBL_LAST_60_DAYS'],
			'last90days' => ['label' => 'LBL_LAST_90_DAYS'],
			'last120days' => ['label' => 'LBL_LAST_120_DAYS'],
			'next30days' => ['label' => 'LBL_NEXT_30_DAYS'],
			'next60days' => ['label' => 'LBL_NEXT_60_DAYS'],
			'next90days' => ['label' => 'LBL_NEXT_90_DAYS'],
			'next120days' => ['label' => 'LBL_NEXT_120_DAYS']
		];

		foreach ($dateFilters as $filterType => $filterDetails) {
			$dateValues = self::getDateForStdFilterBytype($filterType);
			$dateFilters[$filterType]['startdate'] = $dateValues[0];
			$dateFilters[$filterType]['enddate'] = $dateValues[1];
		}
		return $dateFilters;
	}

	/**
	 * Function to get all the supported advanced filter operations
	 * @return <Array>
	 */
	public static function getAdvancedFilterOptions()
	{
		return array(
			'e' => 'LBL_EQUALS',
			'n' => 'LBL_NOT_EQUAL_TO',
			's' => 'LBL_STARTS_WITH',
			'ew' => 'LBL_ENDS_WITH',
			'c' => 'LBL_CONTAINS',
			'k' => 'LBL_DOES_NOT_CONTAIN',
			'l' => 'LBL_LESS_THAN',
			'g' => 'LBL_GREATER_THAN',
			'm' => 'LBL_LESS_THAN_OR_EQUAL',
			'h' => 'LBL_GREATER_OR_EQUAL',
			'b' => 'LBL_BEFORE',
			'a' => 'LBL_AFTER',
			'bw' => 'LBL_BETWEEN',
			'y' => 'LBL_IS_EMPTY',
			'ny' => 'LBL_IS_NOT_EMPTY',
			'om' => 'LBL_CURRENTLY_LOGGED_USER',
			'wr' => 'LBL_IS_WATCHING_RECORD',
			'nwr' => 'LBL_IS_NOT_WATCHING_RECORD',
		);
	}

	/**
	 * Function to get the advanced filter option names by Field type
	 * @return <Array>
	 */
	public static function getAdvancedFilterOpsByFieldType()
	{
		return array(
			'V' => ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny', 'om', 'wr', 'nwr'],
			'N' => ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'],
			'T' => ['e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a', 'y', 'ny'],
			'I' => ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'],
			'C' => ['e', 'n', 'y', 'ny'],
			'D' => ['e', 'n', 'bw', 'b', 'a', 'y', 'ny'],
			'DT' => ['e', 'n', 'bw', 'b', 'a', 'y', 'ny'],
			'NN' => ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'],
			'E' => ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny']
		);
	}

	/**
	 * Function to retrieve field model for specific block and module
	 * @param <Vtiger_Module_Model> $blockModel - block instance
	 * @return <array> List of field model
	 */
	public static function getAllForModule($moduleModel)
	{
		$fieldModelList = Vtiger_Cache::get('ModuleFields', $moduleModel->id);
		if (!$fieldModelList) {
			$fieldObjects = parent::getAllForModule($moduleModel);

			$fieldModelList = [];
			//if module dont have any fields
			if (!is_array($fieldObjects)) {
				$fieldObjects = [];
			}

			foreach ($fieldObjects as $fieldObject) {
				$fieldModelObject = self::getInstanceFromFieldObject($fieldObject);
				$block = $fieldModelObject->get('block') ? $fieldModelObject->get('block')->id : 0;
				$fieldModelList[$block][] = $fieldModelObject;
				Vtiger_Cache::set('field-' . $moduleModel->getId(), $fieldModelObject->getId(), $fieldModelObject);
				Vtiger_Cache::set('field-' . $moduleModel->getId(), $fieldModelObject->getName(), $fieldModelObject);
			}

			Vtiger_Cache::set('ModuleFields', $moduleModel->id, $fieldModelList);
		}
		return $fieldModelList;
	}

	/**
	 * Function to get instance
	 * @param <String> $value - fieldname or fieldid
	 * @param <type> $module - optional - module instance
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
	 * Added function that returns the folders in a Document
	 * @return <Array>
	 */
	public function getDocumentFolders()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT `tree`,`name` FROM
				`vtiger_trees_templates_data` 
			INNER JOIN `vtiger_field` 
				ON `vtiger_trees_templates_data`.`templateid` = `vtiger_field`.`fieldparams` 
			WHERE `vtiger_field`.`columnname` = ? 
				AND `vtiger_field`.`tablename` = ?;", array('folderid', 'vtiger_notes'));
		$rows = $adb->num_rows($result);
		$folders = [];
		for ($i = 0; $i < $rows; $i++) {
			$folderId = $adb->query_result($result, $i, 'tree');
			$folderName = $adb->query_result($result, $i, 'name');
			$folders[$folderId] = $folderName;
		}
		return $folders;
	}

	/**
	 * Function checks if the current Field is Read/Write
	 * @return <Boolean>
	 */
	public function getProfileReadWritePermission()
	{
		return $this->getPermissions('readwrite');
	}

	/**
	 * Function returns Client Side Validators name
	 * @return <Array> [name=>Name of the Validator, params=>Extra Parameters]
	 */
	public function getValidator()
	{
		$validator = [];
		$fieldName = $this->getName();
		switch ($fieldName) {
			case 'birthday' : $funcName = array('name' => 'lessThanToday');
				array_push($validator, $funcName);
				break;
			case 'support_end_date' : $funcName = array('name' => 'greaterThanDependentField',
					'params' => array('support_start_date'));
				array_push($validator, $funcName);
				break;
			case 'support_start_date' : $funcName = array('name' => 'lessThanDependentField',
					'params' => array('support_end_date'));
				array_push($validator, $funcName);
				break;
			case 'targetenddate' :
			case 'actualenddate':
			case 'enddate':
				$funcName = array('name' => 'greaterThanDependentField',
					'params' => array('startdate'));
				array_push($validator, $funcName);
				break;
			case 'startdate':
				if ($this->getModule()->get('name') == 'Project') {
					$params = array('targetenddate');
				} else {
					//for project task
					$params = array('enddate');
				}
				$funcName = array('name' => 'lessThanDependentField',
					'params' => $params);
				array_push($validator, $funcName);
				break;
			case 'expiry_date':
			case 'due_date':
				$funcName = array('name' => 'greaterThanDependentField',
					'params' => array('start_date'));
				array_push($validator, $funcName);
				break;
			case 'sales_end_date':
				$funcName = array('name' => 'greaterThanDependentField',
					'params' => array('sales_start_date'));
				array_push($validator, $funcName);
				break;
			case 'sales_start_date':
				$funcName = array('name' => 'lessThanDependentField',
					'params' => array('sales_end_date'));
				array_push($validator, $funcName);
				break;
			case 'qty_per_unit' :
			case 'qtyindemand' :
			case 'hours':
			case 'days':
				$funcName = array('name' => 'PositiveNumber');
				array_push($validator, $funcName);
				break;
			case 'employees':
				$funcName = array('name' => 'WholeNumber');
				array_push($validator, $funcName);
				break;
			case 'related_to':
				$funcName = array('name' => 'ReferenceField');
				array_push($validator, $funcName);
				break;
			//SRecurringOrders field sepecial validators
			case 'end_period' : $funcName1 = array('name' => 'greaterThanDependentField',
					'params' => array('start_period'));
				array_push($validator, $funcName1);
				$funcName2 = array('name' => 'lessThanDependentField',
					'params' => array('duedate'));
				array_push($validator, $funcName2);

			case 'start_period' :
				$funcName = array('name' => 'lessThanDependentField',
					'params' => array('end_period'));
				array_push($validator, $funcName);
				break;
		}
		return $validator;
	}

	/**
	 * Function to retrieve display value in edit view
	 * @param <String> $value - value which need to be converted to display value
	 * @return <String> - converted display value
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		return $this->getUITypeModel()->getEditViewDisplayValue($value, $record);
	}

	/**
	 * Function to retieve types of file locations in Documents Edit
	 * @return <array> - List of file location types
	 */
	public function getFileLocationType()
	{
		return array('I' => 'LBL_INTERNAL', 'E' => 'LBL_EXTERNAL');
	}

	/**
	 * Function returns list of Currencies available in the system
	 * @return <Array>
	 */
	public function getCurrencyList()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_currency_info WHERE currency_status = ? && deleted=0', array('Active'));
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$currencyId = $db->query_result($result, $i, 'id');
			$currencyName = $db->query_result($result, $i, 'currency_name');
			$currencies[$currencyId] = $currencyName;
		}
		return $currencies;
	}

	/**
	 * Function to get Display value for RelatedList
	 * @param <String> $value
	 * @return <String>
	 */
	public function getRelatedListDisplayValue($value)
	{
		return $this->getUITypeModel()->getRelatedListDisplayValue($value);
	}

	/**
	 * Function to get Default Field Value
	 * @return <String> defaultvalue
	 */
	public function getDefaultFieldValue()
	{
		return $this->defaultvalue;
	}

	/**
	 * Function whcih will get the databse insert value format from user format
	 * @param type $value in user format
	 * @return type
	 */
	public function getDBInsertValue($value)
	{
		return $this->getUITypeModel()->getDBInsertValue($value);
	}

	/**
	 * Function to get visibilty permissions of a Field
	 * @param <String> $accessmode
	 * @return <Boolean>
	 */
	public function getPermissions($accessmode = 'readonly')
	{
		$user = Users_Record_Model::getCurrentUserModel();
		$privileges = $user->getPrivileges();
		if ($privileges->hasGlobalReadPermission()) {
			return true;
		} else {
			$modulePermission = Vtiger_Cache::get('modulePermission-' . $accessmode, $this->getModuleId());
			if (!$modulePermission) {
				$modulePermission = self::preFetchModuleFieldPermission($this->getModuleId(), $accessmode);
			}
			if (array_key_exists($this->getId(), $modulePermission)) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Function to Preinitialize the module Field Permissions
	 * @param <Integer> $tabid
	 * @param <String> $accessmode
	 * @return <Array>
	 */
	public static function preFetchModuleFieldPermission($tabid, $accessmode = 'readonly')
	{
		$adb = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
		$privileges = $user->getPrivileges();
		$profilelist = $privileges->get('profiles');

		if (count($profilelist) > 0) {
			if ($accessmode == 'readonly') {
				$query = 'SELECT vtiger_profile2field.visible,vtiger_field.fieldid FROM vtiger_field INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid WHERE vtiger_field.tabid=? && vtiger_profile2field.visible=0 && vtiger_def_org_field.visible=0  && vtiger_profile2field.profileid in (%s) && vtiger_field.presence in (0,2) GROUP BY vtiger_field.fieldid';
			} else {
				$query = 'SELECT vtiger_profile2field.visible,vtiger_field.fieldid FROM vtiger_field INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid WHERE vtiger_field.tabid=? && vtiger_profile2field.visible=0 && vtiger_profile2field.readonly=0 && vtiger_def_org_field.visible=0  && vtiger_profile2field.profileid in (%s) && vtiger_field.presence in (0,2) GROUP BY vtiger_field.fieldid';
			}
			$query = sprintf($query, generateQuestionMarks($profilelist));
			$params = array($tabid, $profilelist);
		} else {
			if ($accessmode == 'readonly') {
				$query = "SELECT vtiger_profile2field.visible,vtiger_field.fieldid FROM vtiger_field INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid WHERE vtiger_field.tabid=? && vtiger_profile2field.visible=0 && vtiger_def_org_field.visible=0  && vtiger_field.presence in (0,2) GROUP BY vtiger_field.fieldid";
			} else {
				$query = "SELECT vtiger_profile2field.visible,vtiger_field.fieldid FROM vtiger_field INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid WHERE vtiger_field.tabid=? && vtiger_profile2field.visible=0 && vtiger_profile2field.readonly=0 && vtiger_def_org_field.visible=0  && vtiger_field.presence in (0,2) GROUP BY vtiger_field.fieldid";
			}
			$params = array($tabid);
		}

		$result = $adb->pquery($query, $params);
		$modulePermission = [];
		$noOfFields = $adb->num_rows($result);
		for ($i = 0; $i < $noOfFields; ++$i) {
			$row = $adb->query_result_rowdata($result, $i);
			$modulePermission[$row['fieldid']] = $row['visible'];
		}
		Vtiger_Cache::set('modulePermission-' . $accessmode, $tabid, $modulePermission);

		return $modulePermission;
	}

	public function __update()
	{
		$db = PearDatabase::getInstance();
		$this->get('generatedtype') == 1 ? $generatedtype = 1 : $generatedtype = 2;
		$query = 'UPDATE vtiger_field SET typeofdata=?, presence=?, quickcreate=?, masseditable=?, header_field=?, maxlengthtext=?, maxwidthcolumn=?, defaultvalue=?, summaryfield=?, displaytype=?, helpinfo=?, generatedtype=?, fieldparams=? WHERE fieldid=?';
		$params = array(
			$this->get('typeofdata'),
			$this->get('presence'),
			$this->get('quickcreate'),
			$this->get('masseditable'),
			$this->get('header_field'),
			$this->get('maxlengthtext'),
			$this->get('maxwidthcolumn'),
			$this->get('defaultvalue'),
			$this->get('summaryfield'),
			$this->get('displaytype'),
			$this->get('helpinfo'),
			$generatedtype,
			$this->get('fieldparams'),
			$this->get('id')
		);
		$db->pquery($query, $params);
		if ($this->isMandatory())
			$db->pquery('UPDATE vtiger_blocks_hide SET `enabled` = ? WHERE `blockid` = ?;', array(0, $this->getBlockId()));
	}

	public function updateTypeofDataFromMandatory($mandatoryValue = 'O')
	{
		$mandatoryValue = strtoupper($mandatoryValue);
		$supportedMandatoryLiterals = array('O', 'M');
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
		return in_array($presence, array(0, 2));
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
	 * Function which will check if empty piclist option should be given
	 */
	public function isEmptyPicklistOptionAllowed()
	{
		return true;
	}

	public function isReferenceField()
	{
		return in_array($this->getFieldDataType(), self::$REFERENCE_TYPES);
	}

	public function isOwnerField()
	{
		return ($this->getFieldDataType() == self::OWNER_TYPE) ? true : false;
	}

	public static function getInstanceFromFieldId($fieldId, $moduleTabId = false)
	{
		$fieldModel = Vtiger_Cache::get('FieldModel', $fieldId);
		if ($fieldModel) {
			return $fieldModel;
		}
		$field = vtlib\Functions::getModuleFieldInfoWithId($fieldId);
		$fieldModel = new self();
		$fieldModel->initialize($field);
		Vtiger_Cache::set('FieldModel', $fieldId, $fieldModel);
		return $fieldModel;
	}

	public function getWithDefaultValue()
	{
		$defaultValue = $this->getDefaultFieldValue();
		$recordValue = $this->get('fieldvalue');

		if (empty($recordValue) && !empty($defaultValue))
			$this->set('fieldvalue', $defaultValue);
		return $this;
	}

	public function getFieldParams()
	{
		return \includes\utils\Json::decode($this->get('fieldparams'));
	}

	public function isActiveSearchView()
	{
		if ($this->fromOutsideList) {
			return false;
		}
		return $this->getUITypeModel()->isActiveSearchView();
	}
}
