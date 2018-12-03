<?php

namespace App;

/**
 * Query generator class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class QueryGenerator
{
	const STRING_TYPE = ['string', 'text', 'email', 'reference'];
	const NUMERIC_TYPE = ['integer', 'double', 'currency'];
	const DATE_TYPE = ['date', 'datetime'];
	const EQUALITY_TYPES = ['currency', 'percentage', 'double', 'integer', 'number'];
	const COMMA_TYPES = ['picklist', 'multipicklist', 'owner', 'date', 'datetime', 'time', 'tree', 'sharedOwner', 'sharedOwner'];

	/**
	 * State records to display
	 * 0 - Active
	 * 1 - Trash
	 * 2 - Archived.
	 *
	 * @var int
	 */
	private $stateCondition = 0;

	/** @var bool Permissions conditions */
	public $permissions = true;

	/** @var string Module name */
	private $moduleName;

	/** @var \App\Db\Query */
	private $query;

	/** @var \App\Db\Query */
	private $buildedQuery;
	private $fields = [];
	private $referenceFields = [];
	private $ownerFields = [];
	private $customColumns = [];
	private $cvColumns;
	private $advFilterList;
	private $conditions;

	/**
	 * Search fields for duplicates.
	 *
	 * @var array
	 */
	private $searchFieldsForDuplicates = [];

	/** @var array Joins */
	private $joins = [];

	/** @var string[] Tables list */
	private $tablesList = [];
	private $queryFields = [];
	private $order = [];
	private $group = [];
	private $sourceRecord;
	private $concatColumn = [];
	private $relatedFields = [];
	private $relatedQueryFields = [];

	/**
	 * @var bool
	 */
	private $ignoreComma = false;

	/**
	 * @var array Required conditions
	 */
	private $conditionsAnd = [];

	/**
	 * @var array Optional conditions
	 */
	private $conditionsOr = [];

	/**
	 * @var \Vtiger_Module_Model
	 */
	private $moduleModel;

	/**
	 * @var Vtiger_Field_Model[]
	 */
	private $fieldsModel;

	/**
	 * @var Vtiger_Field_Model[]
	 */
	private $relatedFieldsModel;

	/**
	 * @var \CRMEntity
	 */
	private $entityModel;

	/** @var User */
	private $user;

	/** @var Limit */
	private $limit;

	/** @var Offset */
	private $offset;

	/**
	 * QueryGenerator construct.
	 *
	 * @param string $moduleName
	 * @param mixed  $userId
	 */
	public function __construct($moduleName, $userId = false)
	{
		$this->moduleName = $moduleName;
		$this->moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$this->entityModel = \CRMEntity::getInstance($moduleName);
		$this->user = User::getUserModel($userId ? $userId : User::getCurrentUserId());
	}

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function getModule()
	{
		return $this->moduleName;
	}

	/**
	 * Get module model.
	 *
	 * @return string
	 */
	public function getModuleModel()
	{
		return $this->moduleModel;
	}

	/**
	 * Get query fields.
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Get list view query fields.
	 *
	 * @return array
	 */
	public function getListViewFields()
	{
		$headerFields = [];
		foreach ($this->getFields() as $fieldName) {
			if ($model = $this->getModuleField($fieldName)) {
				$headerFields[$fieldName] = $model;
				if ($field = $this->getQueryField($fieldName)->getListViewFields()) {
					$headerFields[$field->getName()] = $field;
					$this->fields[] = $field->getName();
				}
			}
		}
		return $headerFields;
	}

	/**
	 * Set query fields.
	 *
	 * @param string[] $fields
	 *
	 * @return \self
	 */
	public function setFields($fields)
	{
		$this->fields = $fields;

		return $this;
	}

	/**
	 * Set query offset.
	 *
	 * @param int $offset
	 *
	 * @return \self
	 */
	public function setOffset($offset)
	{
		$this->offset = $offset;

		return $this;
	}

	/**
	 * Set query limit.
	 *
	 * @param int $limit
	 *
	 * @return \self
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;

		return $this;
	}

	/**
	 * Get query limit.
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Returns related fields.
	 *
	 * @return array
	 */
	public function getRelatedFields()
	{
		return $this->relatedFields;
	}

	/**
	 * Set query field.
	 *
	 * @param string|string[] $fields
	 *
	 * @return \self
	 */
	public function setField($fields)
	{
		if (is_array($fields)) {
			foreach ($fields as $field) {
				$this->fields[] = $field;
			}
		} else {
			$this->fields[] = $fields;
		}
		return $this;
	}

	/**
	 * Load base module list fields.
	 */
	public function loadListFields()
	{
		$listFields = $this->entityModel->list_fields_name;
		$listFields[] = 'id';
		$this->fields = $listFields;
	}

	/**
	 * Set custom column.
	 *
	 * @param type $columns
	 *
	 * @return \self
	 */
	public function setCustomColumn($columns)
	{
		if (is_array($columns)) {
			foreach ($columns as $key => $column) {
				if (is_numeric($key)) {
					$this->customColumns[] = $column;
				} else {
					$this->customColumns[$key] = $column;
				}
			}
		} else {
			$this->customColumns[] = $columns;
		}
		return $this;
	}

	/**
	 * Set concat column.
	 *
	 * @param type $columns
	 *
	 * @return \self
	 */
	public function setConcatColumn($fieldName, $concat)
	{
		$this->concatColumn[$fieldName] = $concat;

		return $this;
	}

	/**
	 * Get CRMEntity Model.
	 *
	 * @return \CRMEntity
	 */
	public function getEntityModel()
	{
		return $this->entityModel;
	}

	/**
	 * Get reference fields.
	 *
	 * @param string $fieldName
	 *
	 * @return array
	 */
	public function getReference($fieldName)
	{
		return $this->referenceFields[$fieldName];
	}

	/**
	 * Add a mandatory condition.
	 *
	 * @param array $condition
	 * @param bool  $groupAnd
	 */
	public function addNativeCondition($condition, $groupAnd = true)
	{
		if ($groupAnd) {
			$this->conditionsAnd[] = $condition;
		} else {
			$this->conditionsOr[] = $condition;
		}
	}

	/**
	 * Returns related fields for section SELECT.
	 *
	 * @return array
	 */
	public function loadRelatedFields()
	{
		$fields = [];
		$checkIds = [];
		foreach ($this->relatedFields as $field) {
			$relatedFieldModel = $this->addRelatedJoin($field);
			if (!isset($checkIds[$field['sourceField']][$field['relatedModule']])) {
				$checkIds[$field['sourceField']][$field['relatedModule']] = $field['relatedModule'];
				$fields["{$field['sourceField']}{$field['relatedModule']}id"] = $relatedFieldModel->getTableName() . $field['sourceField'] . '.' . \Vtiger_CRMEntity::getInstance($field['relatedModule'])->tab_name_index[$relatedFieldModel->getTableName()];
			}
			$fields["{$field['sourceField']}{$field['relatedModule']}{$relatedFieldModel->getName()}"] = "{$relatedFieldModel->getTableName()}{$field['sourceField']}.{$relatedFieldModel->getColumnName()}";
		}
		return $fields;
	}

	/**
	 * Set related field.
	 *
	 * @param string[] $field
	 *
	 * @return \self
	 */
	public function addRelatedField($field)
	{
		$this->relatedFields[] = $field;

		return $this;
	}

	/**
	 * Set source record.
	 *
	 * @param int $sourceRecord
	 */
	public function setSourceRecord($sourceRecord)
	{
		$this->sourceRecord = $sourceRecord;
	}

	/**
	 * Appends a JOIN part to the query.
	 *
	 * @param array $join
	 */
	public function addJoin($join)
	{
		if (isset($this->joins[$join[1]])) {
			return false;
		}
		$this->joins[$join[1]] = $join;
	}

	/**
	 * Add table to query.
	 *
	 * @param string $tableName
	 */
	public function addTableToQuery($tableName)
	{
		$this->tablesList[$tableName] = $tableName;
	}

	/**
	 * Set ignore comma.
	 *
	 * @param bool $val
	 */
	public function setIgnoreComma($val)
	{
		$this->ignoreComma = $val;
	}

	/**
	 * Get ignore comma.
	 *
	 * @return bool
	 */
	public function getIgnoreComma()
	{
		return $this->ignoreComma;
	}

	/**
	 * Set order.
	 *
	 * @param string $fieldName
	 * @param string $order     ASC/DESC
	 *
	 * @return \self
	 */
	public function setOrder($fieldName, $order = false)
	{
		$queryField = $this->getQueryField($fieldName);
		$this->order = array_merge($this->order, $queryField->getOrderBy($order));

		return $this;
	}

	/**
	 * Set group.
	 *
	 * @param string $fieldName
	 *
	 * @return \self
	 */
	public function setGroup($fieldName)
	{
		$queryField = $this->getQueryField($fieldName);
		$this->group[] = $queryField->getColumnName();

		return $this;
	}

	/**
	 * Set custom group.
	 *
	 * @param string|array $groups
	 *
	 * @return \self
	 */
	public function setCustomGroup($groups)
	{
		if (is_array($groups)) {
			foreach ($groups as $key => $group) {
				if (is_numeric($key)) {
					$this->group[] = $group;
				} else {
					$this->group[$key] = $group;
				}
			}
		} else {
			$this->group[] = $groups;
		}
		return $this;
	}

	/**
	 * Function sets the field for which the duplicated values will be searched.
	 *
	 * @param string   $fieldName
	 * @param int|bool $ignoreEmptyValue
	 */
	public function setSearchFieldsForDuplicates($fieldName, $ignoreEmptyValue = true)
	{
		$field = $this->getModuleField($fieldName);
		if ($field && !isset($this->tablesList[$field->getTableName()])) {
			$this->tablesList[$field->getTableName()] = $field->getTableName();
		}
		$this->searchFieldsForDuplicates[$fieldName] = $ignoreEmptyValue;
	}

	/**
	 * Get fields module.
	 *
	 * @return array
	 */
	public function getModuleFields()
	{
		if ($this->fieldsModel) {
			return $this->fieldsModel;
		}
		$moduleFields = $this->moduleModel->getFields();
		foreach ($moduleFields as $fieldName => &$fieldModel) {
			if ($fieldModel->isReferenceField()) {
				$this->referenceFields[$fieldName] = $fieldModel->getReferenceList();
			}
			if ($fieldModel->getFieldDataType() === 'owner') {
				$this->ownerFields[] = $fieldName;
			}
		}
		return $this->fieldsModel = $moduleFields;
	}

	/**
	 * Get fields module.
	 *
	 * @param string $moduleName
	 *
	 * @return \Vtiger_Field_Model[]
	 */
	public function getRelatedModuleFields(string $moduleName)
	{
		if (isset($this->relatedFieldsModel[$moduleName])) {
			return $this->relatedFieldsModel[$moduleName];
		}
		return $this->relatedFieldsModel[$moduleName] = \Vtiger_Module_Model::getInstance($moduleName)->getFields();
	}

	/**
	 * Get field module.
	 *
	 * @param string $fieldName
	 *
	 * @return \Vtiger_Field_Model|bool
	 */
	public function getModuleField(string $fieldName)
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
	 *  Get field in related module.
	 *
	 * @param string $fieldName
	 * @param string $moduleName
	 *
	 * @return \Vtiger_Field_Model|bool
	 */
	public function getRelatedModuleField(string $fieldName, string $moduleName)
	{
		if (!$this->relatedFieldsModel[$moduleName]) {
			$this->getRelatedModuleFields($moduleName);
		}
		if (isset($this->relatedFieldsModel[$moduleName][$fieldName])) {
			return $this->relatedFieldsModel[$moduleName][$fieldName];
		}
		return false;
	}

	/**
	 * Get default custom view query.
	 *
	 * @return \App\Db\Query
	 */
	public function getDefaultCustomViewQuery()
	{
		$customView = CustomView::getInstance($this->moduleName, $this->user);
		$viewId = $customView->getViewId();
		if (empty($viewId) || $viewId === 0) {
			return false;
		}
		return $this->getCustomViewQueryById($viewId);
	}

	/**
	 * Init function for default custom view.
	 *
	 * @param bool $noCache
	 * @param bool $onlyFields
	 *
	 * @return mixed
	 */
	public function initForDefaultCustomView($noCache = false, $onlyFields = false)
	{
		$customView = CustomView::getInstance($this->moduleName, $this->user);
		$viewId = $customView->getViewId($noCache);
		if (empty($viewId) || $viewId === 0) {
			return false;
		}
		$this->initForCustomViewById($viewId, $onlyFields);
		return $viewId;
	}

	/**
	 * Get custom view query by id.
	 *
	 * @param string|int $viewId
	 *
	 * @return \App\Db\Query
	 */
	public function getCustomViewQueryById($viewId)
	{
		$this->initForCustomViewById($viewId);

		return $this->createQuery();
	}

	/**
	 * Add custom view fields from column.
	 *
	 * @param string[] $cvColumn
	 */
	private function addCustomViewFields(array $cvColumn)
	{
		$fieldName = $cvColumn['field_name'];
		$sourceFieldName = $cvColumn['source_field_name'];
		if (empty($sourceFieldName)) {
			$this->customViewFields[] = 'id';
			if ($fieldName !== 'id') {
				$this->fields[] = $fieldName;
			}
		} else {
			$this->addRelatedField([
				'sourceField' => $sourceFieldName,
				'relatedModule' => $cvColumn['module_name'],
				'relatedField' => $fieldName
			]);
		}
	}

	/**
	 * Get custom view by id.
	 *
	 * @param mixed $viewId
	 * @param bool  $onlyFields
	 */
	public function initForCustomViewById($viewId, $onlyFields = false)
	{
		$this->fields[] = 'id';
		$customView = CustomView::getInstance($this->moduleName, $this->user);
		$this->cvColumns = $customView->getColumnsListByCvid($viewId);
		if ($this->cvColumns) {
			foreach ($this->cvColumns as &$cvColumn) {
				$this->addCustomViewFields($cvColumn);
			}
		}
		foreach (CustomView::getDuplicateFields($viewId) as $fields) {
			$this->setSearchFieldsForDuplicates($fields['fieldname'], (bool) $fields['ignore']);
		}
		if ($this->moduleName === 'Calendar' && !in_array('activitytype', $this->fields)) {
			$this->fields[] = 'activitytype';
		}
		if ($this->moduleName === 'Documents' && in_array('filename', $this->fields)) {
			if (!in_array('filelocationtype', $this->fields)) {
				$this->fields[] = 'filelocationtype';
			}
			if (!in_array('filestatus', $this->fields)) {
				$this->fields[] = 'filestatus';
			}
		}
		if (!$onlyFields) {
			$this->conditions = CustomView::getConditions($viewId);
		}
	}

	/**
	 * Parse conditions to section where in query.
	 *
	 * @param array|null $conditions
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	private function parseConditions(?array $conditions): array
	{
		if (empty($conditions)) {
			return [];
		}
		$where = [$conditions['condition']];
		foreach ($conditions['rules'] as $rule) {
			if (isset($rule['condition'])) {
				$where[] = $this->parseConditions($rule);
			} else {
				[$moduleName, $fieldName, $sourceFieldName] = array_pad(explode(':', $rule['fieldname']), 3, false);
				if (!empty($sourceFieldName)) {
					$condition = $this->getRelatedCondition([
						'relatedModule' => $moduleName,
						'relatedField' => $fieldName,
						'sourceField' => $sourceFieldName,
						'value' => $rule['value'],
						'operator' => $rule['operator']
					]);
				} else {
					$condition = $this->getCondition($fieldName, $rule['value'], $rule['operator']);
				}
				if ($condition) {
					$where[] = $condition;
				}
			}
		}
		return $where;
	}

	/**
	 * Parsing advanced filters conditions.
	 *
	 * @return bool
	 */
	public function parseAdvFilter($advFilterList = false)
	{
		if (!$advFilterList) {
			$advFilterList = $this->advFilterList;
		}
		if (!$advFilterList) {
			return false;
		}
		foreach ($advFilterList as $group => &$filters) {
			$and = ($group === 'and' || (int) $group === 1);
			if (isset($filters['columns'])) {
				$filters = $filters['columns'];
			}
			foreach ($filters as &$filter) {
				if (isset($filter['columnname'])) {
					list($tableName, $columnName, $fieldName) = array_pad(explode(':', $filter['columnname']), 3, false);
					if (empty($fieldName) && $columnName === 'crmid' && $tableName === 'vtiger_crmentity') {
						$fieldName = $this->getColumnName('id');
					}
					$this->addCondition($fieldName, $filter['value'], $filter['comparator'], $and);
				} else {
					if (!empty($filter['source_field_name'])) {
						$this->addRelatedCondition([
							'sourceField' => $filter['source_field_name'],
							'relatedModule' => $filter['module_name'],
							'relatedField' => $filter['field_name'],
							'value' => $filter['value'],
							'operator' => $filter['comparator'],
							'conditionGroup' => $and
						]);
					} else {
						$this->addCondition($filter['field_name'], $filter['value'], $filter['comparator'], $and);
					}
				}
			}
		}
	}

	/**
	 * Create query.
	 *
	 * @return \App\Db\Query
	 */
	public function createQuery($reBuild = false)
	{
		if (!$this->buildedQuery || $reBuild) {
			$this->query = new Db\Query();
			$this->loadSelect();
			$this->loadFrom();
			$this->loadWhere();
			$this->loadOrder();
			$this->loadJoin();
			$this->loadGroup();
			if (!empty($this->limit)) {
				$this->query->limit($this->limit);
			}
			if (!empty($this->offset)) {
				$this->query->offset($this->offset);
			}
			$this->buildedQuery = $this->query;
		}
		return $this->buildedQuery;
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
			if (isset($this->concatColumn[$fieldName])) {
				$columns[$fieldName] = new \yii\db\Expression($this->concatColumn[$fieldName]);
			} else {
				$columns[$fieldName] = $this->getColumnName($fieldName);
			}
		}
		foreach ($this->customColumns as $key => $customColumn) {
			if (is_numeric($key)) {
				$columns[] = $customColumn;
			} else {
				$columns[$key] = $customColumn;
			}
		}
		$this->query->select(array_merge($columns, $this->loadRelatedFields()));
	}

	/**
	 * Get column name by field name.
	 *
	 * @param string $fieldName
	 *
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
	public function loadJoin()
	{
		$tableJoin = [];
		$moduleTableIndexList = $this->entityModel->tab_name_index;
		$baseTable = $this->entityModel->table_name;
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		foreach ($this->fields as $fieldName) {
			if ($fieldName === 'id') {
				continue;
			}
			$field = $this->getModuleField($fieldName);
			if ($field->getFieldDataType() === 'reference') {
				$tableJoin[$field->getTableName()] = 'INNER JOIN';
				foreach ($this->referenceFields[$fieldName] as $moduleName) {
					if ($moduleName === 'Users' && $this->moduleName !== 'Users') {
						$this->addJoin(['LEFT JOIN', 'vtiger_users vtiger_users' . $fieldName, "{$field->getTableName()}.{$field->getColumnName()} = vtiger_users{$fieldName}.id"]);
						$this->addJoin(['LEFT JOIN', 'vtiger_groups vtiger_groups' . $fieldName, "{$field->getTableName()}.{$field->getColumnName()} = vtiger_groups{$fieldName}.groupid"]);
					}
				}
			} elseif ($field->getFieldDataType() === 'owner' && $fieldName === 'created_user_id') {
				$this->addJoin(['LEFT JOIN', 'vtiger_users vtiger_users' . $fieldName, "{$field->getTableName()}.{$field->getColumnName()} = vtiger_users{$fieldName}.id"]);
				$this->addJoin(['LEFT JOIN', 'vtiger_groups vtiger_groups' . $fieldName, "{$field->getTableName()}.{$field->getColumnName()} = vtiger_groups{$fieldName}.groupid"]);
			}
			if (!isset($this->tablesList[$field->getTableName()])) {
				$this->tablesList[$field->getTableName()] = $field->getTableName();
				$tableJoin[$field->getTableName()] = $this->entityModel->getJoinClause($field->getTableName());
			}
		}
		foreach ($this->getEntityDefaultTableList() as $table) {
			if (!isset($this->tablesList[$table])) {
				$this->tablesList[$table] = $table;
			}
			$tableJoin[$table] = 'INNER JOIN';
		}
		if ($this->ownerFields) {
			//there are more than one field pointing to the users table, the real one is the one called assigned_user_id if there is one, otherwise pick the first
			if (in_array('assigned_user_id', $this->ownerFields)) {
				$ownerField = 'assigned_user_id';
			} else {
				$ownerField = $this->ownerFields[0];
			}
		}
		foreach ($this->getEntityDefaultTableList() as $tableName) {
			$this->query->join($tableJoin[$tableName], $tableName, "$baseTable.$baseTableIndex = $tableName.{$moduleTableIndexList[$tableName]}");
			unset($this->tablesList[$tableName]);
		}
		unset($this->tablesList[$baseTable]);
		foreach ($this->tablesList as $tableName) {
			$joinType = $tableJoin[$tableName] ?? $this->entityModel->getJoinClause($tableName);
			if ($tableName === 'vtiger_users') {
				$field = $this->getModuleField($ownerField);
				$this->addJoin([$joinType, $tableName, "{$field->getTableName()}.{$field->getColumnName()} = $tableName.id"]);
			} elseif ($tableName == 'vtiger_groups') {
				$field = $this->getModuleField($ownerField);
				$this->addJoin([$joinType, $tableName, "{$field->getTableName()}.{$field->getColumnName()} = $tableName.groupid"]);
			} else {
				$this->addJoin([$joinType, $tableName, "$baseTable.$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]"]);
			}
		}
		if ($this->searchFieldsForDuplicates) {
			$duplicateCheckClause = [];
			$queryGenerator = new self($this->moduleName, $this->user->getId());
			$queryGenerator->permissions = $this->permissions;
			$queryGenerator->setFields(array_keys($this->searchFieldsForDuplicates));
			foreach ($this->searchFieldsForDuplicates as $fieldName => $ignoreEmptyValue) {
				if ($ignoreEmptyValue) {
					$queryGenerator->addCondition($fieldName, '', 'ny');
				}
				$queryGenerator->setGroup($fieldName);
				$fieldModel = $this->getModuleField($fieldName);
				$duplicateCheckClause[] = $fieldModel->getTableName() . '.' . $fieldModel->getColumnName() . ' = duplicates.' . $fieldModel->getFieldName();
			}
			$subQuery = $queryGenerator->createQuery();
			$subQuery->andHaving((new \yii\db\Expression('COUNT(1) > 1')));
			$this->joins['duplicates'] = ['INNER JOIN', ['duplicates' => $subQuery], implode(' AND ', $duplicateCheckClause)];
		}
		foreach ($this->joins as $join) {
			$on = $join[2] ?? '';
			$params = $join[3] ?? [];
			$this->query->join($join[0], $join[1], $on, $params);
		}
	}

	/**
	 * Get entity default table list.
	 *
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
		if ($this->stateCondition !== false) {
			$this->query->andWhere($this->getStateCondition());
		}
		$this->query->andWhere(['and', array_merge(['and'], $this->conditionsAnd), array_merge(['or'], $this->conditionsOr)]);
		$this->query->andWhere($this->parseConditions($this->conditions));
		if ($this->permissions) {
			if (\AppConfig::security('CACHING_PERMISSION_TO_RECORD') && $this->moduleName !== 'Users') {
				$userId = $this->user->getId();
				$this->query->andWhere(['like', 'vtiger_crmentity.users', ",$userId,"]);
			} else {
				PrivilegeQuery::getConditions($this->query, $this->moduleName, $this->user, $this->sourceRecord);
			}
		}
	}

	/**
	 * Get conditions for records state.
	 *
	 * @return string|array
	 */
	private function getStateCondition()
	{
		$condition = ['vtiger_crmentity.deleted' => $this->stateCondition];
		switch ($this->moduleName) {
			case 'Leads':
				$condition += ['vtiger_leaddetails.converted' => 0];
				break;
			case 'Users':
				$condition = [];
				break;
			default:
				break;
		}
		return $condition;
	}

	/**
	 * Set state condition.
	 *
	 * @param string $state
	 */
	public function setStateCondition($state)
	{
		switch ($state) {
			default:
			case 'Active':
				$this->stateCondition = 0;
				break;
			case 'Trash':
				$this->stateCondition = 1;
				break;
			case 'Archived':
				$this->stateCondition = 2;
				break;
			case 'All':
				$this->stateCondition = false;
				break;
		}
	}

	/**
	 * Returns condition for field in this module.
	 *
	 * @param string $fieldName
	 * @param mixed  $value
	 * @param string $operator
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array|bool
	 */
	private function getCondition(string $fieldName, $value, string $operator)
	{
		$queryField = $this->getQueryField($fieldName);
		$queryField->setValue($value);
		$queryField->setOperator($operator);
		$condition = $queryField->getCondition();
		if ($condition && ($field = $this->getModuleField($fieldName)) && !isset($this->tablesList[$field->getTableName()])) {
			$this->tablesList[$field->getTableName()] = $field->getTableName();
		}
		return $condition;
	}

	/**
	 * Returns condition for field in related module.
	 *
	 * @param array $condition
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array|bool
	 */
	private function getRelatedCondition(array $condition)
	{
		$field = $this->addRelatedJoin($condition);
		if (!$field) {
			Log::error('Not found source field', __METHOD__);
			return false;
		}
		$queryField = $this->getQueryRelatedField($field, $condition);
		$queryField->setValue($condition['value']);
		$queryField->setOperator($condition['operator']);
		return $queryField->getCondition();
	}

	/**
	 * Set condition.
	 *
	 * @param string $fieldName
	 * @param mixed  $value
	 * @param string $operator
	 *
	 * @see CustomView::ADVANCED_FILTER_OPTIONS
	 * @see CustomView::STD_FILTER_CONDITIONS
	 */
	public function addCondition($fieldName, $value, $operator, $groupAnd = true)
	{
		$condition = $this->getCondition($fieldName, $value, $operator);
		if ($condition) {
			if ($groupAnd) {
				$this->conditionsAnd[] = $condition;
			} else {
				$this->conditionsOr[] = $condition;
			}
		} else {
			Log::error('Wrong condition');
		}
	}

	/**
	 * Get query field instance.
	 *
	 * @param string $fieldName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return QueryField\BaseField
	 */
	public function getQueryField($fieldName)
	{
		if (isset($this->queryFields[$fieldName])) {
			return $this->queryFields[$fieldName];
		}
		if ($fieldName === 'id') {
			$queryField = new QueryField\IdField($this, '');

			return $this->queryFields[$fieldName] = $queryField;
		}
		$field = $this->getModuleField($fieldName);
		if (empty($field)) {
			Log::error('Not found field model | Field name: ' . $fieldName);
			throw new \App\Exceptions\AppException('ERR_NOT_FOUND_FIELD_MODEL');
		}
		$className = '\App\QueryField\\' . ucfirst($field->getFieldDataType()) . 'Field';
		if (!class_exists($className)) {
			Log::error('Not found query field condition | FieldDataType: ' . ucfirst($field->getFieldDataType()));
			throw new \App\Exceptions\AppException('ERR_NOT_FOUND_QUERY_FIELD_CONDITION');
		}
		$queryField = new $className($this, $field);

		return $this->queryFields[$fieldName] = $queryField;
	}

	/**
	 * Set condition on reference module fields.
	 *
	 * @param array $condition
	 */
	public function addRelatedCondition($condition)
	{
		$queryCondition = $this->getRelatedCondition($condition);
		if ($queryCondition) {
			if ($condition['conditionGroup']) {
				$this->conditionsAnd[] = $queryCondition;
			} else {
				$this->conditionsOr[] = $queryCondition;
			}
		} else {
			Log::error('Wrong condition');
		}
	}

	/**
	 * Set related field join.
	 *
	 * @param string[] $fieldDetail
	 *
	 * @return Vtiger_Field_Model|bool
	 */
	protected function addRelatedJoin($fieldDetail)
	{
		$relatedModuleModel = \Vtiger_Module_Model::getInstance($fieldDetail['relatedModule']);
		$relatedFieldModel = $relatedModuleModel->getField($fieldDetail['relatedField']);
		if (!$relatedFieldModel || !$relatedFieldModel->isActiveField()) {
			Log::warning("Field in related module is inactive or does not exist. Related module: {$fieldDetail['referenceModule']} | Related field: {$fieldDetail['relatedField']}");

			return false;
		}
		$tableName = $relatedFieldModel->getTableName();
		$sourceFieldModel = $this->getModuleField($fieldDetail['sourceField']);
		$relatedTableName = $tableName . $fieldDetail['sourceField'];
		$relatedTableIndex = $relatedModuleModel->getEntityInstance()->tab_name_index[$tableName];
		$this->addJoin(['LEFT JOIN', "$tableName $relatedTableName", "{$sourceFieldModel->getTableName()}.{$sourceFieldModel->getColumnName()} = $relatedTableName.$relatedTableIndex"]);

		return $relatedFieldModel;
	}

	/**
	 * Get query related field instance.
	 *
	 * @param \Vtiger_Field_Model $field
	 * @param array               $relatedInfo
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return QueryField\BaseField
	 */
	private function getQueryRelatedField(\Vtiger_Field_Model $field, $relatedInfo)
	{
		$relatedModule = $relatedInfo['relatedModule'];
		if (isset($this->relatedQueryFields[$relatedModule][$field->getName()])) {
			$queryField = clone $this->relatedQueryFields[$relatedModule][$field->getName()];
			$queryField->setRelated($relatedInfo);
			return $queryField;
		}
		if ($field->getName() === 'id') {
			$queryField = new QueryField\IdField($this, '');
			$queryField->setRelated($relatedInfo);
			return $this->relatedQueryFields[$relatedModule][$field->getName()] = $queryField;
		}
		$className = '\App\QueryField\\' . ucfirst($field->getFieldDataType()) . 'Field';
		if (!class_exists($className)) {
			Log::error('Not found query field condition');
			throw new \App\Exceptions\AppException('ERR_NOT_FOUND_QUERY_FIELD_CONDITION');
		}
		$queryField = new $className($this, $field);
		$queryField->setRelated($relatedInfo);
		return $this->relatedQueryFields[$relatedModule][$field->getName()] = $queryField;
	}

	/**
	 * Set order for related module.
	 *
	 * @param string[] $orderDetail
	 *
	 * @return void
	 */
	public function setRelatedOrder(array $orderDetail)
	{
		$field = $this->addRelatedJoin($orderDetail);
		if (!$field) {
			Log::error('Not found source field');
		}
		$queryField = $this->getQueryRelatedField($field, $orderDetail);
		$this->order = array_merge($this->order, $queryField->getOrderBy($orderDetail['relatedSortOrder']));
	}

	/**
	 * Sets the ORDER BY part of the query.
	 */
	public function loadOrder()
	{
		if ($this->order) {
			$this->query->orderBy($this->order);
		}
	}

	/**
	 * Sets the GROUP BY part of the query.
	 */
	public function loadGroup()
	{
		if ($this->group) {
			$this->query->groupBy(array_unique($this->group));
		}
	}

	/**
	 * Set base search condition (search_key,search_value in url).
	 *
	 * @param string $fieldName
	 * @param mixed  $value
	 * @param string $operator
	 */
	public function addBaseSearchConditions($fieldName, $values, $operator = 'e')
	{
		if (empty($fieldName)) {
			return;
		}
		$field = $this->getModuleField($fieldName);
		$type = $field->getFieldDataType();
		if (!is_array($values)) {
			$values = [$values];
		}
		foreach ($values as &$value) {
			if ($value !== '') {
				$value = function_exists('iconv') ? iconv('UTF-8', \AppConfig::main('default_charset'), $value) : $value; // search other characters like "|, ?, ?" by jagi
				if ($type === 'currency') {
					// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
					if ($field->getUIType() === 72) {
						$value = \CurrencyField::convertToDBFormat($value, null, true);
					} else {
						$value = \CurrencyField::convertToDBFormat($value);
					}
				}
			}
			if (trim(strtolower($value)) === 'null') {
				$operator = 'e';
			}
		}
		if (count($values) === 1) {
			$values = $values[0];
		}
		$this->addCondition($fieldName, $values, $operator);
	}

	/**
	 * Parse base search condition to db condition.
	 *
	 * @param array $searchParams Example: [[["firstname","a","Tom"]]]
	 *
	 * @return array
	 */
	public function parseBaseSearchParamsToCondition($searchParams)
	{
		if (empty($searchParams)) {
			return [];
		}
		$advFilterConditionFormat = [];
		$glueOrder = ['and', 'or'];
		$groupIterator = 0;
		foreach ($searchParams as $groupInfo) {
			if (empty($groupInfo)) {
				continue;
			}
			$groupColumnsInfo = [];
			foreach ($groupInfo as $fieldSearchInfo) {
				if ($fieldSearchInfo) {
					[$fieldNameInfo, $operator, $fieldValue, $specialOption] = array_pad($fieldSearchInfo, 4, false);
					$fieldValue = Purifier::decodeHtml($fieldValue);
					[$fieldName, $moduleName, $sourceFieldName] = array_pad(explode(':', $fieldNameInfo), 3, false);
					if (!empty($sourceFieldName)) {
						$field = $this->getRelatedModuleField($fieldName, $moduleName);
					} else {
						$field = $this->getModuleField($fieldName);
					}
					if (($field->getFieldDataType() === 'tree' || $field->getFieldDataType() === 'categoryMultipicklist') && $specialOption) {
						$fieldValue = \Settings_TreesManager_Record_Model::getChildren($fieldValue, $fieldName, $this->moduleModel);
					}
					//Request will be having in terms of AM and PM but the database will be having in 24 hr format so converting
					if ($field->getFieldDataType() === 'time') {
						$fieldValue = \Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
					}
					if ($fieldName === 'date_start' || $fieldName === 'due_date' || $field->getFieldDataType() === 'datetime') {
						$dateValues = explode(',', $fieldValue);
						//Indicate whether it is fist date in the between condition
						$isFirstDate = true;
						foreach ($dateValues as $key => $dateValue) {
							$dateTimeCompoenents = explode(' ', $dateValue);
							if (empty($dateTimeCompoenents[1])) {
								if ($isFirstDate) {
									$dateTimeCompoenents[1] = '00:00:00';
								} else {
									$dateTimeCompoenents[1] = '23:59:59';
								}
							}
							$dateValue = implode(' ', $dateTimeCompoenents);
							$dateValues[$key] = $dateValue;
							$isFirstDate = false;
						}
						$fieldValue = implode(',', $dateValues);
					}
					$groupColumnsInfo[] = ['field_name' => $fieldName, 'module_name' => $moduleName, 'source_field_name' => $sourceFieldName, 'comparator' => $operator, 'value' => $fieldValue];
				}
			}
			$advFilterConditionFormat[$glueOrder[$groupIterator]] = $groupColumnsInfo;
			++$groupIterator;
		}
		return $advFilterConditionFormat;
	}
}
