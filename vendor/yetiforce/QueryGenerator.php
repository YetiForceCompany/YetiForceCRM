<?php
namespace App;

/**
 * Query generator class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class QueryGenerator
{

	private $moduleName;

	/**
	 * @var \App\Db\Query 
	 */
	private $query;
	private $fields = [];
	private $referenceFields = [];
	private $ownerFields = [];
	private $customColumns = [];
	private $cvColumns;
	private $stdFilterList;
	private $advFilterList;
	private $customTable = [];
	private $m2mRelModConditions = [];
	private $referenceModuleField = [];
	private $fromClauseCustom = [];
	private $whereOperator = [];
	private $whereFields = [];
	private $whereClauseCustom = [];

	/**
	 * @var \Vtiger_Module_Model 
	 */
	private $moduleModel;
	private $fieldsModel;

	/**
	 * @var \CRMEntity 
	 */
	private $entityModel;

	public function __construct($moduleName, $userId = false)
	{
		$this->moduleName = $moduleName;
		$this->query = new \App\Db\Query();
		$this->moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$this->entityModel = \CRMEntity::getInstance($moduleName);
	}

	/**
	 * Get query instance
	 * @return \App\Db\Query
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * Get module name
	 * @return string
	 */
	public function getModule()
	{
		return $this->moduleName;
	}

	/**
	 * Get CRMEntity Model
	 * @return \CRMEntity
	 */
	public function getEntityModel()
	{
		return $this->entityModel;
	}

	/**
	 * Set custom condition
	 * @param array $where
	 */
	public function setCustomCondition($where)
	{
		$this->whereClauseCustom[] = $where;
	}

	/**
	 * Get fields module
	 * @return array
	 */
	public function getModuleFields()
	{
		if ($this->fieldsModel) {
			return $this->fieldsModel;
		}
		$moduleFields = $this->moduleModel->getFields();
		if ($this->moduleName === 'Calendar') {
			$eventModuleFieldList = \Vtiger_Module_Model::getInstance('Events')->getFields();
			$moduleFields = array_merge($moduleFields, $eventModuleFieldList);
		}
		foreach ($moduleFields as $fieldName => &$fieldModel) {
			$dataType = $fieldModel->getFieldDataType();
			if (in_array($dataType, \Vtiger_Field_Model::$REFERENCE_TYPES)) {
				$this->referenceFields[$fieldName] = $fieldModel->getReferenceList();
			}
			if ($dataType === 'owner') {
				$this->ownerFields[] = $fieldName;
			}
		}
		return $this->fieldsModel = $moduleFields;
	}

	/**
	 * Get field module
	 * @return type
	 */
	public function getModuleField($fieldName)
	{
		if (!$this->fieldsModel) {
			$this->getModuleFields();
		}
		if (isset($this->fieldsModel[$fieldName])) {
			return $this->fieldsModel[$fieldName];
		}
		return false;
	}

	/**
	 * Fix date time value
	 * @param string $fieldName
	 * @param string $value
	 * @param boolean $first
	 * @return string
	 */
	public function fixDateTimeValue($fieldName, $value, $first = true)
	{
		$field = $this->getModuleField($fieldName);
		$type = $field ? $field->getFieldDataType() : false;
		if ($type === 'datetime') {
			if (strrpos($value, ' ') === false) {
				if ($first) {
					$value .= ' 00:00:00';
				} else {
					$value .= ' 23:59:59';
				}
			}
		}
		return $value;
	}

	/**
	 * Get custom view by id
	 * @param mixed $viewId
	 */
	public function initForCustomViewById($viewId)
	{
		$this->fields[] = 'id';
		$customView = new CustomView($this->moduleName);
		$this->cvColumns = $customView->getColumnsListByCvid($viewId);
		if ($this->cvColumns) {
			foreach ($this->cvColumns as &$cvColumn) {
				$details = explode(':', $cvColumn);
				if (empty($details[2]) && $details[1] === 'crmid' && $details[0] === 'vtiger_crmentity') {
					$this->customViewFields[] = 'id';
				} else {
					$this->fields[] = $details[2];
					$this->customViewFields[] = $details[2];
				}
			}
		}
		if ($this->moduleName === 'Calendar' && !in_array('activitytype', $this->fields)) {
			$this->fields[] = 'activitytype';
		}
		if ($this->moduleName === 'Documents') {
			if (in_array('filename', $this->fields)) {
				if (!in_array('filelocationtype', $this->fields)) {
					$this->fields[] = 'filelocationtype';
				}
				if (!in_array('filestatus', $this->fields)) {
					$this->fields[] = 'filestatus';
				}
			}
		}
		$this->stdFilterList = $customView->getStdFilterByCvid($viewId);
		$this->advFilterList = $customView->getAdvFilterByCvid($viewId);
		if (is_array($this->stdFilterList)) {
			$value = [];
			if (!empty($this->stdFilterList['columnname'])) {
				//$this->startGroup('');
				$name = explode(':', $this->stdFilterList['columnname']);
				$name = $name[2];
				$value[] = $this->fixDateTimeValue($name, $this->stdFilterList['startdate']);
				$value[] = $this->fixDateTimeValue($name, $this->stdFilterList['enddate'], false);
				//$this->addCondition($name, $value, 'BETWEEN');
			}
		}
		if ($this->conditionInstanceCount <= 0 && is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			//$this->startGroup('');
		} elseif ($this->conditionInstanceCount > 0 && is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			//$this->addConditionGlue(self::$AND);
		}
		//$this->parseAdvFilterList();
		if ($this->conditionInstanceCount > 0) {
			//$this->endGroup();
		}
	}
	/**
	  public function parseAdvFilterList()
	  {
	  if (!$this->advFilterList) {
	  return false;
	  }
	  if (!empty($glue))
	  $this->addConditionGlue($glue);

	  $customView = new CustomView($this->module);
	  $dateSpecificConditions = $customView->getStdFilterConditions();
	  foreach ($this->advFilterList as $groupindex => $groupcolumns) {
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
	  $countValue = count($value);
	  for ($i = 0; $i < $countValue; $i++) {
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
	 */

	/**
	 * Create query
	 * @return \App\Db\Query
	 */
	public function createQuery()
	{
		$this->loadSelect();
		$this->loadFrom();
		$this->loadJoin();
		$this->loadWhere();
		return $this->getQuery();
	}

	/**
	 * Sets the SELECT part of the query.
	 */
	public function loadSelect()
	{
		$allFields = array_keys($this->getModuleFields());
		$allFields[] = 'id';
		$this->fields = array_intersect($this->fields, $allFields);
		$columns = [];
		foreach ($this->fields as &$fieldName) {
			$columns[] = $this->getColumnName($fieldName);
			//To merge date and time fields
			if ($this->moduleName === 'Calendar' && ($fieldName === 'date_start' || $fieldName === 'due_date')) {
				if ($fieldName === 'date_start') {
					$timeField = 'time_start';
					$columns[] = $this->getColumnName($timeField);
				} elseif ($fieldName === 'due_date') {
					$timeField = 'time_end';
					$columns[] = $this->getColumnName($timeField);
				}
			}
		}
		foreach ($this->customColumns as $customColumn) {
			$columns[] = $customColumn;
		}
		$this->query->select($columns);
	}

	/**
	 * Get column name by field name
	 * @param string $fieldName
	 * @return string
	 */
	public function getColumnName($fieldName)
	{
		if ($fieldName === 'id') {
			$baseTable = $this->entityModel->table_name;
			return $baseTable . '.' . $this->entityModel->tab_name_index[$baseTable];
		}
		$field = $this->getModuleField($fieldName);
		return $field->getTableName() . '.' . $field->getColumnName();
	}

	/**
	 * Sets the FROM part of the query.
	 */
	public function loadFrom()
	{
		$this->query->from($this->entityModel->table_name);
	}

	/**
	 * Sets the JOINs part of the query.
	 */
	public function loadJoin($onlyTableJoin = false)
	{
		$tableList = $tableJoinCondition = $tableJoin = [];
		$moduleTableIndexList = $this->entityModel->tab_name_index;
		$baseTable = $this->entityModel->table_name;
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		foreach ($this->fields as &$fieldName) {
			if ($fieldName === 'id') {
				continue;
			}
			$field = $this->getModuleField($fieldName);
			if ($field->getFieldDataType() === 'reference') {
				$tableJoin[$field->getTableName()] = 'INNER JOIN';
				foreach ($this->referenceFields[$fieldName] as &$moduleName) {
					if ($moduleName === 'Users' && $this->moduleName !== 'Users') {
						$tableJoinCondition[$fieldName]['vtiger_users' . $fieldName] = "{$field->getTableName()}.{$field->getColumnName()} = vtiger_users{$fieldName}.id";
						$tableJoinCondition[$fieldName]['vtiger_groups' . $fieldName] = "{$field->getTableName()}.{$field->getColumnName()} = vtiger_groups{$fieldName}.groupid";
						$tableJoin['vtiger_users' . $fieldName] = 'LEFT JOIN';
						$tableJoin['vtiger_groups' . $fieldName] = 'LEFT JOIN';
					}
				}
			} elseif ($field->getFieldDataType() === 'owner' && $fieldName === 'created_user_id') {
				$tableJoinCondition[$fieldName]['vtiger_users' . $fieldName] = "{$field->getTableName()}.{$field->getColumnName()} = vtiger_users{$fieldName}.id";
				$tableJoinCondition[$fieldName]['vtiger_groups' . $fieldName] = "{$field->getTableName()}.{$field->getColumnName()} = vtiger_groups{$fieldName}.groupid";
				$tableJoin['vtiger_users' . $fieldName] = 'LEFT JOIN';
				$tableJoin['vtiger_groups' . $fieldName] = 'LEFT JOIN';
			}
			if (!isset($tableList[$field->getTableName()])) {
				$tableList[$field->getTableName()] = $field->getTableName();
				$tableJoin[$field->getTableName()] = $this->entityModel->getJoinClause($field->getTableName());
			}
		}
		foreach ($this->whereFields as &$fieldName) {
			if (empty($fieldName)) {
				continue;
			}
			$field = $this->getModuleField($fieldName);
			if (empty($field)) {
				continue;
			}
			$tableName = $field->getTableName();
			// When a field is included in Where Clause, but not is Select Clause, and the field table is not base table,
			// The table will not be present in tablesList and hence needs to be added to the list.
			if (!isset($tableList[$tableName])) {
				$tableList[$tableName] = $tableName;
				$tableJoin[$tableName] = $this->entityModel->getJoinClause($tableName);
			}
			if (in_array($field->getFieldDataType(), \Vtiger_Field_Model::$REFERENCE_TYPES)) {
				if (!(\AppConfig::performance('SEARCH_REFERENCE_BY_AJAX') && isset($this->whereOperator[$fieldName]) && $this->whereOperator[$fieldName] === 'e')) {
					// This is special condition as the data is not stored in the base table,
					$tableJoin[$field->getTableName()] = 'INNER JOIN';
					foreach ($this->referenceFields[$fieldName] as &$moduleName) {
						$referenceModuleModel = \Vtiger_Module_Model::getInstance($moduleName);
						$referenceEntityModel = \CRMEntity::getInstance($moduleName);
						$tableIndexList = $referenceEntityModel->tab_name_index;
						$entityInfo = Module::getEntityInfo($moduleName);
						foreach ($entityInfo['fieldnameArr'] as &$fieldName) {
							$referenceField = \Vtiger_Field_Model::getInstance($fieldName, $referenceModuleModel);
							$referenceTable = $referenceField->getTableName();
							$referenceTableIndex = $tableIndexList[$referenceTable];

							$referenceTableName = "$referenceTable $referenceTable$fieldName";
							$referenceTable = "$referenceTable$fieldName";
							//should always be left join for cases where we are checking for null
							//reference field values.
							if (!isset($tableJoin[$referenceTable])) {  // table already added in from clause
								$tableJoin[$referenceTableName] = 'LEFT JOIN';
								$tableJoinCondition[$fieldName][$referenceTableName] = "$tableName.{$field->getColumnName()} = $referenceTable.$referenceTableIndex";
							}
						}
					}
				}
			} elseif ($field->getFieldDataType() === 'owner') {
				$add = true;
				if (isset($this->whereOperator[$fieldName]) && ($this->whereOperator[$fieldName] === 'om' || $this->whereOperator[$fieldName] === 'e')) {
					$add = false;
				}
				if ($add) {
					$tableList['vtiger_users'] = 'vtiger_users';
					$tableList['vtiger_groups'] = 'vtiger_groups';
					$tableJoin['vtiger_users'] = 'LEFT JOIN';
					$tableJoin['vtiger_groups'] = 'LEFT JOIN';
				}
			} else {
				$tableList[$field->getTableName()] = $field->getTableName();
				$tableJoin[$field->getTableName()] = $this->entityModel->getJoinClause($field->getTableName());
			}
		}
		foreach ($this->getEntityDefaultTableList() as &$table) {
			if (!isset($tableList[$table])) {
				$tableList[$table] = $table;
				$tableJoin[$table] = 'INNER JOIN';
			}
		}
		if ($this->ownerFields) {
			//there are more than one field pointing to the users table, the real one is the one called assigned_user_id if there is one, otherwise pick the first
			if (in_array('assigned_user_id', $this->ownerFields)) {
				$ownerField = 'assigned_user_id';
			} else {
				$ownerField = $this->ownerFields[0];
			}
		}
		foreach ($this->customTable as &$table) {
			$tableName = $table['name'];
			$tableList[$tableName] = $tableName;
			$tableJoin[$tableName] = $table['join'];
		}
		foreach ($this->whereClauseCustom as &$where) {
			if (isset($where['tablename']) && ($baseTable !== $where['tablename'] && !isset($tableList[$where['tablename']]))) {
				$tableList[$where['tablename']] = $where['tablename'];
				$tableJoin[$where['tablename']] = 'LEFT JOIN';
			}
		}
		foreach ($this->getEntityDefaultTableList() as &$tableName) {
			$this->query->join($tableJoin[$tableName], $tableName, "$baseTable.$baseTableIndex = $tableName.{$moduleTableIndexList[$tableName]}");
			unset($tableList[$tableName]);
		}
		unset($tableList[$baseTable]);
		foreach ($tableList as &$tableName) {
			if ($tableName === 'vtiger_users') {
				$field = $this->getModuleField($ownerField);
				$this->query->join($tableJoin[$tableName], $tableName, "{$field->getTableName()}.{$field->getColumnName()} = $tableName.id");
			} elseif ($tableName == 'vtiger_groups') {
				$field = $this->getModuleField($ownerField);
				$this->query->join($tableJoin[$tableName], $tableName, "{$field->getTableName()}.{$field->getColumnName()} = $tableName.groupid");
			} else {
				$this->query->join($tableJoin[$tableName], $tableName, "$baseTable.$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]");
			}
		}
		foreach ($tableJoinCondition as $fieldName => &$conditionInfo) {
			foreach ($conditionInfo as $tableName => &$condition) {
				if (!empty($tableList[$tableName])) {
					$tableNameAlias = $tableName . '2';
					$condition = str_replace($tableName, $tableNameAlias, $condition);
				} else {
					$tableNameAlias = '';
				}
				$this->query->join($tableJoin[$tableName], "$tableName $tableNameAlias", $condition);
			}
		}
		foreach ($this->m2mRelModConditions as &$conditionInfo) {
			$relatedModuleMeta = \RelatedModuleMeta::getInstance($this->moduleName, $conditionInfo['relatedModule']);
			$relationInfo = $relatedModuleMeta->getRelationMeta();
			$this->query->innerJoin($relationInfo['relationTable'], "{$relationInfo['relationTable']}.{$relationInfo[$this->moduleName]} = $baseTable.$baseTableIndex");
		}
		// Adding support for conditions on reference module fields
		if ($this->referenceModuleField) {
			$referenceFieldTableList = [];
			foreach ($this->referenceModuleField as &$conditionInfo) {
				$relatedEntityModel = \CRMEntity::getInstance($conditionInfo['relatedModule']);
				$tabIndex = $relatedEntityModel->tab_name_index;
				$referenceField = $this->getModuleField($conditionInfo['referenceField']);
				$fieldModel = \Vtiger_Field_Model::getInstance($conditionInfo['fieldName'], \Vtiger_Module_Model::getInstance($conditionInfo['relatedModule']));
				if (empty($fieldModel)) {
					continue;
				}
				$tableName = $fieldModel->getTableName();
				if (!isset($referenceFieldTableList[$tableName])) {
					$this->query->leftJoin("$tableName $tableName{$conditionInfo['referenceField']}", "$tableName{$conditionInfo['referenceField']}.{$tabIndex[$tableName]} = {$referenceField->getTableName()}.{$referenceField->getColumnName()}");
					$referenceFieldTableList[$tableName] = $tableName;
				}
			}
		}
		foreach ($this->fromClauseCustom as $where) {
			$this->query->join($where['joinType'], $where['relatedTable'], "{$where['relatedTable']}.{$where['relatedIndex']} = {$where['baseTable']}.{$where['baseIndex']}");
		}
	}

	/**
	 * Get entity default table list
	 * @return type
	 */
	public function getEntityDefaultTableList()
	{
		if (isset($this->entityModel->tab_name_index['vtiger_crmentity'])) {
			return ['vtiger_crmentity'];
		}
		return [];
	}

	/**
	 * Sets the WHERE part of the query.
	 */
	public function loadWhere()
	{
		
	}
}
