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
	private $pearDB;
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

	private function __construct($adb, $row)
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
		$this->pearDB = $adb;
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

	public static function fromQueryResult($adb, $result, $rowNumber)
	{
		return new WebserviceField($adb, $adb->query_result_rowdata($result, $rowNumber));
	}

	public static function fromArray($adb, $row)
	{
		return new WebserviceField($adb, $row);
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
		return \includes\utils\Json::decode($this->fieldparams);
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
			$dbMetaColumns = $this->pearDB->getColumnsMeta($this->getTableName());
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
			if ($fieldDataType == 'date' || $fieldDataType == 'datetime' || $fieldDataType == 'time') {
				$tableFieldDataType = $this->getFieldTypeFromTable();
				if ($tableFieldDataType == 'datetime') {
					$fieldDataType = $tableFieldDataType;
				}
			}
			$this->fieldDataType = $fieldDataType;
		}
		return $this->fieldDataType;
	}

	public function getReferenceList()
	{
		if ($this->referenceList === null) {
			$referenceList = Vtiger_Cache::get('getReferenceList', $this->getFieldId());
			if ($referenceList !== false) {
				return $referenceList;
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
					$sql = "select vtiger_ws_referencetype.`type` from vtiger_ws_referencetype INNER JOIN vtiger_tab ON vtiger_tab.`name` = vtiger_ws_referencetype.`type` where fieldtypeid=? && vtiger_tab.`presence` NOT IN (?)";
					$params = array($fieldTypeData['fieldtypeid'], 1);
				} else {
					$sql = 'select relmodule as type from vtiger_fieldmodulerel INNER JOIN vtiger_tab ON vtiger_tab.`name` = vtiger_fieldmodulerel.`relmodule` WHERE fieldid=? && vtiger_tab.`presence` NOT IN (?) ORDER BY sequence ASC';
					$params = array($this->getFieldId(), 1);
				}
				$result = $this->pearDB->pquery($sql, $params);
				$numRows = $this->pearDB->num_rows($result);
				for ($i = 0; $i < $numRows; ++$i) {
					$referenceType = $this->pearDB->query_result($result, $i, "type");
					if (in_array($referenceType, $accessibleTypes))
						array_push($referenceTypes, $referenceType);
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
			Vtiger_Cache::set('getReferenceList', $this->getFieldId(), $referenceTypesSorted);
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
			$result = $this->pearDB->pquery('select * from vtiger_ws_fieldtype', []);
			while ($resultrow = $this->pearDB->fetch_array($result)) {
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
			$hardCodedPickListNames = array("hdntaxtype", "email_flag");
			$hardCodedPickListValues = array(
				"hdntaxtype" => array(
					array("label" => "Individual", "value" => "individual"),
					array("label" => "Group", "value" => "group")
				),
				"email_flag" => array(
					array('label' => 'SAVED', 'value' => 'SAVED'),
					array('label' => 'SENT', 'value' => 'SENT'),
					array('label' => 'MAILSCANNER', 'value' => 'MAILSCANNER')
				)
			);
			if (in_array(strtolower($this->getFieldName()), $hardCodedPickListNames)) {
				return $hardCodedPickListValues[strtolower($this->getFieldName())];
			}
			$picklistDetails = $this->getPickListOptions($this->getFieldName());
			$cache->setPicklistDetails($this->getTabId(), $this->getFieldName(), $picklistDetails);
			return $picklistDetails;
		}
	}

	public function getPickListOptions()
	{
		$fieldName = $this->getFieldName();

		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$options = [];
		$sql = "select * from vtiger_picklist where name=?";
		$result = $this->pearDB->pquery($sql, array($fieldName));
		$numRows = $this->pearDB->num_rows($result);
		if ($numRows == 0) {
			$sql = "select * from vtiger_$fieldName";
			$result = $this->pearDB->pquery($sql, []);
			$numRows = $this->pearDB->num_rows($result);
			for ($i = 0; $i < $numRows; ++$i) {
				$elem = [];
				$picklistValue = $this->pearDB->query_result($result, $i, $fieldName);
				$picklistValue = decode_html($picklistValue);
				$moduleName = getTabModuleName($this->getTabId());
				if ($moduleName == 'Events')
					$moduleName = 'Calendar';
				$elem["label"] = \includes\Language::translate($picklistValue, $moduleName);
				$elem["value"] = $picklistValue;
				array_push($options, $elem);
			}
		}else {
			$user = VTWS_PreserveGlobal::getGlobal('current_user');
			$details = \includes\fields\Picklist::getRoleBasedPicklistValues($fieldName, $user->roleid);
			for ($i = 0; $i < sizeof($details); ++$i) {
				$elem = [];
				$picklistValue = decode_html($details[$i]);
				$moduleName = getTabModuleName($this->getTabId());
				if ($moduleName == 'Events')
					$moduleName = 'Calendar';
				$elem["label"] = \includes\Language::translate($picklistValue, $moduleName);
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
		$result = $this->pearDB->pquery('SELECT module FROM vtiger_trees_templates WHERE templateid = ?', [$this->getFieldParams()]);
		$module = $this->pearDB->getSingleValue($result);
		$moduleName = getTabModuleName($module);

		$result = $this->pearDB->pquery('SELECT tree,label FROM vtiger_trees_templates_data WHERE templateid = ?', [$this->getFieldParams()]);
		while ($row = $this->pearDB->fetch_array($result)) {
			self::$treeDetails[$row['tree']] = \includes\Language::translate($row['label'], $moduleName);
		}
		return self::$treeDetails;
	}
}
