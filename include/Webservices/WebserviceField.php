<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * **************************************************************************** */
require_once 'include/runtime/Cache.php';

class WebserviceField
{

	private $fieldId;
	private $uitype;
	private $blockId;
	private $blockName;
	private $nullable;
	private $default;
	private $tableName;
	private $columnName;
	private $fieldName;
	private $fieldLabel;
	private $editable;
	private $fieldType;
	private $displayType;
	private $mandatory;
	private $massEditable;
	private $tabid;
	private $presence;
	private $fieldparams;

	/**
	 *
	 * @var PearDatabase
	 */
	private $typeOfData;
	private $fieldDataType;
	private $dataFromMeta;
	private static $tableMeta = [];
	private static $fieldTypeMapping = [];
	private $referenceList;
	private $defaultValuePresent;
	private $explicitDefaultValue;
	private $genericUIType = 10;
	private $readOnly = 0;

	public function __construct($row)
	{
		$this->uitype = $row['uitype'];
		$this->blockId = $row['block'];
		$this->blockName = null;
		$this->tableName = $row['tablename'];
		$this->columnName = $row['columnname'];
		$this->fieldName = $row['fieldname'];
		$this->fieldLabel = $row['fieldlabel'];
		$this->displayType = $row['displaytype'];
		$this->massEditable = ($row['masseditable'] === '1') ? true : false;
		$typeOfData = $row['typeofdata'];
		$this->presence = $row['presence'];
		$this->typeOfData = $typeOfData;
		$typeOfData = explode('~', $typeOfData);
		$this->mandatory = (isset($typeOfData[1]) && $typeOfData[1] == 'M') ? true : false;
		if ($this->uitype == 4) {
			$this->mandatory = false;
		}
		$this->fieldType = $typeOfData[0];
		$this->tabid = $row['tabid'];
		$this->fieldId = $row['fieldid'];
		$this->fieldDataType = null;
		$this->dataFromMeta = false;
		$this->defaultValuePresent = false;
		$this->referenceList = null;
		$this->explicitDefaultValue = false;
		$this->fieldparams = $row['fieldparams'];
		$this->readOnly = (isset($row['readonly'])) ? $row['readonly'] : 0;

		if (isset($row['defaultvalue'])) {
			$this->setDefault($row['defaultvalue']);
		}
	}

	public static function fromArray($adb, $row)
	{
		return new WebserviceField($row);
	}

	public function getTableName()
	{
		return $this->tableName;
	}

	public function getFieldName()
	{
		return $this->fieldName;
	}

	public function getFieldLabelKey()
	{
		return $this->fieldLabel;
	}

	public function getFieldType()
	{
		return $this->fieldType;
	}

	public function isMandatory()
	{
		return $this->mandatory;
	}

	public function getTypeOfData()
	{
		return $this->typeOfData;
	}

	public function getDisplayType()
	{
		return $this->displayType;
	}

	public function getMassEditable()
	{
		return $this->massEditable;
	}

	public function getFieldId()
	{
		return $this->fieldId;
	}

	public function getDefault()
	{
		if ($this->dataFromMeta !== true && $this->explicitDefaultValue !== true) {
			$this->fillColumnMeta();
		}
		return $this->default;
	}

	public function getColumnName()
	{
		return $this->columnName;
	}

	public function getBlockId()
	{
		return $this->blockId;
	}

	public function getBlockName()
	{
		if (empty($this->blockName)) {
			$this->blockName = getBlockName($this->blockId);
		}
		return $this->blockName;
	}

	public function getTabId()
	{
		return $this->tabid;
	}

	public function isNullable()
	{
		if ($this->dataFromMeta !== true) {
			$this->fillColumnMeta();
		}
		return $this->nullable;
	}

	public function hasDefault()
	{
		if ($this->dataFromMeta !== true && $this->explicitDefaultValue !== true) {
			$this->fillColumnMeta();
		}
		return $this->defaultValuePresent;
	}

	public function getUIType()
	{
		return $this->uitype;
	}

	public function getFieldParams()
	{
		return \App\Json::decode($this->fieldparams);
	}

	public function isReadOnly()
	{
		if ($this->readOnly == 1)
			return true;
		return false;
	}

	private function setNullable($nullable)
	{
		$this->nullable = $nullable;
	}

	public function setDefault($value)
	{
		$this->default = $value;
		$this->explicitDefaultValue = true;
		$this->defaultValuePresent = true;
	}

	public function setFieldDataType($dataType)
	{
		$this->fieldDataType = $dataType;
	}

	public function setReferenceList($referenceList)
	{
		$this->referenceList = $referenceList;
	}

	public function getTableFields()
	{
		$tableFields = null;
		if (isset(WebserviceField::$tableMeta[$this->getTableName()])) {
			$tableFields = WebserviceField::$tableMeta[$this->getTableName()];
		} else {
			$dbMetaColumns = PearDatabase::getInstance()->getColumnsMeta($this->getTableName());
			$tableFields = [];
			foreach ($dbMetaColumns as $key => $dbField) {
				$tableFields[$dbField->name] = $dbField;
			}
			WebserviceField::$tableMeta[$this->getTableName()] = $tableFields;
		}
		return $tableFields;
	}

	public function fillColumnMeta()
	{
		$tableFields = $this->getTableFields();
		foreach ($tableFields as $fieldName => $dbField) {
			if (strcmp($fieldName, $this->getColumnName()) === 0) {
				$this->setNullable(!$dbField->notNull);
				if ($dbField->hasDefault === true && !$this->explicitDefaultValue) {
					$this->defaultValuePresent = $dbField->hasDefault;
					$this->setDefault($dbField->default);
				}
			}
		}
		$this->dataFromMeta = true;
	}

	public function getFieldDataType()
	{
		if ($this->fieldDataType === null) {
			$fieldDataType = $this->getFieldTypeFromUIType();
			if ($fieldDataType === null) {
				$fieldDataType = $this->getFieldTypeFromTypeOfData();
			}
			$this->fieldDataType = $fieldDataType;
		}
		return $this->fieldDataType;
	}

	public function getReferenceList()
	{
		if ($this->referenceList === null) {
			if (\App\Cache::has('getReferenceList', $this->getFieldId())) {
				return \App\Cache::get('getReferenceList', $this->getFieldId());
			}
			if (!isset(WebserviceField::$fieldTypeMapping[$this->getUIType()])) {
				$this->getFieldTypeFromUIType();
			}
			$fieldTypeData = WebserviceField::$fieldTypeMapping[$this->getUIType()];
			$current_user = vglobal('current_user');
			$types = vtws_listtypes(null, $current_user);

			$accessibleTypes = $types['types'];
			//If it is non admin user or the edit and view is there for profile then users module will be accessible
			if (!\vtlib\Functions::userIsAdministrator($current_user) && !in_array("Users", $accessibleTypes)) {
				array_push($accessibleTypes, 'Users');
			}

			$referenceTypes = [];
			if (!in_array($this->getUIType(), [66, 67, 68])) {
				if ($this->getUIType() != $this->genericUIType) {
					$query = (new \App\Db\Query())->select('vtiger_ws_referencetype.type')
						->from('vtiger_ws_referencetype')
						->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_ws_referencetype.type')
						->where(['fieldtypeid' => $fieldTypeData['fieldtypeid']])
						->andWhere(['not in', 'vtiger_tab.presence', [1]]);
				} else {
					$query = (new \App\Db\Query())->select('relmodule as type')
						->from('vtiger_fieldmodulerel')
						->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_fieldmodulerel.relmodule')
						->where(['fieldid' => $this->getFieldId()])
						->andWhere(['not in', 'vtiger_tab.presence', [1]])
						->orderBy(['sequence' => SORT_ASC]);
				}
				$dataReader = $query->createCommand()->query();
				while ($row = $dataReader->read()) {
					if (in_array($row['type'], $accessibleTypes))
						array_push($referenceTypes, $row['type']);
				}
			} else {
				$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($this->getFieldId());
				$referenceTypes = $fieldModel->getUITypeModel()->getReferenceList();
			}
			$referenceTypesUnsorted = array_values(array_intersect($accessibleTypes, $referenceTypes));

			$referenceTypesSorted = [];
			foreach ($referenceTypesUnsorted as $key => $reference) {
				$keySort = array_search($reference, $referenceTypes);
				$referenceTypesSorted[$keySort] = $reference;
			}
			ksort($referenceTypesSorted);
			\App\Cache::save('getReferenceList', $this->getFieldId(), $referenceTypesSorted);
			$this->referenceList = $referenceTypesSorted;
			return $referenceTypesSorted;
		}
		return $this->referenceList;
	}

	private function getFieldTypeFromTable()
	{
		$tableFields = $this->getTableFields();
		foreach ($tableFields as $fieldName => $dbField) {
			if (strcmp($fieldName, $this->getColumnName()) === 0) {
				return $dbField->type;
			}
		}
		//This should not be returned if entries in DB are correct.
		return null;
	}

	private function getFieldTypeFromTypeOfData()
	{
		switch ($this->fieldType) {
			case 'T': return 'time';
			case 'D': return 'date';
			case 'DT': return 'datetime';
			case 'E': return 'email';
			case 'N':
			case 'NN': return 'double';
			case 'P': return 'password';
			case 'I': return 'integer';
			case 'V':
			default: return 'string';
		}
	}

	private function getFieldTypeFromUIType()
	{

		// Cache all the information for futher re-use
		if (empty(self::$fieldTypeMapping)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery('select * from vtiger_ws_fieldtype', []);
			while ($resultrow = $db->fetch_array($result)) {
				self::$fieldTypeMapping[$resultrow['uitype']] = $resultrow;
			}
		}

		if (isset(WebserviceField::$fieldTypeMapping[$this->getUIType()])) {
			if (WebserviceField::$fieldTypeMapping[$this->getUIType()] === false) {
				return null;
			}
			$row = WebserviceField::$fieldTypeMapping[$this->getUIType()];
			return $row['fieldtype'];
		} else {
			WebserviceField::$fieldTypeMapping[$this->getUIType()] = false;
			return null;
		}
	}

	public function getPicklistDetails()
	{
		$cache = Vtiger_Cache::getInstance();
		if ($cache->getPicklistDetails($this->getTabId(), $this->getFieldName())) {
			return $cache->getPicklistDetails($this->getTabId(), $this->getFieldName());
		} else {
			$picklistDetails = $this->getPickListOptions($this->getFieldName());
			$cache->setPicklistDetails($this->getTabId(), $this->getFieldName(), $picklistDetails);
			return $picklistDetails;
		}
	}

	public function getPickListOptions()
	{
		$fieldName = $this->getFieldName();
		$db = PearDatabase::getInstance();
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$options = [];
		$sql = "select * from vtiger_picklist where name=?";
		$result = $db->pquery($sql, array($fieldName));
		$numRows = $db->num_rows($result);
		if ($numRows == 0) {
			$sql = "select * from vtiger_$fieldName";
			$result = $db->pquery($sql, []);
			$numRows = $db->num_rows($result);
			for ($i = 0; $i < $numRows; ++$i) {
				$elem = [];
				$picklistValue = $db->query_result($result, $i, $fieldName);
				$picklistValue = decode_html($picklistValue);
				$moduleName = \App\Module::getModuleName($this->getTabId());
				if ($moduleName == 'Events')
					$moduleName = 'Calendar';
				$elem["label"] = \App\Language::translate($picklistValue, $moduleName);
				$elem["value"] = $picklistValue;
				array_push($options, $elem);
			}
		}else {
			$user = VTWS_PreserveGlobal::getGlobal('current_user');
			$details = \App\Fields\Picklist::getRoleBasedPicklistValues($fieldName, $user->roleid);
			for ($i = 0; $i < sizeof($details); ++$i) {
				$elem = [];
				$picklistValue = decode_html($details[$i]);
				$moduleName = \App\Module::getModuleName($this->getTabId());
				if ($moduleName == 'Events')
					$moduleName = 'Calendar';
				$elem["label"] = \App\Language::translate($picklistValue, $moduleName);
				$elem["value"] = $picklistValue;
				array_push($options, $elem);
			}
		}
		return $options;
	}

	public function getPresence()
	{
		return $this->presence;
	}

	private static $treeDetails = [];

	public function getTreeDetails()
	{
		if (count(self::$treeDetails) > 0) {
			return self::$treeDetails;
		}
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT module FROM vtiger_trees_templates WHERE templateid = ?', [$this->getFieldParams()]);
		$module = $db->getSingleValue($result);
		$moduleName = \App\Module::getModuleName($module);

		$result = $db->pquery('SELECT tree,label FROM vtiger_trees_templates_data WHERE templateid = ?', [$this->getFieldParams()]);
		while ($row = $db->fetch_array($result)) {
			self::$treeDetails[$row['tree']] = \App\Language::translate($row['label'], $moduleName);
		}
		return self::$treeDetails;
	}
}
