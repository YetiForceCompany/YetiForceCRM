<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Settings_LayoutEditor_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function that returns all the fields for the module
	 * @return <Array of Vtiger_Field_Model> - list of field models
	 */
	public function getFields($blockInstance = false)
	{
		if (empty($this->fields)) {
			$fieldList = [];
			$blocks = $this->getBlocks();
			$blockId = [];
			foreach ($blocks as $block) {
				//to skip events hardcoded block id
				if ($block->get('id') == 'EVENT_INVITE_USER_BLOCK_ID') {
					continue;
				}
				$blockId[] = $block->get('id');
			}
			if (count($blockId) > 0) {
				$fieldList = Settings_LayoutEditor_Field_Model::getInstanceFromBlockIdList($blockId);
			}
			//To handle special case for invite users
			if ($this->getName() == 'Events') {
				$blockModel = new Settings_LayoutEditor_Block_Model();
				$blockModel->set('id', 'EVENT_INVITE_USER_BLOCK_ID');
				$blockModel->set('label', 'LBL_INVITE_RECORDS');
				$blockModel->set('module', $this);

				$fieldModel = new Settings_LayoutEditor_Field_Model();
				$fieldModel->set('name', 'selectedusers');
				$fieldModel->set('label', 'LBL_INVITE_RECORDS');
				$fieldModel->set('block', $blockModel);
				$fieldModel->setModule($this);
				$fieldList[] = $fieldModel;
			}
			$this->fields = $fieldList;
		}
		return $this->fields;
	}

	/**
	 * Function returns all the blocks for the module
	 * @return <Array of Vtiger_Block_Model> - list of block models
	 */
	public function getBlocks()
	{
		if (empty($this->blocks)) {
			$blocksList = [];
			$moduleBlocks = Settings_LayoutEditor_Block_Model::getAllForModule($this);
			foreach ($moduleBlocks as $block) {
				if (!$block->get('label')) {
					continue;
				}
				if ($this->getName() == 'HelpDesk' && $block->get('label') == 'LBL_COMMENTS') {
					continue;
				}

				if ($block->get('label') != 'LBL_ITEM_DETAILS') {
					$blocksList[$block->get('label')] = $block;
				}
			}
			//To handle special case for invite users block
			if ($this->getName() == 'Events') {
				$blockModel = new Settings_LayoutEditor_Block_Model();
				$blockModel->set('id', 'EVENT_INVITE_USER_BLOCK_ID');
				$blockModel->set('label', 'LBL_INVITE_RECORDS');
				$blockModel->set('module', $this);
				$blocksList['LBL_INVITE_RECORDS'] = $blockModel;
			}
			$this->blocks = $blocksList;
		}
		return $this->blocks;
	}

	public function getAddSupportedFieldTypes()
	{
		return array(
			'Text', 'Decimal', 'Integer', 'Percent', 'Currency', 'Date', 'Email', 'Phone', 'Picklist', 'URL', 'Checkbox', 'TextArea', 'MultiSelectCombo', 'Skype', 'Time', 'Related1M', 'Editor', 'Tree', 'MultiReferenceValue'
		);
	}

	/**
	 * Function whcih will give information about the field types that are supported for add
	 * @return <Array>
	 */
	public function getAddFieldTypeInfo()
	{
		$fieldTypesInfo = [];
		$addFieldSupportedTypes = $this->getAddSupportedFieldTypes();
		$lengthSupportedFieldTypes = array('Text', 'Decimal', 'Integer', 'Currency');
		foreach ($addFieldSupportedTypes as $fieldType) {
			$details = [];
			if (in_array($fieldType, $lengthSupportedFieldTypes)) {
				$details['lengthsupported'] = true;
			}
			if ($fieldType == 'Decimal' || $fieldType == 'Currency') {
				$details['decimalSupported'] = true;
				$details['maxFloatingDigits'] = 5;
				if ($fieldType == 'Currency') {
					$details['decimalReadonly'] = true;
				}
				//including mantisaa and integer part
				$details['maxLength'] = 64;
			}
			if ($fieldType == 'Picklist' || $fieldType == 'MultiSelectCombo') {
				$details['preDefinedValueExists'] = true;
				//text area value type , can give multiple values
				$details['preDefinedValueType'] = 'text';
				if ($fieldType == 'Picklist')
					$details['picklistoption'] = true;
			}
			if ($fieldType == 'Related1M') {
				$details['ModuleListMultiple'] = true;
			}
			$fieldTypesInfo[$fieldType] = $details;
		}
		return $fieldTypesInfo;
	}

	public function addField($fieldType, $blockId, $params)
	{
		$db = PearDatabase::getInstance();
		$label = $params['fieldLabel'];
		$type = $params['fieldTypeList'];
		$name = strtolower($params['fieldName']);
		$fieldparams = '';
		if ($this->checkFieldLableExists($label)) {
			throw new Exception(vtranslate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor'), 513);
		}
		if ($this->checkFieldNameCharacters($name)) {
			throw new Exception(vtranslate('LBL_INVALIDCHARACTER', 'Settings::LayoutEditor'), 512);
		}
		if ($this->checkFieldNameExists($name)) {
			throw new Exception(vtranslate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor'), 512);
		}
		if ($this->checkFieldNameIsAnException($name, $params['sourceModule'])) {
			throw new Exception(vtranslate('LBL_FIELD_NAME_IS_RESERVED', 'Settings::LayoutEditor'), 512);
		}
		$supportedFieldTypes = $this->getAddSupportedFieldTypes();
		if (!in_array($fieldType, $supportedFieldTypes)) {
			throw new Exception(vtranslate('LBL_WRONG_FIELD_TYPE', 'Settings::LayoutEditor'), 513);
		}
		$moduleName = $this->getName();
		$focus = CRMEntity::getInstance($moduleName);
		if ($type == 0) {
			$columnName = $name;
			$tableName = $focus->table_name;
		} elseif ($type == 1) {
			$max_fieldid = $db->getUniqueID("vtiger_field");
			$columnName = 'cf_' . $max_fieldid;
			$custfld_fieldid = $max_fieldid;
			if (isset($focus->customFieldTable)) {
				$tableName = $focus->customFieldTable[0];
			} else {
				$tableName = 'vtiger_' . strtolower($moduleName) . 'cf';
			}
		}
		if ($fieldType == 'Tree') {
			$fieldparams = (int) $params['tree'];
		} elseif ($fieldType == 'MultiReferenceValue') {
			$fieldparams['module'] = $params['MRVModule'];
			$fieldparams['field'] = $params['MRVField'];
			$fieldparams['filterField'] = $params['MRVFilterField'];
			$fieldparams['filterValue'] = $params['MRVFilterValue'];
			$db->insert('s_yf_multireference', [
				'source_module' => $moduleName,
				'dest_module' => $params['MRVModule'],
			]);
		}
		$details = $this->getTypeDetailsForAddField($fieldType, $params);
		$uitype = $details['uitype'];
		$typeofdata = $details['typeofdata'];
		$dbType = $details['dbType'];

		$quickCreate = in_array($moduleName, getInventoryModules()) ? 3 : 1;

		$fieldModel = new Settings_LayoutEditor_Field_Model();
		$fieldModel->set('name', $columnName)
			->set('table', $tableName)
			->set('generatedtype', 2)
			->set('uitype', $uitype)
			->set('label', $label)
			->set('typeofdata', $typeofdata)
			->set('quickcreate', $quickCreate)
			->set('fieldparams', $fieldparams ? \includes\utils\Json::encode($fieldparams) : '')
			->set('columntype', $dbType);

		if (isset($details['displayType'])) {
			$fieldModel->set('displaytype', $details['displayType']);
		}
		$blockModel = Vtiger_Block_Model::getInstance($blockId, $this);
		$blockModel->addField($fieldModel);

		if ($fieldType == 'Picklist' || $fieldType == 'MultiSelectCombo') {
			$pickListValues = $params['pickListValues'];
			if (is_string($pickListValues))
				$pickListValues = [$pickListValues];
			$fieldModel->setPicklistValues($pickListValues);
		}
		if ($fieldType == 'Related1M') {
			if (!is_array($params['referenceModule']))
				$moduleList[] = $params['referenceModule'];
			else
				$moduleList = $params['referenceModule'];
			$fieldModel->setRelatedModules($moduleList);
			foreach ($moduleList as $module) {
				$targetModule = vtlib\Module::getInstance($module);
				$targetModule->setRelatedList($this, $moduleName, array('Add'), 'get_dependents_list');
			}
		}
		return $fieldModel;
	}

	public function getTypeDetailsForAddField($fieldType, $params)
	{
		$displayType = 1;
		switch ($fieldType) {
			Case 'Text' :
				$fieldLength = $params['fieldLength'];
				$uichekdata = 'V~O~LE~' . $fieldLength;
				$uitype = 1;
				$type = "VARCHAR(" . $fieldLength . ") default ''";
				break;
			Case 'Decimal' :
				$fieldLength = $params['fieldLength'];
				$decimal = $params['decimal'];
				$uitype = 7;

				$dbfldlength = $fieldLength + $decimal + 1;
				$type = "NUMERIC(" . $dbfldlength . "," . $decimal . ")";
				// Fix for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/6363
				$uichekdata = 'NN~O';
				break;
			Case 'Percent' :
				$uitype = 9;
				$type = "NUMERIC(5,2)";
				$uichekdata = 'N~O~2~2';
				break;
			Case 'Currency' :
				$fieldLength = $params['fieldLength'];
				$decimal = $params['decimal'];
				$uitype = 71;
				if (1 == $fieldLength)
					$dbfldlength = $fieldLength + $decimal + 2;
				else
					$dbfldlength = $fieldLength + $decimal + 1;
				$decimal = $decimal + 3;
				$type = "NUMERIC(" . $dbfldlength . "," . $decimal . ")";
				$uichekdata = 'N~O';
				break;
			Case 'Date' :
				$uichekdata = 'D~O';
				$uitype = 5;
				$type = "DATE";
				break;
			Case 'Email' :
				$uitype = 13;
				$type = "VARCHAR(50) default '' ";
				$uichekdata = 'E~O';
				break;
			Case 'Time' :
				$uitype = 14;
				$type = "TIME";
				$uichekdata = 'T~O';
				break;
			Case 'Phone' :
				$uitype = 11;
				$type = "VARCHAR(30) default '' ";
				$uichekdata = 'V~O';
				break;
			Case 'Picklist' :
				$uitype = 16;
				if (!empty($params['isRoleBasedPickList']))
					$uitype = 15;
				$type = "VARCHAR(255) default '' ";
				$uichekdata = 'V~O';
				break;
			Case 'URL' :
				$uitype = 17;
				$type = "VARCHAR(255) default '' ";
				$uichekdata = 'V~O';
				break;
			Case 'Checkbox' :
				$uitype = 56;
				$type = "TINYINT(1) default 0";
				$uichekdata = 'C~O';
				break;
			Case 'TextArea' :
				$uitype = 21;
				$type = "TEXT";
				$uichekdata = 'V~O';
				break;
			Case 'MultiSelectCombo' :
				$uitype = 33;
				$type = "TEXT";
				$uichekdata = 'V~O';
				break;
			Case 'Skype' :
				$uitype = 85;
				$type = "VARCHAR(255) default '' ";
				$uichekdata = 'V~O';
				break;
			Case 'Integer' :
				$fieldLength = $params['fieldLength'];
				$uitype = 7;
				if ($fieldLength > 10) {
					$type = "BIGINT(" . $fieldLength . ")";
				} else {
					$type = "INTEGER(" . $fieldLength . ")";
				}
				$uichekdata = 'I~O';
				break;
			Case 'Related1M' :
				$uitype = 10;
				$type = "INTEGER(19)";
				$uichekdata = 'V~O';
				break;
			Case 'Editor' :
				$uitype = 300;
				$type = "TEXT";
				$uichekdata = 'V~O';
				break;
			Case 'Tree' :
				$uitype = 302;
				$type = "VARCHAR(255) default '' ";
				$uichekdata = 'V~O';
				break;
			Case 'MultiReferenceValue' :
				$uitype = 305;
				$type = "TEXT";
				$uichekdata = 'C~O';
				$displayType = 5;
				break;
		}
		return array(
			'uitype' => $uitype,
			'typeofdata' => $uichekdata,
			'dbType' => $type,
			'displayType' => $displayType,
		);
	}

	public function checkFieldNameCharacters($name)
	{
		if (preg_match('#[^a-z0-9]#is', $name)) {
			return true;
		}
		if (strpos($name, ' ') !== false) {
			return true;
		}
		return false;
	}

	public function checkFieldLableExists($fieldLabel)
	{
		$db = PearDatabase::getInstance();
		$tabId = [$this->getId()];
		if ($this->getName() == 'Calendar' || $this->getName() == 'Events') {
			//Check for fiel exists in both calendar and events module
			$tabId = ['9', '16'];
		}
		$query = sprintf('SELECT 1 FROM vtiger_field WHERE tabid IN (%s) && fieldlabel = ?', $db->generateQuestionMarks($tabId));
		$result = $db->pquery($query, [$tabId, $fieldLabel]);
		return ($db->getRowCount($result) > 0 ) ? true : false;
	}

	public function checkFieldNameExists($fieldName)
	{
		$db = PearDatabase::getInstance();
		$tabId = [$this->getId()];
		if ($this->getName() == 'Calendar' || $this->getName() == 'Events') {
			$tabId = [vtlib\Functions::getModuleId('Calendar'), vtlib\Functions::getModuleId('Events')];
		}
		$query = sprintf('SELECT 1 FROM vtiger_field WHERE tabid IN (%s) && (fieldname = ? || columnname = ?)', $db->generateQuestionMarks($tabId));
		$result = $db->pquery($query, [$tabId, $fieldName, $fieldName]);
		return ($db->getRowCount($result) > 0 ) ? true : false;
	}

	public function checkFieldNameIsAnException($fieldName, $moduleName)
	{
		$exceptions = ['id', 'inventoryItemsNo', 'seq'];
		$instance = Vtiger_InventoryField_Model::getInstance($moduleName);
		foreach ($instance->getAllFields() as $field) {
			$exceptions[] = $field->getColumnName();
			if (preg_match('/^' . $field->getColumnName() . '[0-9]/', $fieldName) != 0) {
				return true;
			}
			foreach ($field->getCustomColumn() as $columnName => $dbType) {
				$exceptions[] = $columnName;
			}
		}
		return in_array($fieldName, $exceptions);
	}

	public static $supportedModules = false;

	public static function getSupportedModules()
	{
		if (empty(self::$supportedModules)) {
			self::$supportedModules = self::getEntityModulesList();
		}
		return self::$supportedModules;
	}

	public static function getInstanceByName($moduleName)
	{
		$moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		$objectProperties = get_object_vars($moduleInstance);
		$selfInstance = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$selfInstance->$properName = $propertyValue;
		}
		return $selfInstance;
	}

	/**
	 * Function to get Entity module names list
	 * @return <Array> List of Entity modules
	 */
	public static function getEntityModulesList()
	{
		$db = PearDatabase::getInstance();
		self::preModuleInitialize2();

		$presence = [0, 2];
		$restrictedModules = ['SMSNotifier', 'Emails', 'Integration', 'Dashboard', 'ModComments', 'vtmessages', 'vttwitter'];

		$query = sprintf('SELECT `name` FROM vtiger_tab WHERE presence IN (%s) && isentitytype = ? && `name` NOT IN (%s)', $db->generateQuestionMarks($presence), $db->generateQuestionMarks($restrictedModules));
		$result = $db->pquery($query, [$presence, 1, $restrictedModules]);

		$modulesList = [];
		while (($moduleName = $db->getSingleValue($result)) !== false) {
			$modulesList[$moduleName] = $moduleName;
		}
		// If calendar is disabled we should not show events module too
		// in layout editor
		if (!array_key_exists('Calendar', $modulesList)) {
			unset($modulesList['Events']);
		}
		return $modulesList;
	}

	/**
	 * Function to check field is editable or not
	 * @return <Boolean> true/false
	 */
	public function isSortableAllowed()
	{
		$moduleName = $this->getName();
		if (in_array($moduleName, array('Calendar', 'Events'))) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check blocks are sortable for the module
	 * @return <Boolean> true/false
	 */
	public function isBlockSortableAllowed()
	{
		$moduleName = $this->getName();
		if (in_array($moduleName, array('Calendar', 'Events'))) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check fields are sortable for the block
	 * @return <Boolean> true/false
	 */
	public function isFieldsSortableAllowed($blockName)
	{
		$moduleName = $this->getName();
		$blocksEliminatedArray = array('HelpDesk' => array('LBL_TICKET_RESOLUTION', 'LBL_COMMENTS'),
			'Faq' => array('LBL_COMMENT_INFORMATION'),
			'Calendar' => array('LBL_TASK_INFORMATION', 'LBL_DESCRIPTION_INFORMATION'),
			'Events' => array('LBL_EVENT_INFORMATION', 'LBL_REMINDER_INFORMATION', 'LBL_RECURRENCE_INFORMATION', 'LBL_RELATED_TO', 'LBL_DESCRIPTION_INFORMATION', 'LBL_INVITE_RECORDS'));
		if (in_array($moduleName, array('Calendar', 'Events', 'HelpDesk', 'Faq'))) {
			if (!empty($blocksEliminatedArray[$moduleName])) {
				if (in_array($blockName, $blocksEliminatedArray[$moduleName])) {
					return false;
				}
			} else {
				return false;
			}
		}
		return true;
	}

	public function getRelations()
	{
		if ($this->relations === null) {
			$this->relations = Vtiger_Relation_Model::getAllRelations($this, false);
		}

		// Contacts relation-tab is turned into custom block on DetailView.
		if ($this->getName() == 'Calendar') {
			$contactsIndex = false;
			foreach ($this->relations as $index => $model) {
				if ($model->getRelationModuleName() == 'Contacts') {
					$contactsIndex = $index;
					break;
				}
			}
			if ($contactsIndex !== false) {
				array_splice($this->relations, $contactsIndex, 1);
			}
		}

		return $this->relations;
	}

	public function getTreeTemplates($sourceModule)
	{
		$db = PearDatabase::getInstance();
		$sourceModule = vtlib\Functions::getModuleId($sourceModule);

		$query = 'SELECT templateid,name FROM vtiger_trees_templates WHERE module = ?';
		$result = $db->pquery($query, array($sourceModule));

		$treeList = [];
		while ($row = $db->getRow($result)) {
			$treeList[$row['templateid']] = $row['name'];
		}
		return $treeList;
	}

	public static function getRelationsTypes()
	{
		$typesList = array(
			'get_related_list' => 'PLL_RELATED_LIST',
			'get_dependents_list' => 'PLL_DEPENDENTS_LIST',
			'get_many_to_many' => 'PLL_SPLITED_RELATED_LIST',
			'get_attachments' => 'PLL_ATTACHMENTS',
		);
		return $typesList;
	}

	public static function getRelationsActions()
	{
		$actionList = array(
			'ADD' => 'PLL_ADD',
			'SELECT' => 'PLL_SELECT',
		);
		return $actionList;
	}
}
