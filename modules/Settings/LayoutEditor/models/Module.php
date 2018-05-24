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
	 * Related view types.
	 *
	 * @var string[]
	 */
	private static $relatedViewType = [
		'RelatedTab' => 'LBL_RELATED_TAB_TYPE',
		'DetailTop' => 'LBL_DETAIL_TOP_TYPE',
		'DetailBottom' => 'LBL_DETAIL_BOTTOM_TYPE',
		'SummaryTop' => 'LBL_SUMMARY_TOP_TYPE',
		'SummaryBottom' => 'LBL_SUMMARY_BOTTOM_TYPE',
	];

	/**
	 * Function that returns all the fields for the module.
	 *
	 * @return <Array of Vtiger_Field_Model> - list of field models
	 */
	public function getFields($blockInstance = false)
	{
		if (empty($this->fieldsModule)) {
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
			if ($this->getName() === 'Events') {
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
			$this->fieldsModule = $fieldList;
		}

		return $this->fieldsModule;
	}

	/**
	 * Function returns all the blocks for the module.
	 *
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
				if ($this->getName() === 'HelpDesk' && $block->get('label') === 'LBL_COMMENTS') {
					continue;
				}

				if ($block->get('label') != 'LBL_ITEM_DETAILS') {
					$blocksList[$block->get('label')] = $block;
				}
			}
			//To handle special case for invite users block
			if ($this->getName() === 'Events') {
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

	/**
	 * List of supported field types.
	 *
	 * @return string[]
	 */
	public function getAddSupportedFieldTypes()
	{
		return [
			'Text', 'Decimal', 'Integer', 'Percent', 'Currency', 'Date', 'Email', 'Phone', 'Picklist', 'Country', 'URL', 'Checkbox', 'TextArea', 'MultiSelectCombo', 'Skype', 'Time', 'Related1M', 'Editor', 'Tree', 'MultiReferenceValue', 'CategoryMultipicklist', 'DateTime', 'Image', 'MultiImage'
		];
	}

	/**
	 * Function which will give information about the field types that are supported for add.
	 *
	 * @return <Array>
	 */
	public function getAddFieldTypeInfo()
	{
		$fieldTypesInfo = [];
		$addFieldSupportedTypes = $this->getAddSupportedFieldTypes();
		$lengthSupportedFieldTypes = ['Text', 'Decimal', 'Integer', 'Currency'];
		foreach ($addFieldSupportedTypes as $fieldType) {
			$details = [];
			if (in_array($fieldType, $lengthSupportedFieldTypes)) {
				$details['lengthsupported'] = true;
			}
			if ($fieldType === 'Decimal' || $fieldType === 'Currency') {
				$details['decimalSupported'] = true;
				$details['maxFloatingDigits'] = 5;
				if ($fieldType === 'Currency') {
					$details['decimalReadonly'] = true;
				}
				//including mantisaa and integer part
				$details['maxLength'] = 64;
			}
			if ($fieldType === 'Picklist' || $fieldType === 'MultiSelectCombo') {
				$details['preDefinedValueExists'] = true;
				//text area value type , can give multiple values
				$details['preDefinedValueType'] = 'text';
				if ($fieldType === 'Picklist') {
					$details['picklistoption'] = true;
				}
			}
			if ($fieldType === 'Related1M') {
				$details['ModuleListMultiple'] = true;
			}
			$fieldTypesInfo[$fieldType] = $details;
		}

		return $fieldTypesInfo;
	}

	/**
	 * Add field.
	 *
	 * @param string $fieldType
	 * @param int    $blockId
	 * @param array  $params
	 *
	 * @throws Exception
	 *
	 * @return \Settings_LayoutEditor_Field_Model
	 */
	public function addField($fieldType, $blockId, $params)
	{
		$label = $params['fieldLabel'];
		$type = $params['fieldTypeList'];
		$name = strtolower($params['fieldName']);
		$fieldParams = '';
		if ($this->checkFieldLableExists($label)) {
			throw new Exception(\App\Language::translate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor'), 513);
		}
		if ($this->checkFieldNameCharacters($name)) {
			throw new Exception(\App\Language::translate('LBL_INVALIDCHARACTER', 'Settings::LayoutEditor'), 512);
		}
		if ($this->checkFieldNameExists($name)) {
			throw new Exception(\App\Language::translate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor'), 512);
		}
		if ($this->checkFieldNameIsAnException($name, $params['sourceModule'])) {
			throw new Exception(\App\Language::translate('LBL_FIELD_NAME_IS_RESERVED', 'Settings::LayoutEditor'), 512);
		}
		if (strlen($name) > 30) {
			throw new Exception(\App\Language::translate('LBL_EXCEEDED_MAXIMUM_NUMBER_CHARACTERS_FOR_FIELD_NAME', 'Settings::LayoutEditor'), 512);
		}
		$supportedFieldTypes = $this->getAddSupportedFieldTypes();
		if (!in_array($fieldType, $supportedFieldTypes)) {
			throw new Exception(\App\Language::translate('LBL_WRONG_FIELD_TYPE', 'Settings::LayoutEditor'), 513);
		}
		if ($fieldType === 'Picklist' || $fieldType === 'MultiSelectCombo') {
			$pickListValues = $params['pickListValues'];
			if (is_string($pickListValues)) {
				$pickListValues = [$pickListValues];
			}
			foreach ($pickListValues as $value) {
				if (preg_match('/[\<\>\"\#\,]/', $value)) {
					throw new Exception(\App\Language::translateArgs('ERR_SPECIAL_CHARACTERS_NOT_ALLOWED', 'Other.Exceptions', '<>"#,'), 512);
				}
				if (strlen($value) > 200) {
					throw new Exception(\App\Language::translate('ERR_EXCEEDED_NUMBER_CHARACTERS', 'Other.Exceptions'), 512);
				}
			}
		}
		$moduleName = $this->getName();
		$focus = CRMEntity::getInstance($moduleName);
		if ($type == 0) {
			$columnName = $name;
			$tableName = $focus->table_name;
		} elseif ($type == 1) {
			$columnName = 'cf_' . App\Db::getInstance()->getUniqueID('vtiger_field');
			if (isset($focus->customFieldTable)) {
				$tableName = $focus->customFieldTable[0];
			} else {
				$tableName = 'vtiger_' . strtolower($moduleName) . 'cf';
			}
		}
		if ($fieldType === 'Tree' || $fieldType === 'CategoryMultipicklist') {
			$fieldParams = (int) $params['tree'];
		} elseif ($fieldType === 'MultiReferenceValue') {
			$fieldParams = [];
			$fieldParams['module'] = $params['MRVModule'];
			$fieldParams['field'] = $params['MRVField'];
			$fieldParams['filterField'] = $params['MRVFilterField'];
			$fieldParams['filterValue'] = $params['MRVFilterValue'];
			\App\Db::getInstance()->createCommand()->insert('s_#__multireference', ['source_module' => $moduleName, 'dest_module' => $params['MRVModule']])->execute();
		}
		$details = $this->getTypeDetailsForAddField($fieldType, $params);
		$uitype = $details['uitype'];
		$typeofdata = $details['typeofdata'];
		$dbType = $details['dbType'];
		$fieldModel = new Settings_LayoutEditor_Field_Model();
		$fieldModel->set('name', $columnName)
			->set('table', $tableName)
			->set('generatedtype', 2)
			->set('uitype', $uitype)
			->set('label', $label)
			->set('typeofdata', $typeofdata)
			->set('quickcreate', 1)
			->set('fieldparams', $fieldParams ? \App\Json::encode($fieldParams) : '')
			->set('columntype', $dbType);

		if (isset($details['displayType'])) {
			$fieldModel->set('displaytype', $details['displayType']);
		}
		$blockModel = Vtiger_Block_Model::getInstance($blockId, $moduleName);
		$blockModel->addField($fieldModel);
		if ($fieldType === 'Picklist' || $fieldType === 'MultiSelectCombo') {
			$fieldModel->setPicklistValues($pickListValues);
		}
		if ($fieldType === 'Related1M') {
			if (!is_array($params['referenceModule'])) {
				$moduleList[] = $params['referenceModule'];
			} else {
				$moduleList = $params['referenceModule'];
			}
			$fieldModel->setRelatedModules($moduleList);
			foreach ($moduleList as $module) {
				$targetModule = vtlib\Module::getInstance($module);
				$targetModule->setRelatedList($this, $moduleName, ['Add'], 'getDependentsList');
			}
		}
		App\Cache::clear();

		return $fieldModel;
	}

	/**
	 * Function defines details of the created field.
	 *
	 * @param string $fieldType
	 * @param array  $params
	 *
	 * @return (sting|int)[]
	 */
	public function getTypeDetailsForAddField($fieldType, $params)
	{
		$displayType = 1;
		$importerType = new \App\Db\Importers\Base();
		switch ($fieldType) {
			case 'Text':
				$fieldLength = $params['fieldLength'];
				$uichekdata = 'V~O~LE~' . $fieldLength;
				$uitype = 1;
				$type = $importerType->stringType($fieldLength)->defaultValue('');
				break;
			case 'Decimal':
				$fieldLength = $params['fieldLength'];
				$decimal = $params['decimal'];
				$uitype = 7;
				$dbfldlength = $fieldLength + $decimal + 1;
				$type = $importerType->decimal($dbfldlength, $decimal);
				// Fix for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/6363
				$uichekdata = 'NN~O';
				break;
			case 'Percent':
				$uitype = 9;
				$type = $importerType->decimal(5, 2);
				$uichekdata = 'N~O~2~2';
				break;
			case 'Currency':
				$fieldLength = $params['fieldLength'];
				$decimal = $params['decimal'];
				$uitype = 71;
				if (1 == $fieldLength) {
					$dbfldlength = $fieldLength + $decimal + 2;
				} else {
					$dbfldlength = $fieldLength + $decimal + 1;
				}
				$decimal = $decimal + 3;
				$type = $importerType->decimal($dbfldlength, $decimal);
				$uichekdata = 'N~O';
				break;
			case 'Date':
				$uichekdata = 'D~O';
				$uitype = 5;
				$type = $importerType->date();
				break;
			case 'Email':
				$uitype = 13;
				$type = $importerType->stringType(100)->defaultValue('');
				$uichekdata = 'E~O';
				break;
			case 'Time':
				$uitype = 14;
				$type = $importerType->time();
				$uichekdata = 'T~O';
				break;
			case 'Phone':
				$uitype = 11;
				$type = $importerType->stringType(30)->defaultValue('');
				$uichekdata = 'V~O';
				break;
			case 'Picklist':
				$uitype = 16;
				if (!empty($params['isRoleBasedPickList'])) {
					$uitype = 15;
				}
				$type = $importerType->stringType()->defaultValue('');
				$uichekdata = 'V~O';
				break;
			case 'URL':
				$uitype = 17;
				$type = $importerType->stringType()->defaultValue('');
				$uichekdata = 'V~O';
				break;
			case 'Checkbox':
				$uitype = 56;
				$type = $importerType->boolean()->defaultValue(false);
				$uichekdata = 'C~O';
				break;
			case 'TextArea':
				$uitype = 21;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'MultiSelectCombo':
				$uitype = 33;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'Skype':
				$uitype = 85;
				$type = $importerType->stringType()->defaultValue('');
				$uichekdata = 'V~O';
				break;
			case 'Integer':
				$fieldLength = $params['fieldLength'];
				$uitype = 7;
				if ($fieldLength > 10) {
					$type = $importerType->bigInteger($fieldLength)->defaultValue(0);
				} else {
					$type = $importerType->integer($fieldLength)->defaultValue(0);
				}
				$uichekdata = 'I~O';
				break;
			case 'Related1M':
				$uitype = 10;
				$type = $importerType->integer()->defaultValue(0)->unsigned();
				$uichekdata = 'V~O';
				break;
			case 'Editor':
				$uitype = 300;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'Tree':
				$uitype = 302;
				$type = $importerType->stringType(30)->defaultValue('');
				$uichekdata = 'V~O';
				break;
			case 'MultiReferenceValue':
				$uitype = 305;
				$type = $importerType->text();
				$uichekdata = 'C~O';
				$displayType = 5;
				break;
			case 'MultiImage':
				$uitype = 311;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'Image':
				$uitype = 69;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'CategoryMultipicklist':
				$uitype = 309;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'DateTime':
				$uichekdata = 'DT~O';
				$uitype = 79;
				$type = $importerType->dateTime();
				break;
			case 'Country':
				$uitype = 35;
				$uichekdata = 'V~O';
				$type = $importerType->text();
				break;
		}

		return [
			'uitype' => $uitype,
			'typeofdata' => $uichekdata,
			'dbType' => $type,
			'displayType' => $displayType,
		];
	}

	public function checkFieldNameCharacters($name)
	{
		if (preg_match('#[^a-z0-9_]#is', $name) || !preg_match('/[a-z]/i', $name)) {
			return true;
		}
		if (strpos($name, ' ') !== false) {
			return true;
		}

		return false;
	}

	public function checkFieldLableExists($fieldLabel)
	{
		$tabId = [$this->getId()];
		if ($this->getName() === 'Calendar' || $this->getName() === 'Events') {
			//Check for fiel exists in both calendar and events module
			$tabId = ['9', '16'];
		}
		$count = (new \App\Db\Query())->from('vtiger_field')->where(['fieldlabel' => $fieldLabel, 'tabid' => $tabId])->count();

		return ($count > 0) ? true : false;
	}

	public function checkFieldNameExists($fieldName)
	{
		$tabId = [$this->getId()];
		if ($this->getName() === 'Calendar' || $this->getName() === 'Events') {
			$tabId = [\App\Module::getModuleId('Calendar'), \App\Module::getModuleId('Events')];
		}
		$count = (new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $tabId])
			->andWhere(['or', ['fieldname' => $fieldName], ['columnname' => $fieldName]])
			->count();

		return ($count > 0) ? true : false;
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
	 * Function to get Entity module names list.
	 *
	 * @return <Array> List of Entity modules
	 */
	public static function getEntityModulesList()
	{
		$presence = [0, 2];
		$restrictedModules = ['SMSNotifier', 'Integration', 'Dashboard', 'ModComments'];

		$query = (new \App\Db\Query())->select('name')->from('vtiger_tab')->where(['presence' => $presence, 'isentitytype' => 1])->andWhere(['not in', 'name', $restrictedModules]);
		$dataReader = $query->createCommand()->query();
		$modulesList = [];
		while ($moduleName = $dataReader->read()) {
			$moduleName = $moduleName['name'];
			$modulesList[$moduleName] = $moduleName;
		}
		$dataReader->close();
		// If calendar is disabled we should not show events module too
		// in layout editor
		if (!array_key_exists('Calendar', $modulesList)) {
			unset($modulesList['Events']);
		}

		return $modulesList;
	}

	/**
	 * Function to check field is editable or not.
	 *
	 * @return bool true/false
	 */
	public function isSortableAllowed()
	{
		$moduleName = $this->getName();
		if (in_array($moduleName, ['Calendar', 'Events'])) {
			return false;
		}

		return true;
	}

	/**
	 * Function to check blocks are sortable for the module.
	 *
	 * @return bool true/false
	 */
	public function isBlockSortableAllowed()
	{
		$moduleName = $this->getName();
		if (in_array($moduleName, ['Calendar', 'Events'])) {
			return false;
		}

		return true;
	}

	/**
	 * Function to check fields are sortable for the block.
	 *
	 * @return bool true/false
	 */
	public function isFieldsSortableAllowed($blockName)
	{
		$moduleName = $this->getName();
		$blocksEliminatedArray = ['HelpDesk' => ['LBL_TICKET_RESOLUTION', 'LBL_COMMENTS'],
			'Faq' => ['LBL_COMMENT_INFORMATION'],
			'Calendar' => ['LBL_TASK_INFORMATION', 'LBL_DESCRIPTION_INFORMATION'],
			'Events' => ['LBL_EVENT_INFORMATION', 'LBL_REMINDER_INFORMATION', 'LBL_RECURRENCE_INFORMATION', 'LBL_RELATED_TO', 'LBL_DESCRIPTION_INFORMATION', 'LBL_INVITE_RECORDS'], ];
		if (in_array($moduleName, ['Calendar', 'Events', 'HelpDesk', 'Faq'])) {
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
		if ($this->getName() === 'Calendar') {
			$contactsIndex = false;
			foreach ($this->relations as $index => $model) {
				if ($model->getRelationModuleName() === 'Contacts') {
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

	/**
	 * Function returns available templates for tree type field.
	 *
	 * @param string $sourceModule
	 *
	 * @return array
	 */
	public function getTreeTemplates($sourceModule)
	{
		$sourceModule = \App\Module::getModuleId($sourceModule);
		$query = (new \App\Db\Query())->select('templateid, name')->from('vtiger_trees_templates')->where(['module' => $sourceModule])->orWhere(['like', 'share', ",$sourceModule,"]);
		$treeList = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$treeList[$row['templateid']] = $row['name'];
		}
		$dataReader->close();

		return $treeList;
	}

	public static function getRelationsTypes()
	{
		return [
			'getRelatedList' => 'PLL_RELATED_LIST',
			//'getDependentsList' => 'PLL_DEPENDENTS_LIST',
			'getManyToMany' => 'PLL_SPLITED_RELATED_LIST',
			'getAttachments' => 'PLL_ATTACHMENTS',
		];
	}

	public static function getRelationsActions()
	{
		$actionList = [
			'ADD' => 'PLL_ADD',
			'SELECT' => 'PLL_SELECT',
		];

		return $actionList;
	}

	public static function getRelationFields($moduleId)
	{
		$query = (new \App\Db\Query())->select('vtiger_field.fieldname')
			->from('vtiger_relatedlists_fields')
			->innerJoin('vtiger_field', 'vtiger_relatedlists_fields.fieldid = vtiger_field.fieldid')
			->where(['vtiger_relatedlists_fields.relation_id' => $moduleId, 'vtiger_field.presence' => [0, 2]]);
		$dataReader = $query->createCommand()->query();
		$fields = [];
		while ($row = $dataReader->read()) {
			$fields[] = $row['fieldname'];
		}
		$dataReader->close();

		return $fields;
	}

	/**
	 * Update related view type.
	 *
	 * @param int      $relationId
	 * @param string[] $type
	 */
	public static function updateRelatedViewType($relationId, $type)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_relatedlists', ['view_type' => implode(',', $type)], ['relation_id' => $relationId])->execute();
		\App\Cache::clear();
	}

	/**
	 * Get related view types.
	 *
	 * @return string[]
	 */
	public static function getRelatedViewTypes()
	{
		return static::$relatedViewType;
	}
}
