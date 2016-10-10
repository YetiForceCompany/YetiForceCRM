<?php
/* +*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ******************************************************************************* */

require_once 'include/CRMEntity.php';
require_once 'modules/CustomView/CustomView.php';
require_once 'include/Webservices/Utils.php';
require_once 'include/Webservices/RelatedModuleMeta.php';

/**
 * Description of QueryGenerator
 *
 * @author MAK
 */
class QueryGenerator
{

	private $module;
	private $customViewColumnList;
	private $stdFilterList;
	private $conditionals;
	private $manyToManyRelatedModuleConditions;
	private $groupType;
	private $whereFields;
	private $whereOperator;

	/**
	 *
	 * @var VtigerCRMObjectMeta
	 */
	private $meta;

	/**
	 *
	 * @var Users
	 */
	private $user;
	private $advFilterList;
	private $fields;
	private $referenceModuleMetaInfo;
	private $moduleNameFields;
	private $referenceFieldInfoList;
	private $referenceFieldList;
	private $ownerFields;
	private $columns;
	private $fromClause;
	private $whereClause;
	private $query;
	private $groupInfo;
	public $conditionInstanceCount;
	private $conditionalWhere;
	public static $AND = 'AND';
	public static $OR = 'OR';
	private $customViewFields;
	private $columnsCustom;
	private $fromClauseCustom;
	private $whereClauseCustom;
	private $customTable;
	public $permissions = true;

	/**
	 * Import Feature
	 */
	private $ignoreComma;

	public function __construct($module, $user = false)
	{
		if ($user === false) {
			$user = Users_Record_Model::getCurrentUserModel();
		}
		$this->module = $module;
		$this->customViewColumnList = null;
		$this->stdFilterList = null;
		$this->conditionals = [];
		$this->user = $user;
		$this->advFilterList = null;
		$this->fields = [];
		$this->referenceModuleMetaInfo = [];
		$this->moduleNameFields = [];
		$this->whereFields = [];
		$this->groupType = self::$AND;
		$this->meta = $this->getMeta($module);
		$this->moduleNameFields[$module] = $this->meta->getNameFields();
		$this->referenceFieldInfoList = $this->meta->getReferenceFieldDetails();
		$this->referenceFieldList = array_keys($this->referenceFieldInfoList);
		;
		$this->ownerFields = $this->meta->getOwnerFields();
		$this->columns = null;
		$this->fromClause = null;
		$this->whereClause = null;
		$this->query = null;
		$this->conditionalWhere = null;
		$this->groupInfo = '';
		$this->manyToManyRelatedModuleConditions = [];
		$this->conditionInstanceCount = 0;
		$this->customViewFields = [];
		$this->columnsCustom = [];
		$this->fromClauseCustom = [];
		$this->whereClauseCustom = [];
		$this->customTable = [];
		$this->sourceRecord = false;
	}

	/**
	 *
	 * @param String:ModuleName $module
	 * @return EntityMeta
	 */
	public function getMeta($module)
	{
		$db = PearDatabase::getInstance();
		if (empty($this->referenceModuleMetaInfo[$module])) {
			$handler = vtws_getModuleHandlerFromName($module, $this->user);
			$meta = $handler->getMeta();
			$this->referenceModuleMetaInfo[$module] = $meta;
			$this->moduleNameFields[$module] = $meta->getNameFields();
		}
		return $this->referenceModuleMetaInfo[$module];
	}

	public function reset()
	{
		$this->fromClause = null;
		$this->whereClause = null;
		$this->columns = null;
		$this->query = null;
	}

	public function setFields($fields)
	{
		$this->fields = $fields;
	}

	public function getCustomViewFields()
	{
		return $this->customViewFields;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getWhereFields()
	{
		return $this->whereFields;
	}

	public function setCustomColumn($columns)
	{
		$this->columnsCustom[] = $columns;
	}

	public function setCustomTable($table)
	{
		$this->customTable[] = $table;
	}

	public function setCustomFrom($from)
	{
		$this->fromClauseCustom[] = $from;
	}

	public function setCustomCondition($where)
	{
		$this->whereClauseCustom[] = $where;
	}

	public function setConditionField($fieldName)
	{
		$this->whereFields[] = $fieldName;
	}

	public function setSourceRecord($sourceRecord)
	{
		$this->sourceRecord = $sourceRecord;
	}

	public function getSourceRecord()
	{
		return $this->sourceRecord;
	}

	public function getOwnerFieldList()
	{
		return $this->ownerFields;
	}

	public function getModuleNameFields($module)
	{
		return $this->moduleNameFields[$module];
	}

	public function getReferenceFieldList()
	{
		return $this->referenceFieldList;
	}

	public function getReferenceFieldInfoList()
	{
		return $this->referenceFieldInfoList;
	}

	public function getModule()
	{
		return $this->module;
	}

	public function getModuleFields()
	{
		$moduleFields = $this->meta->getModuleFields();

		$module = $this->getModule();
		if ($module == 'Calendar') {
			$eventmoduleMeta = $this->getMeta('Events');
			$eventModuleFieldList = $eventmoduleMeta->getModuleFields();
			$moduleFields = array_merge($moduleFields, $eventModuleFieldList);
		}
		return $moduleFields;
	}

	public function getConditionalWhere()
	{
		return $this->conditionalWhere;
	}

	public function getDefaultCustomViewQuery()
	{
		$customView = new CustomView($this->module);
		$viewId = $customView->getViewId($this->module);
		return $this->getCustomViewQueryById($viewId);
	}

	public function initForDefaultCustomView()
	{
		$customView = new CustomView($this->module);
		$viewId = $customView->getViewId($this->module);
		$this->initForCustomViewById($viewId);
	}

	public function initForCustomViewById($viewId)
	{
		$customView = new CustomView($this->module);
		$this->customViewColumnList = $customView->getColumnsListByCvid($viewId);
		if ($this->customViewColumnList) {
			foreach ($this->customViewColumnList as $customViewColumnInfo) {
				$details = explode(':', $customViewColumnInfo);
				if (empty($details[2]) && $details[1] == 'crmid' && $details[0] == 'vtiger_crmentity') {
					$name = 'id';
					$this->customViewFields[] = $name;
				} else {
					$this->fields[] = $details[2];
					$this->customViewFields[] = $details[2];
				}
			}
		}
		if ($this->module == 'Calendar' && !in_array('activitytype', $this->fields)) {
			$this->fields[] = 'activitytype';
		}

		if ($this->module == 'Documents') {
			if (in_array('filename', $this->fields)) {
				if (!in_array('filelocationtype', $this->fields)) {
					$this->fields[] = 'filelocationtype';
				}
				if (!in_array('filestatus', $this->fields)) {
					$this->fields[] = 'filestatus';
				}
			}
		}
		$this->fields[] = 'id';

		$this->stdFilterList = $customView->getStdFilterByCvid($viewId);
		$this->advFilterList = $customView->getAdvFilterByCvid($viewId);

		if (is_array($this->stdFilterList)) {
			$value = [];
			if (!empty($this->stdFilterList['columnname'])) {
				$this->startGroup('');
				$name = explode(':', $this->stdFilterList['columnname']);
				$name = $name[2];
				$value[] = $this->fixDateTimeValue($name, $this->stdFilterList['startdate']);
				$value[] = $this->fixDateTimeValue($name, $this->stdFilterList['enddate'], false);
				$this->addCondition($name, $value, 'BETWEEN');
			}
		}
		if ($this->conditionInstanceCount <= 0 && is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			$this->startGroup('');
		} elseif ($this->conditionInstanceCount > 0 && is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			$this->addConditionGlue(self::$AND);
		}
		if (is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			$this->parseAdvFilterList($this->advFilterList);
		}
		if ($this->conditionInstanceCount > 0) {
			$this->endGroup();
		}
	}

	public function parseAdvFilterList($advFilterList, $glue = '')
	{
		if (!empty($glue))
			$this->addConditionGlue($glue);

		$customView = new CustomView($this->module);
		$dateSpecificConditions = $customView->getStdFilterConditions();
		foreach ($advFilterList as $groupindex => $groupcolumns) {
			$filtercolumns = $groupcolumns['columns'];
			if (count($filtercolumns) > 0) {
				$this->startGroup('');
				foreach ($filtercolumns as $index => $filter) {
					$nameComponents = explode(':', $filter['columnname']);
					// For Events "End Date & Time" field datatype should be DT. But, db will give D for due_date field
					if ($nameComponents[2] == 'due_date' && $nameComponents[3] == 'Events_End_Date_&_Time')
						$nameComponents[4] = 'DT';
					if (empty($nameComponents[2]) && $nameComponents[1] == 'crmid' && $nameComponents[0] == 'vtiger_crmentity') {
						$name = $this->getSQLColumn('id');
					} else {
						$name = $nameComponents[2];
					}
					if (($nameComponents[4] == 'D' || $nameComponents[4] == 'DT') && in_array($filter['comparator'], $dateSpecificConditions)) {
						$filter['stdfilter'] = $filter['comparator'];
						$valueComponents = explode(',', $filter['value']);
						if ($filter['comparator'] == 'custom') {
							if ($nameComponents[4] == 'DT') {
								$startDateTimeComponents = explode(' ', $valueComponents[0]);
								$endDateTimeComponents = explode(' ', $valueComponents[1]);
								$filter['startdate'] = DateTimeField::convertToDBFormat($startDateTimeComponents[0]);
								$filter['enddate'] = DateTimeField::convertToDBFormat($endDateTimeComponents[0]);
							} else {
								$filter['startdate'] = DateTimeField::convertToDBFormat($valueComponents[0]);
								$filter['enddate'] = DateTimeField::convertToDBFormat($valueComponents[1]);
							}
						}
						$dateFilterResolvedList = $customView->resolveDateFilterValue($filter);
						// If datatype is DT then we should append time also
						if ($nameComponents[4] == 'DT') {
							$startdate = explode(' ', $dateFilterResolvedList['startdate']);
							if ($startdate[1] == '')
								$startdate[1] = '00:00:00';
							$dateFilterResolvedList['startdate'] = $startdate[0] . ' ' . $startdate[1];

							$enddate = explode(' ', $dateFilterResolvedList['enddate']);
							if ($enddate[1] == '')
								$enddate[1] = '23:59:59';
							$dateFilterResolvedList['enddate'] = $enddate[0] . ' ' . $enddate[1];
						}
						$value = [];
						$value[] = $this->fixDateTimeValue($name, $dateFilterResolvedList['startdate']);
						$value[] = $this->fixDateTimeValue($name, $dateFilterResolvedList['enddate'], false);
						$this->addCondition($name, $value, 'BETWEEN');
					} elseif ($nameComponents[4] == 'DT' && ($filter['comparator'] == 'e' || $filter['comparator'] == 'n')) {
						$filter['stdfilter'] = $filter['comparator'];
						$dateTimeComponents = explode(' ', $filter['value']);
						$filter['startdate'] = DateTimeField::convertToDBFormat($dateTimeComponents[0]);
						$filter['enddate'] = DateTimeField::convertToDBFormat($dateTimeComponents[0]);

						$startDate = $this->fixDateTimeValue($name, $filter['startdate']);
						$endDate = $this->fixDateTimeValue($name, $filter['enddate'], false);

						$value = [];
						$start = explode(' ', $startDate);
						if ($start[1] == "")
							$startDate = $start[0] . ' ' . '00:00:00';

						$end = explode(' ', $endDate);
						if ($end[1] == "")
							$endDate = $end[0] . ' ' . '23:59:59';

						$value[] = $startDate;
						$value[] = $endDate;
						if ($filter['comparator'] == 'n') {
							$this->addCondition($name, $value, 'NOTEQUAL');
						} else {
							$this->addCondition($name, $value, 'BETWEEN');
						}
					} elseif ($nameComponents[4] == 'DT' && ($filter['comparator'] == 'a' || $filter['comparator'] == 'b')) {
						$dateTime = explode(' ', $filter['value']);
						$date = DateTimeField::convertToDBFormat($dateTime[0]);
						$value = [];
						$value[] = $this->fixDateTimeValue($name, $date, false);
						// Still fixDateTimeValue returns only date value, we need to append time because it is DT type
						for ($i = 0; $i < count($value); $i++) {
							$values = explode(' ', $value[$i]);
							if ($values[1] == '') {
								$values[1] = '00:00:00';
							}
							$value[$i] = $values[0] . ' ' . $values[1];
						}
						$this->addCondition($name, $value, $filter['comparator']);
					} else {
						$this->addCondition($name, $filter['value'], $filter['comparator']);
					}
					$columncondition = $filter['column_condition'];
					if (!empty($columncondition)) {
						$this->addConditionGlue($columncondition);
					}
				}
				$this->endGroup();
				$groupConditionGlue = $groupcolumns['condition'];
				if (!empty($groupConditionGlue))
					$this->addConditionGlue($groupConditionGlue);
			}
		}
	}

	public function getCustomViewQueryById($viewId)
	{
		$this->initForCustomViewById($viewId);
		return $this->getQuery();
	}

	public function getQuery($statement = 'SELECT')
	{
		if (empty($this->query)) {
			$conditionedReferenceFields = [];
			$allFields = array_merge($this->whereFields, $this->fields);
			foreach ($allFields as $fieldName) {
				if (in_array($fieldName, $this->referenceFieldList)) {
					$moduleList = $this->referenceFieldInfoList[$fieldName];
					foreach ($moduleList as $module) {
						if (empty($this->moduleNameFields[$module])) {
							$this->getMeta($module);
						}
					}
				} elseif (in_array($fieldName, $this->ownerFields)) {
					$this->getMeta('Users');
					$this->getMeta('Groups');
				}
			}

			$query = $statement . ' ';
			$query .= $this->getSelectClauseColumnSQL();
			$query .= $this->getFromClause();
			$query .= $this->getWhereClause();
			$this->query = $query;
			return $query;
		} else {
			return $this->query;
		}
	}

	public function getSQLColumn($name)
	{
		if ($name == 'id') {
			$baseTable = $this->meta->getEntityBaseTable();
			$moduleTableIndexList = $this->meta->getEntityTableIndexList();
			$baseTableIndex = $moduleTableIndexList[$baseTable];
			return $baseTable . '.' . $baseTableIndex;
		}

		$moduleFields = $this->getModuleFields();
		$field = $moduleFields[$name];
		$sql = '';
		$column = $field->getColumnName();
		return $field->getTableName() . '.' . $column;
	}

	public function getSelectClauseColumnSQL()
	{
		$columns = [];
		$moduleFields = $this->getModuleFields();
		$accessibleFieldList = array_keys($moduleFields);
		$accessibleFieldList[] = 'id';
		$this->fields = array_intersect($this->fields, $accessibleFieldList);
		foreach ($this->fields as $field) {
			$sql = $this->getSQLColumn($field);
			$columns[] = $sql;

			//To merge date and time fields
			if ($this->meta->getEntityName() == 'Calendar' && ($field == 'date_start' || $field == 'due_date')) {
				if ($field == 'date_start') {
					$timeField = 'time_start';
					$sql = $this->getSQLColumn($timeField);
				} elseif ($field == 'due_date') {
					$timeField = 'time_end';
					$sql = $this->getSQLColumn($timeField);
				}
				$columns[] = $sql;
			}
		}
		foreach ($this->columnsCustom as $columnsCustom) {
			$columns[] = $columnsCustom;
		}
		$this->columns = implode(', ', $columns);
		return $this->columns;
	}

	public function getFromClause($onlyTableJoin = false)
	{
		$current_user = vglobal('current_user');
		if (!empty($this->query) || !empty($this->fromClause)) {
			return $this->fromClause;
		}
		$baseModule = $this->getModule();
		$moduleFields = $this->getModuleFields();
		$tableList = [];
		$tableJoinMapping = [];
		$tableJoinCondition = [];
		$tableJoinSql = '';
		$i = 1;

		$moduleTableIndexList = $this->meta->getEntityTableIndexList();
		foreach ($this->fields as $fieldName) {
			if ($fieldName == 'id') {
				continue;
			}

			$field = $moduleFields[$fieldName];
			$baseTable = $field->getTableName();
			$tableIndexList = $this->meta->getEntityTableIndexList();
			$baseTableIndex = $tableIndexList[$baseTable];
			if ($field->getFieldDataType() == 'reference') {
				$moduleList = $this->referenceFieldInfoList[$fieldName];
				$tableJoinMapping[$field->getTableName()] = 'INNER JOIN';
				foreach ($moduleList as $module) {
					if ($module == 'Users' && $baseModule != 'Users') {
						$tableJoinCondition[$fieldName]['vtiger_users' . $fieldName] = $field->getTableName() .
							"." . $field->getColumnName() . " = vtiger_users" . $fieldName . ".id";
						$tableJoinCondition[$fieldName]['vtiger_groups' . $fieldName] = $field->getTableName() .
							"." . $field->getColumnName() . " = vtiger_groups" . $fieldName . ".groupid";
						$tableJoinMapping['vtiger_users' . $fieldName] = 'LEFT JOIN vtiger_users AS';
						$tableJoinMapping['vtiger_groups' . $fieldName] = 'LEFT JOIN vtiger_groups AS';
						$i++;
					}
				}
			} elseif ($field->getFieldDataType() == 'owner') {
				/*
				 * Removed unnecessary join tables
				  $tableList['vtiger_users'] = 'vtiger_users';
				  $tableList['vtiger_groups'] = 'vtiger_groups';
				  $tableJoinMapping['vtiger_users'] = 'LEFT JOIN';
				  $tableJoinMapping['vtiger_groups'] = 'LEFT JOIN';
				 */
				if ($fieldName == 'created_user_id') {
					$tableJoinCondition[$fieldName]['vtiger_users' . $fieldName] = $field->getTableName() .
						'.' . $field->getColumnName() . ' = vtiger_users' . $fieldName . '.id';
					$tableJoinCondition[$fieldName]['vtiger_groups' . $fieldName] = $field->getTableName() .
						'.' . $field->getColumnName() . ' = vtiger_groups' . $fieldName . '.groupid';
					$tableJoinMapping['vtiger_users' . $fieldName] = 'LEFT JOIN vtiger_users AS';
					$tableJoinMapping['vtiger_groups' . $fieldName] = 'LEFT JOIN vtiger_groups AS';
				}
			}

			$tableList[$field->getTableName()] = $field->getTableName();
			$tableJoinMapping[$field->getTableName()] = $this->meta->getJoinClause($field->getTableName());
		}
		$baseTable = $this->meta->getEntityBaseTable();
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		foreach ($this->whereFields as $fieldName) {
			if (empty($fieldName)) {
				continue;
			}
			$field = $moduleFields[$fieldName];
			if (empty($field)) {
				// not accessible field.
				continue;
			}
			$baseTable = $field->getTableName();
			// When a field is included in Where Clause, but not is Select Clause, and the field table is not base table,
			// The table will not be present in tablesList and hence needs to be added to the list.
			if (empty($tableList[$baseTable])) {
				$tableList[$baseTable] = $field->getTableName();
				$tableJoinMapping[$baseTable] = $this->meta->getJoinClause($field->getTableName());
			}
			if (in_array($field->getFieldDataType(), Vtiger_Field_Model::$REFERENCE_TYPES)) {
				if (!(AppConfig::performance('SEARCH_REFERENCE_BY_AJAX') && isset($this->whereOperator[$fieldName]) && $this->whereOperator[$fieldName] == 'e')) {
					$moduleList = $this->referenceFieldInfoList[$fieldName];
					// This is special condition as the data is not stored in the base table, 
					$tableJoinMapping[$field->getTableName()] = 'INNER JOIN';
					foreach ($moduleList as $module) {
						$meta = $this->getMeta($module);
						$nameFields = $this->moduleNameFields[$module];
						$nameFieldList = explode(',', $nameFields);
						foreach ($nameFieldList as $index => $column) {
							$referenceField = $meta->getFieldByColumnName($column);
							$referenceTable = $referenceField->getTableName();
							$tableIndexList = $meta->getEntityTableIndexList();
							$referenceTableIndex = $tableIndexList[$referenceTable];

							$referenceTableName = "$referenceTable $referenceTable$fieldName";
							$referenceTable = "$referenceTable$fieldName";
							//should always be left join for cases where we are checking for null
							//reference field values.
							if (!array_key_exists($referenceTable, $tableJoinMapping)) {  // table already added in from clause
								$tableJoinMapping[$referenceTableName] = 'LEFT JOIN';
								$tableJoinCondition[$fieldName][$referenceTableName] = $baseTable . '.' .
									$field->getColumnName() . ' = ' . $referenceTable . '.' . $referenceTableIndex;
							}
						}
					}
				}
			} elseif ($field->getFieldDataType() == 'owner') {
				$add = true;
				if (isset($this->whereOperator[$fieldName]) && ($this->whereOperator[$fieldName] == 'om' || $this->whereOperator[$fieldName] == 'e')) {
					$add = false;
				}
				if ($add) {
					$tableList['vtiger_users'] = 'vtiger_users';
					$tableList['vtiger_groups'] = 'vtiger_groups';
					$tableJoinMapping['vtiger_users'] = 'LEFT JOIN';
					$tableJoinMapping['vtiger_groups'] = 'LEFT JOIN';
				}
			} else {
				$tableList[$field->getTableName()] = $field->getTableName();
				$tableJoinMapping[$field->getTableName()] = $this->meta->getJoinClause($field->getTableName());
			}
		}

		$defaultTableList = $this->meta->getEntityDefaultTableList();
		foreach ($defaultTableList as $table) {
			if (!in_array($table, $tableList)) {
				$tableList[$table] = $table;
				$tableJoinMapping[$table] = 'INNER JOIN';
			}
		}
		$ownerFields = $this->meta->getOwnerFields();
		if (count($ownerFields) > 0) {
			$ownerField = $ownerFields[0];
		}
		$baseTable = $this->meta->getEntityBaseTable();
		$sql = " FROM $baseTable ";
		foreach ($this->customTable as $table) {
			$tableName = $table['name'];
			$tableList[$tableName] = $tableName;
			$tableJoinMapping[$tableName] = $table['join'];
		}
		foreach ($this->whereClauseCustom as $where) {
			if (isset($where['tablename']) && ($baseTable != $where['tablename'] && !in_array($where['tablename'], $tableList))) {
				$tableList[] = $where['tablename'];
				$tableJoinMapping[$where['tablename']] = 'LEFT JOIN';
			}
		}
		foreach ($defaultTableList as $tableName) {
			$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable.$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]";
			unset($tableList[$tableName]);
		}
		unset($tableList[$baseTable]);
		foreach ($tableList as $tableName) {
			if ($tableName == 'vtiger_users') {
				$field = $moduleFields[$ownerField];
				$sql .= " $tableJoinMapping[$tableName] $tableName ON " . $field->getTableName() . '.' .
					$field->getColumnName() . " = $tableName.id";
			} elseif ($tableName == 'vtiger_groups') {
				$field = $moduleFields[$ownerField];
				$sql .= " $tableJoinMapping[$tableName] $tableName ON " . $field->getTableName() . '.' .
					$field->getColumnName() . " = $tableName.groupid";
			} else {
				$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable." .
					"$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]";
			}
		}

		foreach ($tableJoinCondition as $fieldName => $conditionInfo) {
			foreach ($conditionInfo as $tableName => $condition) {
				if (!empty($tableList[$tableName])) {
					$tableNameAlias = $tableName . '2';
					$condition = str_replace($tableName, $tableNameAlias, $condition);
				} else {
					$tableNameAlias = '';
				}
				$tableJoinSql .= " $tableJoinMapping[$tableName] $tableName $tableNameAlias ON $condition";
			}
		}

		if ($onlyTableJoin) {
			return $tableJoinSql;
		}

		$sql .= $tableJoinSql;
		foreach ($this->manyToManyRelatedModuleConditions as $conditionInfo) {
			$relatedModuleMeta = RelatedModuleMeta::getInstance($this->meta->getTabName(), $conditionInfo['relatedModule']);
			$relationInfo = $relatedModuleMeta->getRelationMeta();
			$relatedModule = $this->meta->getTabName();
			$sql .= ' INNER JOIN ' . $relationInfo['relationTable'] . ' ON ' .
				$relationInfo['relationTable'] . ".$relationInfo[$relatedModule]=" .
				"$baseTable.$baseTableIndex";
		}

		// Adding support for conditions on reference module fields
		if (isset($this->referenceModuleField)) {
			$referenceFieldTableList = [];
			foreach ($this->referenceModuleField as $index => $conditionInfo) {
				$handler = vtws_getModuleHandlerFromName($conditionInfo['relatedModule'], $current_user);
				$meta = $handler->getMeta();
				$tableList = $meta->getEntityTableIndexList();
				$fieldName = $conditionInfo['fieldName'];
				$referenceFieldObject = $moduleFields[$conditionInfo['referenceField']];
				$fields = $meta->getModuleFields();
				$fieldObject = $fields[$fieldName];

				if (empty($fieldObject))
					continue;

				$tableName = $fieldObject->getTableName();
				if (!in_array($tableName, $referenceFieldTableList)) {
					$sql .= " LEFT JOIN " . $tableName . ' AS ' . $tableName . $conditionInfo['referenceField'] . ' ON
							' . $tableName . $conditionInfo['referenceField'] . '.' . $tableList[$tableName] . '=' .
						$referenceFieldObject->getTableName() . '.' . $referenceFieldObject->getColumnName();
					$referenceFieldTableList[] = $tableName;
				}
			}
		}
		foreach ($this->fromClauseCustom as $where) {
			$sql .= ' ' . $where['joinType'] . ' JOIN ' . $where['relatedTable'] . ' ON ' . $where['relatedTable'] . '.' . $where['relatedIndex'] .
				'=' . $where['baseTable'] . '.' . $where['baseIndex'];
		}
		$this->fromClause = $sql;
		return $sql;
	}

	public function getWhereClause($onlyWhereQuery = false)
	{
		$current_user = vglobal('current_user');
		if (!empty($this->query) || !empty($this->whereClause)) {
			return $this->whereClause;
		}
		$deletedQuery = $this->meta->getEntityDeletedQuery();
		$sql = '';
		if (!empty($deletedQuery) && !$onlyWhereQuery) {
			$sql .= " WHERE $deletedQuery";
		}
		if ($this->conditionInstanceCount > 0) {
			$sql .= ' && ';
		} elseif (empty($deletedQuery)) {
			$sql .= ' WHERE ';
		}
		$baseModule = $this->getModule();
		$moduleFieldList = $this->getModuleFields();
		$baseTable = $this->meta->getEntityBaseTable();
		$moduleTableIndexList = $this->meta->getEntityTableIndexList();
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		$groupSql = $this->groupInfo;
		$fieldSqlList = [];
		foreach ($this->conditionals as $index => $conditionInfo) {
			$fieldName = $conditionInfo['name'];
			$field = $moduleFieldList[$fieldName];
			if ($fieldName == 'id') {
				$sqlOperator = $this->getSqlOperator($conditionInfo['operator']);
				$fieldSqlList[$index] = $baseTable . '.' . $baseTableIndex . $sqlOperator . '"' . $conditionInfo['value'] . '"';
				continue;
			}
			if (empty($field) || $conditionInfo['operator'] == 'None') {
				continue;
			}
			$fieldSql = '(';
			$fieldGlue = '';
			$valueSqlList = $this->getConditionValue($conditionInfo['value'], $conditionInfo['operator'], $field, $conditionInfo['custom']);
			$operator = strtolower($conditionInfo['operator']);
			if ($operator == 'between' && $this->isDateType($field->getFieldDataType())) {
				$start = explode(' ', $conditionInfo['value'][0]);
				if (count($start) == 2)
					$conditionInfo['value'][0] = getValidDBInsertDateTimeValue($start[0] . ' ' . $start[1]);

				$end = explode(' ', $conditionInfo['values'][1]);
				// Dates will be equal for Today, Tomorrow, Yesterday.
				if (count($end) == 2) {
					if ($start[0] == $end[0]) {
						$dateTime = new DateTime($conditionInfo['value'][0]);
						$nextDay = $dateTime->modify('+1 days');
						$nextDay = $nextDay->format('Y-m-d H:i:s');
						$values = explode(' ', $nextDay);
						$conditionInfo['value'][1] = getValidDBInsertDateTimeValue($values[0]) . ' ' . $values[1];
					} else {
						$end = $conditionInfo['value'][1];
						$dateObject = new DateTimeField($end);
						$conditionInfo['value'][1] = $dateObject->getDBInsertDateTimeValue();
					}
				}
			}
			if (!is_array($valueSqlList)) {
				$valueSqlList = array($valueSqlList);
			}
			foreach ($valueSqlList as $valueSql) {
				if (in_array($fieldName, $this->referenceFieldList)) {
					if ($conditionInfo['operator'] == 'y') {
						$columnName = $field->getColumnName();
						$tableName = $field->getTableName();
						// We are checking for zero since many reference fields will be set to 0 if it doest not have any value
						$fieldSql .= "$fieldGlue $tableName.$columnName $valueSql || $tableName.$columnName = '0'";
						$fieldGlue = ' OR';
					} elseif (AppConfig::performance('SEARCH_REFERENCE_BY_AJAX') && $conditionInfo['operator'] == 'e') {
						$values = explode(',', $valueSql);
						foreach ($values as $value) {
							$fieldSql .= "$fieldGlue " . $field->getTableName() . '.' . $field->getColumnName() . ' ' . ltrim($value);
							$fieldGlue = ' OR';
						}
					} else {
						$moduleList = $this->referenceFieldInfoList[$fieldName];
						foreach ($moduleList as $module) {
							$meta = $this->getMeta($module);
							$nameFields = $this->moduleNameFields[$module];
							$nameFieldList = explode(',', $nameFields);
							$columnList = [];
							foreach ($nameFieldList as $column) {
								if ($module == 'Users') {
									$instance = CRMEntity::getInstance($module);
									$referenceTable = $instance->table_name;
									if (count($this->ownerFields) > 0) {
										$referenceTable .= $fieldName;
									}
								} else {
									$referenceField = $meta->getFieldByColumnName($column);
									$referenceTable = $referenceField->getTableName() . $fieldName;
								}
								if (isset($moduleTableIndexList[$referenceTable])) {
									$referenceTable = "$referenceTable$fieldName";
								}
								$columnList[$column] = "$referenceTable.$column";
							}
							if (count($columnList) > 1) {
								$columnSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat($columnList, $module);
							} else {
								$columnSql = implode('', $columnList);
							}

							$fieldSql .= "$fieldGlue trim($columnSql) $valueSql";
							$fieldGlue = ' OR';
						}
					}
				} elseif (in_array($fieldName, $this->ownerFields)) {
					if ($conditionInfo['operator'] == 'om' || $conditionInfo['operator'] == 'e') {
						$fieldSql .= "$fieldGlue " . $field->getTableName() . '.' . $field->getColumnName() . " $valueSql";
					} elseif ($conditionInfo['operator'] == 'wr' || $conditionInfo['operator'] == 'nwr') {
						$fieldSql .= $fieldGlue . $valueSql;
					} elseif ($fieldName == 'created_user_id') {
						$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => "vtiger_users$fieldName.first_name", 'last_name' => "vtiger_users$fieldName.last_name"), 'Users');
						$fieldSql .= "$fieldGlue (trim($concatSql) $valueSql)";
					} else {
						$entityFields = \includes\Modules::getEntityInfo('Users');
						if (count($entityFields['fieldnameArr']) > 1) {
							$columns = [];
							foreach ($entityFields['fieldnameArr'] as &$fieldname) {
								$columns[$fieldname] = $entityFields['tablename'] . '.' . $fieldname;
							}
							$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat($columns, 'Users');
							$fieldSql .= "$fieldGlue (trim($concatSql) $valueSql || " . "vtiger_groups.groupname $valueSql)";
						} else {
							$columnSql = $entityFields['tablename'] . '.' . $entityFields['fieldname'];
							$fieldSql .= "$fieldGlue (trim($columnSql) $valueSql || " . "vtiger_groups.groupname $valueSql)";
						}
					}
				} elseif ($field->getUIType() == 120) {
					if (in_array($conditionInfo['operator'], ['y', 'ny'])) {
						$fieldSql .= $fieldGlue . $field->getTableName() . '.' . $field->getColumnName() . " $valueSql";
					} else {
						$fieldSql .= $fieldGlue . ' ' . $valueSql;
					}
				} elseif ($fieldName == 'date_start' && $conditionInfo['operator'] == 'ir') {
					$fieldSql .= "$fieldGlue vtiger_activity.date_start <= $valueSql && vtiger_activity.due_date >= $valueSql";
				} elseif ($field->getFieldDataType() == 'date' && ($baseModule == 'Events' || $baseModule == 'Calendar') && ($fieldName == 'date_start' || $fieldName == 'due_date')) {
					$value = $conditionInfo['value'];
					$operator = $conditionInfo['operator'];
					if ($fieldName == 'date_start') {
						$dateFieldColumnName = 'vtiger_activity.date_start';
						$timeFieldColumnName = 'vtiger_activity.time_start';
					} else {
						$dateFieldColumnName = 'vtiger_activity.due_date';
						$timeFieldColumnName = 'vtiger_activity.time_end';
					}
					if ($operator == 'bw') {
						$values = explode(',', $value);
						$startDateValue = explode(' ', $values[0]);
						$endDateValue = explode(' ', $values[1]);
						if (count($startDateValue) == 2 && count($endDateValue) == 2) {
							$fieldSql .= " CAST(CONCAT($dateFieldColumnName,' ',$timeFieldColumnName) AS DATETIME) $valueSql";
						} else {
							$fieldSql .= "$dateFieldColumnName $valueSql";
						}
					} else {
						if (is_array($value)) {
							$value = $value[0];
						}
						$values = explode(' ', $value);
						if (count($values) == 2) {
							$fieldSql .= "$fieldGlue CAST(CONCAT($dateFieldColumnName,' ',$timeFieldColumnName) AS DATETIME) $valueSql ";
						} else {
							$fieldSql .= "$fieldGlue $dateFieldColumnName $valueSql";
						}
					}
				} elseif ($field->getFieldDataType() == 'datetime') {
					$value = $conditionInfo['value'];
					$operator = strtolower($conditionInfo['operator']);
					if ($operator == 'bw') {
						$values = explode(',', $value);
						$startDateValue = explode(' ', $values[0]);
						$endDateValue = explode(' ', $values[1]);
						if ($startDateValue[1] == '00:00:00' && ($endDateValue[1] == '00:00:00' || $endDateValue[1] == '23:59:59')) {
							$fieldSql .= "$fieldGlue CAST(" . $field->getTableName() . '.' . $field->getColumnName() . " AS DATE) $valueSql";
						} else {
							$fieldSql .= "$fieldGlue " . $field->getTableName() . '.' . $field->getColumnName() . ' ' . $valueSql;
						}
					} elseif ($operator == 'between' || $operator == 'notequal' || $operator == 'a' || $operator == 'b') {
						$fieldSql .= "$fieldGlue " . $field->getTableName() . '.' . $field->getColumnName() . ' ' . $valueSql;
					} else {
						$values = explode(' ', $value);
						if ($values[1] == '00:00:00') {
							$fieldSql .= "$fieldGlue CAST(" . $field->getTableName() . '.' . $field->getColumnName() . " AS DATE) $valueSql";
						} else {
							$fieldSql .= "$fieldGlue " . $field->getTableName() . '.' . $field->getColumnName() . ' ' . $valueSql;
						}
					}
				} elseif (($baseModule == 'Events' || $baseModule == 'Calendar') && ($field->getColumnName() == 'status')) {
					$otherFieldName = 'activitystatus';
					$otherField = $moduleFieldList[$otherFieldName];

					$specialCondition = '';
					$specialConditionForOtherField = '';
					$conditionGlue = ' || ';
					if ($conditionInfo['operator'] == 'n' || $conditionInfo['operator'] == 'k' || $conditionInfo['operator'] == 'y') {
						$conditionGlue = ' && ';
						if ($conditionInfo['operator'] == 'n') {
							$specialCondition = ' || ' . $field->getTableName() . '.' . $field->getColumnName() . ' IS NULL ';
							if (!empty($otherField))
								$specialConditionForOtherField = ' || ' . $otherField->getTableName() . '.' . $otherField->getColumnName() . ' IS NULL ';
						}
					}

					$otherFieldValueSql = $valueSql;
					if ($conditionInfo['operator'] == 'ny' && !empty($otherField)) {
						$otherFieldValueSql = "IS NOT NULL && " . $otherField->getTableName() . '.' . $otherField->getColumnName() . " != ''";
					}

					$fieldSql .= "$fieldGlue ((" . $field->getTableName() . '.' . $field->getColumnName() . ' ' . $valueSql . " $specialCondition) ";
					if (!empty($otherField))
						$fieldSql .= $conditionGlue . '(' . $otherField->getTableName() . '.' . $otherField->getColumnName() . ' ' . $otherFieldValueSql . ' ' . $specialConditionForOtherField . '))';
					else
						$fieldSql .= ')';
				} elseif ($conditionInfo['custom']) {
					$fieldSql .= $fieldGlue . 'vtiger_crmentity.crmid ' . $valueSql;
				} else {
					if ($fieldName == 'birthday' && !$this->isRelativeSearchOperators($conditionInfo['operator'])) {
						$fieldSql .= "$fieldGlue DATE_FORMAT(" . $field->getTableName() . '.' .
							$field->getColumnName() . ",'%m%d') " . $valueSql;
					} else {
						$fieldSql .= "$fieldGlue " . $field->getTableName() . '.' .
							$field->getColumnName() . ' ' . $valueSql;
					}
				}
				if (($conditionInfo['operator'] == 'n' || $conditionInfo['operator'] == 'k') && ($field->getFieldDataType() == 'owner' || $field->getFieldDataType() == 'picklist' || $field->getFieldDataType() == 'sharedOwner')) {
					$fieldGlue = ' AND';
				} else {
					$fieldGlue = ' OR';
				}
			}
			$fieldSql .= ')';
			$fieldSqlList[$index] = $fieldSql;
		}
		foreach ($this->manyToManyRelatedModuleConditions as $index => $conditionInfo) {
			$relatedModuleMeta = RelatedModuleMeta::getInstance($this->meta->getTabName(), $conditionInfo['relatedModule']);
			$relationInfo = $relatedModuleMeta->getRelationMeta();
			$relatedModule = $this->meta->getTabName();
			$fieldSql = "(" . $relationInfo['relationTable'] . '.' .
				$relationInfo[$conditionInfo['column']] . $conditionInfo['SQLOperator'] .
				$conditionInfo['value'] . ")";
			$fieldSqlList[$index] = $fieldSql;
		}

		// This is added to support reference module fields
		if (isset($this->referenceModuleField)) {
			foreach ($this->referenceModuleField as $index => $conditionInfo) {
				$handler = vtws_getModuleHandlerFromName($conditionInfo['relatedModule'], $current_user);
				$meta = $handler->getMeta();
				$fieldName = $conditionInfo['fieldName'];
				$fields = $meta->getModuleFields();
				$fieldObject = $fields[$fieldName];
				$columnName = $fieldObject->getColumnName();
				$tableName = $fieldObject->getTableName();
				$valueSQL = $this->getConditionValue($conditionInfo['value'], $conditionInfo['SQLOperator'], $fieldObject);
				$fieldSql = "(" . $tableName . $conditionInfo['referenceField'] . '.' . $columnName . ' ' . $valueSQL[0] . ")";
				$fieldSqlList[$index] = $fieldSql;
			}
		}

		// This is needed as there can be condition in different order and there is an assumption in makeGroupSqlReplacements API
		// that it expects the array in an order and then replaces the sql with its the corresponding place
		ksort($fieldSqlList);
		$groupSql = $this->makeGroupSqlReplacements($fieldSqlList, $groupSql);
		if ($this->conditionInstanceCount > 0) {
			$this->conditionalWhere = $groupSql;
			$sql .= $groupSql;
		}

		foreach ($this->whereClauseCustom as $where) {
			$value = $where['value'];
			$operator = $where['operator'];
			$valueAndOp = $this->getSqlOperator($operator, $value);
			if (is_array($valueAndOp)) {
				$value = $valueAndOp[1];
				$operator = $valueAndOp[0] ? $valueAndOp[0] : $operator;
			} else {
				$operator = $valueAndOp ? $valueAndOp : $operator;
			}
			$sql .= ' ' . $where['glue'] . ' ' . $where['column'] . ' ' . $operator . ' ' . $value;
		}

		if (!$onlyWhereQuery && $this->permissions) {
			$instance = CRMEntity::getInstance($baseModule);
			$sql .= $instance->getUserAccessConditionsQuerySR($baseModule, $current_user, $this->getSourceRecord());
		}
		$this->whereClause = $sql;
		return $sql;
	}

	/**
	 *
	 * @param mixed $value
	 * @param String $operator
	 * @param WebserviceField $field
	 */
	private function getConditionValue($value, $operator, $field, $custom = false)
	{

		$operator = strtolower($operator);
		$db = PearDatabase::getInstance();
		$inEqualityFieldTypes = ['currency', 'percentage', 'double', 'integer', 'number'];

		if (is_string($value) && $this->ignoreComma == false) {
			$commaSeparatedFieldTypes = ['picklist', 'multipicklist', 'owner', 'date', 'datetime', 'time', 'tree', 'sharedOwner', 'sharedOwner'];
			if (in_array($field->getFieldDataType(), $commaSeparatedFieldTypes)) {
				$valueArray = explode(',', $value);
				if ($field->getFieldDataType() == 'multipicklist' && in_array($operator, ['e', 'n'])) {
					$valueArray = getCombinations($valueArray);
					foreach ($valueArray as $key => $value) {
						$valueArray[$key] = ltrim($value, ' |##| ');
					}
				}
			} elseif ($field->getFieldDataType() == 'multiReferenceValue' && !$custom) {
				$valueArray = explode(',', $value);
				foreach ($valueArray as $key => $value) {
					$valueArray[$key] = '|#|' . $value . '|#|';
				}
			} else {
				$valueArray = [$value];
			}
		} elseif (is_array($value)) {
			$valueArray = $value;
		} else {
			$valueArray = [$value];
		}
		if ($operator == 'e' && in_array($field->getFieldDataType(), Vtiger_Field_Model::$REFERENCE_TYPES) && AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			$valueArray = explode(',', $value);
		}
		$sql = [];
		if ($operator == 'between' || $operator == 'bw' || $operator == 'notequal') {
			if ($field->getFieldName() == 'birthday') {
				$valueArray[0] = getValidDBInsertDateTimeValue($valueArray[0]);
				$valueArray[1] = getValidDBInsertDateTimeValue($valueArray[1]);
				$sql[] = "BETWEEN DATE_FORMAT(" . $db->quote($valueArray[0]) . ", '%m%d') AND " .
					"DATE_FORMAT(" . $db->quote($valueArray[1]) . ", '%m%d')";
			} else {
				if ($this->isDateType($field->getFieldDataType())) {
					$start = explode(' ', $valueArray[0]);
					$end = explode(' ', $valueArray[1]);
					if ($operator == 'between' && count($start) == 2 && count($end) == 2) {
						$valueArray[0] = getValidDBInsertDateTimeValue($start[0] . ' ' . $start[1]);

						if ($start[0] == $end[0]) {
							$dateTime = new DateTime($valueArray[0]);
							$nextDay = $dateTime->modify('+1 days');
							$nextDay = strtotime($nextDay->format('Y-m-d H:i:s')) - 1;
							$nextDay = date('Y-m-d H:i:s', $nextDay);
							$values = explode(' ', $nextDay);
							$valueArray[1] = getValidDBInsertDateTimeValue($values[0]) . ' ' . $values[1];
						} else {
							$end = $valueArray[1];
							$dateObject = new DateTimeField($end);
							$valueArray[1] = $dateObject->getDBInsertDateTimeValue();
						}
					} else {
						$valueArray[0] = getValidDBInsertDateTimeValue($valueArray[0]);
						$dateTimeStart = explode(' ', $valueArray[0]);
						if ($dateTimeStart[1] == '00:00:00' && $operator != 'between') {
							$valueArray[0] = $dateTimeStart[0];
						}
						$valueArray[1] = getValidDBInsertDateTimeValue($valueArray[1]);
						$dateTimeEnd = explode(' ', $valueArray[1]);
						if ($dateTimeEnd[1] == '00:00:00' || $dateTimeEnd[1] == '23:59:59') {
							$valueArray[1] = $dateTimeEnd[0];
						}
					}
				}

				if ($operator == 'notequal') {
					$sql[] = "NOT BETWEEN " . $db->quote($valueArray[0]) . " AND " .
						$db->quote($valueArray[1]);
				} else {
					$sql[] = "BETWEEN " . $db->quote($valueArray[0]) . " AND " .
						$db->quote($valueArray[1]);
				}
			}
			return $sql;
		} elseif ($custom && $operator == 'subquery') {
			$sql[] = 'IN (' . $value . ')';
			return $sql;
		}
		foreach ($valueArray as $value) {
			if (!$this->isStringType($field->getFieldDataType())) {
				$value = trim($value);
			}
			if ($operator == 'empty' || $operator == 'y') {
				$sql[] = sprintf("IS NULL || %s = ''", $this->getSQLColumn($field->getFieldName()));
				continue;
			}
			if ($operator == 'ny') {
				$sql[] = sprintf("IS NOT NULL && %s != ''", $this->getSQLColumn($field->getFieldName()));
				continue;
			}
			if ((strtolower(trim($value)) == 'null') ||
				(trim($value) == '' && !$this->isStringType($field->getFieldDataType())) &&
				($operator == 'e' || $operator == 'n')) {
				if ($operator == 'e') {
					$sql[] = "IS NULL";
					continue;
				}
				$sql[] = "IS NOT NULL";
				continue;
			} elseif ($field->getFieldDataType() == 'boolean') {
				$value = strtolower($value);
				if ($value == 'yes') {
					$value = 1;
				} elseif ($value == 'no') {
					$value = 0;
				}
			} elseif ($this->isDateType($field->getFieldDataType())) {
				// For "after" and "before" conditions
				$values = explode(' ', $value);
				if (($operator == 'a' || $operator == 'b') && count($values) == 2) {
					if ($operator == 'a') {
						// for after comparator we should check the date after the given
						$dateTime = new DateTime($value);
						$modifiedDate = $dateTime->modify('+1 days');
						$nextday = $modifiedDate->format('Y-m-d H:i:s');
						$temp = strtotime($nextday) - 1;
						$date = date('Y-m-d H:i:s', $temp);
						$value = getValidDBInsertDateTimeValue($date);
					} else {
						$dateTime = new DateTime($value);
						$prevday = $dateTime->format('Y-m-d H:i:s');
						$temp = strtotime($prevday) - 1;
						$date = date('Y-m-d H:i:s', $temp);
						$value = getValidDBInsertDateTimeValue($date);
					}
				} else {
					$value = getValidDBInsertDateTimeValue($value);
					$dateTime = explode(' ', $value);
					if ($dateTime[1] == '00:00:00') {
						$value = $dateTime[0];
					}
				}
			} elseif (in_array($field->getFieldDataType(), $inEqualityFieldTypes)) {
				$table = get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, vglobal('default_charset'));
				$chars = implode('', array_keys($table));
				if (preg_match("/[{$chars}]+/", $value) === 1) {
					if ($operator == 'g' || $operator == 'l') {
						$value = substr($value, 4);
					} elseif ($operator == 'h' || $operator == 'm') {
						$value = substr($value, 5);
					}
				}
			} elseif ($field->getFieldDataType() === 'currency') {
				$uiType = $field->getUIType();
				if ($uiType == 72) {
					$value = CurrencyField::convertToDBFormat($value, null, true);
				} elseif ($uiType == 71) {
					$value = CurrencyField::convertToDBFormat($value);
				}
			}

			if ($field->getFieldName() == 'birthday' && !$this->isRelativeSearchOperators(
					$operator)) {
				$value = "DATE_FORMAT(" . $db->quote($value) . ", '%m%d')";
			} else {
				$value = $db->sql_escape_string($value, true);
			}

			if ($field->getFieldDataType() == 'multiReferenceValue' && in_array($operator, ['e', 's', 'ew', 'c'])) {
				$sql[] = "LIKE '%$value%'";
				continue;
			} elseif ($field->getFieldDataType() == 'multiReferenceValue' && in_array($operator, ['n', 'k'])) {
				$sql[] = "NOT LIKE '%$value%'";
				continue;
			}

			if (trim($value) == '' && ($operator == 's' || $operator == 'ew' || $operator == 'c') && ($this->isStringType($field->getFieldDataType()) ||
				$field->getFieldDataType() == 'picklist' ||
				$field->getFieldDataType() == 'multipicklist')) {
				$sql[] = "LIKE ''";
				continue;
			}
			if (trim($value) == '' && ($operator == 'om') && in_array($field->getFieldName(), $this->ownerFields)) {
				$sql[] = " = '" . Users_Record_Model::getCurrentUserModel()->get('id') . "'";
				continue;
			}
			if (trim($value) == '' && in_array($operator, ['wr', 'nwr']) && in_array($field->getFieldName(), $this->ownerFields)) {
				$userId = Users_Record_Model::getCurrentUserModel()->get('id');
				$watchingSql = '((SELECT COUNT(*) FROM u_yf_watchdog_module WHERE userid = ' . $userId . ' && module = ' . vtlib\Functions::getModuleId($this->module) . ') > 0 && ';
				$watchingSql .= '(SELECT COUNT(*) FROM u_yf_watchdog_record WHERE userid = ' . $userId . ' && record = vtiger_crmentity.crmid && state = 0) = 0) || ';
				$watchingSql .= '((SELECT COUNT(*) FROM u_yf_watchdog_module WHERE userid = ' . $userId . ' && module = ' . vtlib\Functions::getModuleId($this->module) . ') = 0 && ';
				$watchingSql .= '(SELECT COUNT(*) FROM u_yf_watchdog_record WHERE userid = ' . $userId . ' && record = vtiger_crmentity.crmid && state = 1) > 0)';
				$sql[] = $watchingSql;
				continue;
			}
			if ($field->getUIType() == 120) {
				if ($operator == 'om') {
					$sql[] = 'vtiger_crmentity.crmid IN (SELECT DISTINCT crmid FROM u_yf_crmentity_showners WHERE userid = ' . Users_Record_Model::getCurrentUserModel()->get('id') . ')';
				} elseif (in_array($operator, ['e', 's', 'ew', 'c'])) {
					$sql[] = 'vtiger_crmentity.crmid IN (SELECT DISTINCT crmid FROM u_yf_crmentity_showners WHERE userid = ' . $value . ')';
				} elseif (in_array($operator, ['n', 'k'])) {
					$sql[] = 'vtiger_crmentity.crmid NOT IN (SELECT DISTINCT crmid FROM u_yf_crmentity_showners WHERE userid = ' . $value . ')';
				}
				continue;
			}
			if ($field->getUIType() == 307) {
				if ($value == '-') {
					$sql[] = 'IS NULL';
					continue;
				} elseif (!in_array(substr($value, 0, 1), ['>', '<']) && !in_array(substr($value, 0, 2), ['>=', '<=']) && !is_numeric($value)) {
					$value = "'$value'";
				}
			}
			if (trim($value) == '' && ($operator == 'k') &&
				$this->isStringType($field->getFieldDataType())) {
				$sql[] = "NOT LIKE ''";
				continue;
			}
			$sqlOperatorData = $this->getSqlOperator($operator, $value);
			$sqlOperator = $sqlOperatorData[0];
			$value = $sqlOperatorData[1];

			if (!$this->isNumericType($field->getFieldDataType()) &&
				($field->getFieldName() != 'birthday' || ($field->getFieldName() == 'birthday' && $this->isRelativeSearchOperators($operator)))) {
				$value = "'$value'";
			}
			if ($this->isNumericType($field->getFieldDataType()) && empty($value)) {
				$value = '0';
			}
			$sql[] = "$sqlOperator $value";
		}
		return $sql;
	}

	private function makeGroupSqlReplacements($fieldSqlList, $groupSql)
	{
		$pos = 0;
		$nextOffset = 0;
		foreach ($fieldSqlList as $index => $fieldSql) {
			$pos = strpos($groupSql, $index . '', $nextOffset);
			if ($pos !== false) {
				$beforeStr = substr($groupSql, 0, $pos);
				$afterStr = substr($groupSql, $pos + strlen($index));
				$nextOffset = strlen($beforeStr . $fieldSql);
				$groupSql = $beforeStr . $fieldSql . $afterStr;
			}
		}
		return $groupSql;
	}

	private function isRelativeSearchOperators($operator)
	{
		$nonDaySearchOperators = array('l', 'g', 'm', 'h');
		return in_array($operator, $nonDaySearchOperators);
	}

	private function isNumericType($type)
	{
		return ($type == 'integer' || $type == 'double' || $type == 'currency');
	}

	private function isStringType($type)
	{
		return ($type == 'string' || $type == 'text' || $type == 'email' || $type == 'reference');
	}

	private function isDateType($type)
	{
		return ($type == 'date' || $type == 'datetime');
	}

	public function fixDateTimeValue($name, $value, $first = true)
	{
		$moduleFields = $this->getModuleFields();
		$field = $moduleFields[$name];
		$type = $field ? $field->getFieldDataType() : false;
		if ($type == 'datetime') {
			if (strrpos($value, ' ') === false) {
				if ($first) {
					return $value . ' 00:00:00';
				} else {
					return $value . ' 23:59:59';
				}
			}
		}
		return $value;
	}

	public function addCondition($fieldname, $value, $operator, $glue = null, $custom = false, $newGroupType = null, $ignoreComma = false)
	{
		$conditionNumber = $this->conditionInstanceCount++;
		if ($glue != null && $conditionNumber > 0)
			$this->addConditionGlue($glue);

		$this->groupInfo .= "$conditionNumber ";
		$this->whereFields[] = $fieldname;
		$this->ignoreComma = $ignoreComma;
		$this->reset();
		$this->whereOperator[$fieldname] = $operator;
		$this->conditionals[$conditionNumber] = $this->getConditionalArray($fieldname, $value, $operator, $custom);
	}

	public function addRelatedModuleCondition($relatedModule, $column, $value, $SQLOperator)
	{
		$conditionNumber = $this->conditionInstanceCount++;
		$this->groupInfo .= "$conditionNumber ";
		$this->manyToManyRelatedModuleConditions[$conditionNumber] = array('relatedModule' =>
			$relatedModule, 'column' => $column, 'value' => $value, 'SQLOperator' => $SQLOperator);
	}

	public function addReferenceModuleFieldCondition($relatedModule, $referenceField, $fieldName, $value, $SQLOperator, $glue = null)
	{
		$conditionNumber = $this->conditionInstanceCount++;
		if ($glue != null && $conditionNumber > 0)
			$this->addConditionGlue($glue);

		$this->groupInfo .= "$conditionNumber ";
		$this->referenceModuleField[$conditionNumber] = array('relatedModule' => $relatedModule, 'referenceField' => $referenceField, 'fieldName' => $fieldName, 'value' => $value,
			'SQLOperator' => $SQLOperator);
	}

	private function getConditionalArray($fieldname, $value, $operator, $custom = false)
	{
		if (is_string($value)) {
			$value = trim($value);
		} elseif (is_array($value)) {
			$value = array_map(trim, $value);
		}
		return array('name' => $fieldname, 'value' => $value, 'operator' => $operator, 'custom' => $custom);
	}

	public function startGroup($groupType)
	{
		$this->groupInfo .= " $groupType (";
	}

	public function endGroup()
	{
		$this->groupInfo .= ')';
	}

	public function addConditionGlue($glue)
	{
		$this->groupInfo .= " $glue ";
	}

	public function addUserSearchConditions($input)
	{
		$log = LoggerManager::getInstance();
		$default_charset = AppConfig::main('default_charset');
		if ($input['searchtype'] == 'advance') {
			$advftCriteria = AppRequest::get('advft_criteria');
			$advftCriteriaGroups = AppRequest::get('advft_criteria_groups');

			if (empty($advftCriteria) || count($advftCriteria) <= 0) {
				return;
			}

			$advfilterlist = getAdvancedSearchCriteriaList($advftCriteria, $advftCriteriaGroups, $this->getModule());

			if (empty($advfilterlist) || count($advfilterlist) <= 0) {
				return;
			}

			if ($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			foreach ($advfilterlist as $groupindex => $groupcolumns) {
				$filtercolumns = $groupcolumns['columns'];
				if (count($filtercolumns) > 0) {
					$this->startGroup('');
					foreach ($filtercolumns as $index => $filter) {
						$name = explode(':', $filter['columnname']);
						if (empty($name[2]) && $name[1] == 'crmid' && $name[0] == 'vtiger_crmentity') {
							$name = $this->getSQLColumn('id');
						} else {
							$name = $name[2];
						}
						$this->addCondition($name, $filter['value'], $filter['comparator']);
						$columncondition = $filter['column_condition'];
						if (!empty($columncondition)) {
							$this->addConditionGlue($columncondition);
						}
					}
					$this->endGroup();
					$groupConditionGlue = $groupcolumns['condition'];
					if (!empty($groupConditionGlue))
						$this->addConditionGlue($groupConditionGlue);
				}
			}
			$this->endGroup();
		} elseif ($input['type'] == 'dbrd') {
			if ($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			$allConditionsList = $this->getDashBoardConditionList();
			$conditionList = $allConditionsList['conditions'];
			$relatedConditionList = $allConditionsList['relatedConditions'];
			$noOfConditions = count($conditionList);
			$noOfRelatedConditions = count($relatedConditionList);
			foreach ($conditionList as $index => $conditionInfo) {
				$this->addCondition($conditionInfo['fieldname'], $conditionInfo['value'], $conditionInfo['operator']);
				if ($index < $noOfConditions - 1 || $noOfRelatedConditions > 0) {
					$this->addConditionGlue(self::$AND);
				}
			}
			foreach ($relatedConditionList as $index => $conditionInfo) {
				$this->addRelatedModuleCondition($conditionInfo['relatedModule'], $conditionInfo['conditionModule'], $conditionInfo['finalValue'], $conditionInfo['SQLOperator']);
				if ($index < $noOfRelatedConditions - 1) {
					$this->addConditionGlue(self::$AND);
				}
			}
			$this->endGroup();
		} else {
			if (isset($input['search_field']) && $input['search_field'] != "") {
				$fieldName = vtlib_purify($input['search_field']);
			} else {
				return;
			}
			if ($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			$moduleFields = $this->getModuleFields();
			$field = $moduleFields[$fieldName];
			$type = $field->getFieldDataType();
			if (isset($input['search_text']) && $input['search_text'] != "") {
				// search other characters like "|, ?, ?" by jagi
				$value = $input['search_text'];
				$stringConvert = function_exists(iconv) ? @iconv("UTF-8", $default_charset, $value) : $value;
				if (!$this->isStringType($type)) {
					$value = trim($stringConvert);
				}

				if ($type == 'picklist') {
					global $mod_strings;
					// Get all the keys for the for the Picklist value
					$mod_keys = array_keys($mod_strings, $value);
					if (sizeof($mod_keys) >= 1) {
						// Iterate on the keys, to get the first key which doesn't start with LBL_      (assuming it is not used in PickList)
						foreach ($mod_keys as $mod_idx => $mod_key) {
							$stridx = strpos($mod_key, 'LBL_');
							// Use strict type comparision, refer strpos for more details
							if ($stridx !== 0) {
								$value = $mod_key;
								break;
							}
						}
					}
				}
				if ($type == 'currency') {
					// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
					if ($field->getUIType() == '72') {
						$value = CurrencyField::convertToDBFormat($value, null, true);
					} else {
						$currencyField = new CurrencyField($value);
						$value = $currencyField->getDBInsertedValue();
					}
				}
			}
			if (!empty($input['operator'])) {
				$operator = $input['operator'];
			} elseif (trim(strtolower($value)) == 'null') {
				$operator = 'e';
			} else {
				if (!$this->isNumericType($type) && !$this->isDateType($type)) {
					$operator = 'c';
				} else {
					$operator = 'h';
				}
			}
			$this->addCondition($fieldName, $value, $operator);
			$this->endGroup();
		}
	}

	public function getDashBoardConditionList()
	{
		if (AppRequest::has('leadsource')) {
			$leadSource = AppRequest::get('leadsource');
		}
		if (AppRequest::has('date_closed')) {
			$dateClosed = AppRequest::get('date_closed');
		}
		if (AppRequest::has('sales_stage')) {
			$salesStage = AppRequest::get('sales_stage');
		}
		if (AppRequest::has('closingdate_start')) {
			$dateClosedStart = AppRequest::get('closingdate_start');
		}
		if (AppRequest::has('closingdate_end')) {
			$dateClosedEnd = AppRequest::get('closingdate_end');
		}
		if (AppRequest::has('owner')) {
			$owner = AppRequest::get('owner');
		}
		if (AppRequest::has('campaignid')) {
			$campaignId = AppRequest::get('campaignid');
		}

		$conditionList = [];
		if (!empty($dateClosedStart) && !empty($dateClosedEnd)) {

			$conditionList[] = array('fieldname' => 'closingdate', 'value' => $dateClosedStart,
				'operator' => 'h');
			$conditionList[] = array('fieldname' => 'closingdate', 'value' => $dateClosedEnd,
				'operator' => 'm');
		}
		if (!empty($salesStage)) {
			if ($salesStage == 'Other') {
				$conditionList[] = array('fieldname' => 'sales_stage', 'value' => 'Closed Won',
					'operator' => 'n');
				$conditionList[] = array('fieldname' => 'sales_stage', 'value' => 'Closed Lost',
					'operator' => 'n');
			} else {
				$conditionList[] = array('fieldname' => 'sales_stage', 'value' => $salesStage,
					'operator' => 'e');
			}
		}
		if (!empty($leadSource)) {
			$conditionList[] = array('fieldname' => 'leadsource', 'value' => $leadSource,
				'operator' => 'e');
		}
		if (!empty($dateClosed)) {
			$conditionList[] = array('fieldname' => 'closingdate', 'value' => $dateClosed,
				'operator' => 'h');
		}
		if (!empty($owner)) {
			$conditionList[] = array('fieldname' => 'assigned_user_id', 'value' => $owner,
				'operator' => 'e');
		}
		$relatedConditionList = [];
		if (!empty($campaignId)) {
			$relatedConditionList[] = array('relatedModule' => 'Campaigns', 'conditionModule' =>
				'Campaigns', 'finalValue' => $campaignId, 'SQLOperator' => '=');
		}
		return array('conditions' => $conditionList, 'relatedConditions' => $relatedConditionList);
	}

	public function initForGlobalSearchByType($type, $value, $operator = 's')
	{
		$fieldList = $this->meta->getFieldNameListByType($type);
		if ($this->conditionInstanceCount <= 0) {
			$this->startGroup('');
		} else {
			$this->startGroup(self::$AND);
		}
		$nameFieldList = explode(',', $this->getModuleNameFields($this->module));
		foreach ($nameFieldList as $nameList) {
			$field = $this->meta->getFieldByColumnName($nameList);
			$this->fields[] = $field->getFieldName();
		}
		foreach ($fieldList as $index => $field) {
			$fieldName = $this->meta->getFieldByColumnName($field);
			$this->fields[] = $fieldName->getFieldName();
			if ($index > 0) {
				$this->addConditionGlue(self::$OR);
			}
			$this->addCondition($fieldName->getFieldName(), $value, $operator);
		}
		$this->endGroup();
		if (!in_array('id', $this->fields)) {
			$this->fields[] = 'id';
		}
	}

	public function getSqlOperator($operator, $value = false)
	{
		switch ($operator) {
			case 'e': $sqlOperator = '=';
				break;
			case 'om': $sqlOperator = '=';
				break;
			case 'n': $sqlOperator = '<>';
				break;
			case 's': $sqlOperator = 'LIKE';
				$value = $value . '%';
				break;
			case 'ew': $sqlOperator = 'LIKE';
				$value = '%' . $value;
				break;
			case 'c': $sqlOperator = 'LIKE';
				$value = '%' . $value . '%';
				break;
			case 'k': $sqlOperator = 'NOT LIKE';
				$value = '%' . $value . '%';
				break;
			case 'in': $sqlOperator = 'IN';
				$value = '(' . $value . ')';
				break;
			case 'nin': $sqlOperator = 'NOT IN';
				$value = '(' . $value . ')';
				break;
			case 'l': $sqlOperator = '<';
				break;
			case 'g': $sqlOperator = '>';
				break;
			case 'm': $sqlOperator = '<=';
				break;
			case 'h': $sqlOperator = '>=';
				break;
			case 'a': $sqlOperator = '>';
				break;
			case 'b': $sqlOperator = '<';
				break;
			case 'subQuery': $sqlOperator = 'IN';
				break;
		}
		if (!$value) {
			return $sqlOperator;
		}
		return [$sqlOperator, $value];
	}
}
