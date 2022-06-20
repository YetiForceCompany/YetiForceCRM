<?php
/**
 * Query generator file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Query generator class.
 */
class QueryGenerator
{
	const STRING_TYPE = ['string', 'text', 'email', 'reference'];
	const NUMERIC_TYPE = ['integer', 'double', 'currency', 'currencyInventory'];
	const DATE_TYPE = ['date', 'datetime'];
	const EQUALITY_TYPES = ['currency', 'percentage', 'double', 'integer', 'number'];
	const COMMA_TYPES = ['picklist', 'multipicklist', 'owner', 'date', 'datetime', 'time', 'tree', 'sharedOwner', 'sharedOwner'];

	/**
	 * State records to display
	 * 0 - Active
	 * 1 - Trash
	 * 2 - Archived.
	 *
	 * @var int|null
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
	private $advFilterList;
	private $conditions;

	/** @var array Advanced conditions */
	private $advancedConditions = [];

	/** @var array Search fields for duplicates. */
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

	/** @var int|null Limit */
	private $limit;

	/** @var int|null Offset */
	private $offset;

	/** @var string|null Distinct field */
	private $distinct;

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
		$this->user = User::getUserModel($userId ?: User::getCurrentUserId());
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
	 * @return \Vtiger_Module_Model
	 */
	public function getModuleModel()
	{
		return $this->moduleModel;
	}

	/**
	 * Get query fields.
	 *
	 * @return string[]
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Get list view query fields.
	 *
	 * @return \Vtiger_Field_Model[]
	 */
	public function getListViewFields(): array
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
	 * Sets conditions from ConditionBuilder.
	 *
	 * @param array $conditions
	 *
	 * @return $this
	 */
	public function setConditions(array $conditions)
	{
		$this->conditions = $conditions;

		return $this;
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
	 * Set distinct column.
	 *
	 * @param string $columnName
	 *
	 * @return \self
	 */
	public function setDistinct($columnName)
	{
		$this->distinct = $columnName;
		return $this;
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
	 * @param string $fieldName
	 *
	 * @return \self
	 */
	public function setField(string $fieldName): self
	{
		if (false !== strpos($fieldName, ':')) {
			[$relatedFieldName, $relatedModule, $sourceField] = array_pad(explode(':', $fieldName), 3, null);
			$this->addRelatedField([
				'sourceField' => $sourceField,
				'relatedModule' => $relatedModule,
				'relatedField' => $relatedFieldName
			]);
		} else {
			$this->fields[] = $fieldName;
		}

		return $this;
	}

	/**
	 * Clear fields.
	 *
	 * @return self
	 */
	public function clearFields(): self
	{
		$this->fields = ['id'];
		$this->relatedFields = [];
		$this->customColumns = [];
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
	 * @param string|string[] $columns
	 *
	 * @return \self
	 */
	public function setCustomColumn($columns): self
	{
		if (\is_array($columns)) {
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
	 * @param string $fieldName
	 * @param string $concat
	 *
	 * @return \self
	 */
	public function setConcatColumn(string $fieldName, string $concat)
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
		return $this;
	}

	/**
	 * Returns related fields for section SELECT.
	 *
	 * @return array
	 */
	public function loadRelatedFields()
	{
		$fields = $checkIds = [];
		foreach ($this->relatedFields as $field) {
			$joinTableName = $this->getModuleField($field['sourceField'])->getTableName();
			$moduleTableIndexList = $this->entityModel->tab_name_index;
			$baseTable = $this->entityModel->table_name;
			if ($joinTableName !== $baseTable) {
				$this->addJoin(['INNER JOIN', $joinTableName, "{$baseTable}.{$moduleTableIndexList[$baseTable]} = {$joinTableName}.{$moduleTableIndexList[$joinTableName]}"]);
			}
			$relatedFieldModel = $this->addRelatedJoin($field);
			$fields["{$field['sourceField']}{$field['relatedModule']}{$relatedFieldModel->getName()}"] = "{$relatedFieldModel->getTableName()}{$field['sourceField']}.{$relatedFieldModel->getColumnName()}";
			if (!isset($checkIds[$field['sourceField']][$field['relatedModule']])) {
				$checkIds[$field['sourceField']][$field['relatedModule']] = $field['relatedModule'];
				$fields["{$field['sourceField']}{$field['relatedModule']}id"] = $relatedFieldModel->getTableName() . $field['sourceField'] . '.' . \Vtiger_CRMEntity::getInstance($field['relatedModule'])->tab_name_index[$relatedFieldModel->getTableName()];
			}
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
		if (!\in_array($field, $this->relatedFields)) {
			$this->relatedFields[] = $field;
		}
		return $this;
	}

	/**
	 * Set source record.
	 *
	 * @param int $sourceRecord
	 *
	 * @return $this
	 */
	public function setSourceRecord(int $sourceRecord)
	{
		$this->sourceRecord = $sourceRecord;
		return $this;
	}

	/**
	 * Appends a JOIN part to the query.
	 *
	 * @param array $join
	 *
	 * @return $this
	 */
	public function addJoin($join)
	{
		if (!isset($this->joins[$join[1]])) {
			$this->joins[$join[1]] = $join;
		}
		return $this;
	}

	/**
	 * Add table to query.
	 *
	 * @param string $tableName
	 */
	public function addTableToQuery($tableName)
	{
		$this->tablesList[$tableName] = $tableName;
		return $this;
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
	 * @param array|string $groups
	 *
	 * @return \self
	 */
	public function setCustomGroup($groups)
	{
		if (\is_array($groups)) {
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
	 * @param bool|int $ignoreEmptyValue
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
			if ('owner' === $fieldModel->getFieldDataType()) {
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
		return $this->getRelatedModuleFields($moduleName)[$fieldName] ?? null;
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
		if (empty($viewId) || 0 === $viewId) {
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
		if (empty($viewId) || 0 === $viewId) {
			return false;
		}
		$this->initForCustomViewById($viewId, $onlyFields);
		return $viewId;
	}

	/**
	 * Get custom view query by id.
	 *
	 * @param int|string $viewId
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
			if ('id' !== $fieldName) {
				$this->fields[] = $fieldName;
			}
		} else {
			$this->addRelatedField([
				'sourceField' => $sourceFieldName,
				'relatedModule' => $cvColumn['module_name'],
				'relatedField' => $fieldName,
			]);
		}
	}

	/**
	 * Get advanced conditions.
	 *
	 * @return array
	 */
	public function getAdvancedConditions(): array
	{
		return $this->advancedConditions;
	}

	/**
	 * Set advanced conditions.
	 *
	 * @param array $advancedConditions
	 *
	 * @return $this
	 */
	public function setAdvancedConditions(array $advancedConditions)
	{
		$this->advancedConditions = $advancedConditions;
		return $this;
	}

	/**
	 * Get custom view by id.
	 *
	 * @param mixed $viewId
	 * @param bool  $onlyFields
	 *
	 * @return $this
	 */
	public function initForCustomViewById($viewId, $onlyFields = false)
	{
		$this->fields[] = 'id';
		$customView = CustomView::getInstance($this->moduleName, $this->user);
		foreach ($customView->getColumnsListByCvid($viewId) as $cvColumn) {
			$this->addCustomViewFields($cvColumn);
		}
		foreach (CustomView::getDuplicateFields($viewId) as $fields) {
			$this->setSearchFieldsForDuplicates($fields['fieldname'], (bool) $fields['ignore']);
		}
		if ('Calendar' === $this->moduleName && !\in_array('activitytype', $this->fields)) {
			$this->fields[] = 'activitytype';
		} elseif ('Documents' === $this->moduleName && \in_array('filename', $this->fields)) {
			if (!\in_array('filelocationtype', $this->fields)) {
				$this->fields[] = 'filelocationtype';
			}
			if (!\in_array('filestatus', $this->fields)) {
				$this->fields[] = 'filestatus';
			}
		} elseif ('EmailTemplates' === $this->moduleName && !\in_array('sys_name', $this->fields)) {
			$this->fields[] = 'sys_name';
		}
		if (!$onlyFields) {
			$this->conditions = CustomView::getConditions($viewId);
		}

		return $this;
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
				[$fieldName, $moduleName, $sourceFieldName] = array_pad(explode(':', $rule['fieldname']), 3, false);
				if (!empty($sourceFieldName)) {
					$condition = $this->getRelatedCondition([
						'relatedModule' => $moduleName,
						'relatedField' => $fieldName,
						'sourceField' => $sourceFieldName,
						'value' => $rule['value'],
						'operator' => $rule['operator'],
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
	 * @param mixed $advFilterList
	 *
	 * @return $this
	 */
	public function parseAdvFilter($advFilterList = false)
	{
		if (!$advFilterList) {
			$advFilterList = $this->advFilterList;
		}
		if (!$advFilterList) {
			return $this;
		}
		foreach ($advFilterList as $group => &$filters) {
			$and = ('and' === $group || 1 === (int) $group);
			if (isset($filters['columns'])) {
				$filters = $filters['columns'];
			}
			foreach ($filters as &$filter) {
				if (isset($filter['columnname'])) {
					[$tableName, $columnName, $fieldName] = array_pad(explode(':', $filter['columnname']), 3, false);
					if (empty($fieldName) && 'crmid' === $columnName && 'vtiger_crmentity' === $tableName) {
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
							'conditionGroup' => $and,
						]);
					} elseif (0 === strpos($filter['field_name'], 'relationColumn_') && preg_match('/(^relationColumn_)(\d+)$/', $filter['field_name'], $matches)) {
						if (\in_array($matches[2], $this->advancedConditions['relationColumns'] ?? [])) {
							$this->advancedConditions['relationColumnsValues'][$matches[2]] = $filter;
						}
					} else {
						$this->addCondition($filter['field_name'], $filter['value'], $filter['comparator'], $and);
					}
				}
			}
		}
		return $this;
	}

	/**
	 * Create query.
	 *
	 * @param mixed $reBuild
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
			if (isset($this->distinct)) {
				$this->query->distinct($this->distinct);
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
		if ('id' === $fieldName) {
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
			if ('id' === $fieldName) {
				continue;
			}
			$field = $this->getModuleField($fieldName);
			if ('reference' === $field->getFieldDataType()) {
				$tableJoin[$field->getTableName()] = 'INNER JOIN';
				foreach ($this->referenceFields[$fieldName] as $moduleName) {
					if ('Users' === $moduleName && 'Users' !== $this->moduleName) {
						$this->addJoin(['LEFT JOIN', 'vtiger_users vtiger_users' . $fieldName, "{$field->getTableName()}.{$field->getColumnName()} = vtiger_users{$fieldName}.id"]);
						$this->addJoin(['LEFT JOIN', 'vtiger_groups vtiger_groups' . $fieldName, "{$field->getTableName()}.{$field->getColumnName()} = vtiger_groups{$fieldName}.groupid"]);
					}
				}
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
			if (\in_array('assigned_user_id', $this->ownerFields)) {
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
			if ('vtiger_users' === $tableName) {
				$field = $this->getModuleField($ownerField);
				$this->addJoin([$joinType, $tableName, "{$field->getTableName()}.{$field->getColumnName()} = $tableName.id"]);
			} elseif ('vtiger_groups' == $tableName) {
				$field = $this->getModuleField($ownerField);
				$this->addJoin([$joinType, $tableName, "{$field->getTableName()}.{$field->getColumnName()} = $tableName.groupid"]);
			} elseif (isset($moduleTableIndexList[$tableName])) {
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
		uksort($this->joins, fn ($a, $b) => (int) (!isset($moduleTableIndexList[$a]) && isset($moduleTableIndexList[$b])));
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
		if (null !== $this->stateCondition) {
			$this->query->andWhere($this->getStateCondition());
		}
		if ($this->advancedConditions) {
			$this->loadAdvancedConditions();
		}
		$this->query->andWhere(['and', array_merge(['and'], $this->conditionsAnd), array_merge(['or'], $this->conditionsOr)]);
		$this->query->andWhere($this->parseConditions($this->conditions));
		if ($this->permissions) {
			if (\App\Config::security('CACHING_PERMISSION_TO_RECORD') && 'Users' !== $this->moduleName) {
				$userId = $this->user->getId();
				$this->query->andWhere(['like', 'vtiger_crmentity.users', ",$userId,"]);
			} else {
				PrivilegeQuery::getConditions($this->query, $this->moduleName, $this->user, $this->sourceRecord);
			}
		}
	}

	/**
	 * Load advanced conditions to section where in query.
	 *
	 * @return void
	 */
	private function loadAdvancedConditions(): void
	{
		if (!empty($this->advancedConditions['relationId']) && ($relationModel = \Vtiger_Relation_Model::getInstanceById($this->advancedConditions['relationId']))) {
			$typeRelationModel = $relationModel->getTypeRelationModel();
			if (!method_exists($typeRelationModel, 'loadAdvancedConditionsByRelationId')) {
				$className = \get_class($typeRelationModel);
				Log::error("The relationship relationId: {$this->advancedConditions['relationId']} does not support advanced conditions | No function in the class: $className | Module: " . $this->getModule());
				throw new \App\Exceptions\AppException("ERR_FUNCTION_NOT_FOUND_IN_CLASS||loadAdvancedConditionsByRelationId|$className|" . $this->getModule());
			}
			$typeRelationModel->loadAdvancedConditionsByRelationId($this);
		}
		if (!empty($this->advancedConditions['relationColumnsValues'])) {
			foreach ($this->advancedConditions['relationColumnsValues'] as $relationId => $value) {
				if ($relationModel = \Vtiger_Relation_Model::getInstanceById($relationId)) {
					$typeRelationModel = $relationModel->getTypeRelationModel();
					if (!method_exists($typeRelationModel, 'loadAdvancedConditionsByColumns')) {
						$className = \get_class($typeRelationModel);
						Log::error("The relationship relationId: {$relationId} does not support advanced conditions | No function in the class: $className | Module: " . $this->getModule());
						throw new \App\Exceptions\AppException("ERR_FUNCTION_NOT_FOUND_IN_CLASS|loadAdvancedConditionsByColumns|$className|" . $this->getModule());
					}
					$typeRelationModel->loadAdvancedConditionsByColumns($this, $value);
				}
			}
		}
	}

	/**
	 * Get records state.
	 *
	 * @return string
	 */
	public function getState(): string
	{
		if (null === $this->stateCondition) {
			return 'All';
		}
		switch ($this->stateCondition) {
			default:
			case 0:
				$stateCondition = 'Active';
				break;
			case 1:
				$stateCondition = 'Trash';
				break;
			case 2:
				$stateCondition = 'Archived';
				break;
		}
		return $stateCondition;
	}

	/**
	 * Get conditions for records state.
	 *
	 * @return array|string
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
	 *
	 * @return $this
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
				$this->stateCondition = null;
				break;
		}
		return $this;
	}

	/**
	 * Returns condition for field in this module.
	 *
	 * @param string $fieldName
	 * @param mixed  $value
	 * @param string $operator
	 * @param bool   $userFormat
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array|bool
	 */
	private function getCondition(string $fieldName, $value, string $operator, bool $userFormat = false)
	{
		$queryField = $this->getQueryField($fieldName);
		if ($userFormat && $queryField->getField()) {
			$value = $queryField->getField()->getUITypeModel()->getDbConditionBuilderValue($value, $operator);
		}
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
		$queryField = $this->getQueryRelatedField($condition, $field);
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
	 * @param mixed  $groupAnd
	 * @param bool   $userFormat
	 *
	 * @see Condition::ADVANCED_FILTER_OPTIONS
	 * @see Condition::DATE_OPERATORS
	 *
	 * @return $this
	 */
	public function addCondition($fieldName, $value, $operator, $groupAnd = true, $userFormat = false)
	{
		$condition = $this->getCondition($fieldName, $value, $operator, $userFormat);
		if ($condition) {
			if ($groupAnd) {
				$this->conditionsAnd[] = $condition;
			} else {
				$this->conditionsOr[] = $condition;
			}
		} else {
			Log::error('Wrong condition');
		}
		return $this;
	}

	/**
	 * Get query field instance.
	 *
	 * @param string $fieldName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\Conditions\QueryFields\BaseField
	 */
	public function getQueryField($fieldName)
	{
		if (isset($this->queryFields[$fieldName])) {
			return $this->queryFields[$fieldName];
		}
		if ('id' === $fieldName) {
			$queryField = new Conditions\QueryFields\IdField($this, '');
			return $this->queryFields[$fieldName] = $queryField;
		}
		$field = $this->getModuleField($fieldName);
		if (empty($field)) {
			Log::error("Not found field model | Field name: '$fieldName' in module" . $this->getModule());
			throw new \App\Exceptions\AppException("ERR_NOT_FOUND_FIELD_MODEL|$fieldName|" . $this->getModule());
		}
		$className = '\App\Conditions\QueryFields\\' . ucfirst($field->getFieldDataType()) . 'Field';
		if (!class_exists($className)) {
			Log::error('Not found query field condition | FieldDataType: ' . ucfirst($field->getFieldDataType()));
			throw new \App\Exceptions\AppException('ERR_NOT_FOUND_QUERY_FIELD_CONDITION|' . $fieldName);
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
	 * @return bool|\Vtiger_Field_Model
	 */
	public function addRelatedJoin($fieldDetail)
	{
		$relatedFieldModel = $this->getRelatedModuleField($fieldDetail['relatedField'], $fieldDetail['relatedModule']);
		if (!$relatedFieldModel || !$relatedFieldModel->isActiveField()) {
			Log::warning("Field in related module is inactive or does not exist. Related module: {$fieldDetail['relatedModule']} | Related field: {$fieldDetail['relatedField']}");
			return false;
		}
		$tableName = $relatedFieldModel->getTableName();
		$sourceFieldModel = $this->getModuleField($fieldDetail['sourceField']);
		$relatedTableName = $tableName . $fieldDetail['sourceField'];
		$relatedTableIndex = $relatedFieldModel->getModule()->getEntityInstance()->tab_name_index[$tableName];
		$this->addJoin(['LEFT JOIN', "$tableName $relatedTableName", "{$sourceFieldModel->getTableName()}.{$sourceFieldModel->getColumnName()} = $relatedTableName.$relatedTableIndex"]);
		return $relatedFieldModel;
	}

	/**
	 * Get query related field instance.
	 *
	 * @param array|string        $relatedInfo
	 * @param \Vtiger_Field_Model $field
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\Conditions\QueryFields\BaseField
	 */
	public function getQueryRelatedField($relatedInfo, ?\Vtiger_Field_Model $field = null)
	{
		if (!\is_array($relatedInfo)) {
			[$fieldName, $relatedModule, $sourceFieldName] = array_pad(explode(':', $relatedInfo), 3, false);
			$relatedInfo = [
				'sourceField' => $sourceFieldName,
				'relatedModule' => $relatedModule,
				'relatedField' => $fieldName,
			];
		}
		$relatedModule = $relatedInfo['relatedModule'];
		$fieldName = $relatedInfo['relatedField'];

		if (isset($this->relatedQueryFields[$relatedModule][$fieldName])) {
			$queryField = clone $this->relatedQueryFields[$relatedModule][$fieldName];
			$queryField->setRelated($relatedInfo);
			return $queryField;
		}
		if (null === $field) {
			$field = $this->getRelatedModuleField($fieldName, $relatedModule);
		}
		$className = '\App\Conditions\QueryFields\\' . ucfirst($field->getFieldDataType()) . 'Field';
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
	 */
	public function setRelatedOrder(array $orderDetail)
	{
		$field = $this->addRelatedJoin($orderDetail);
		if (!$field) {
			Log::error('Not found source field');
		}
		$queryField = $this->getQueryRelatedField($orderDetail, $field);
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
			if (!empty($groupInfo)) {
				$groupColumnsInfo = [];
				foreach ($groupInfo as $fieldSearchInfo) {
					if ($fieldSearchInfo) {
						[$fieldNameInfo, $operator, $fieldValue] = array_pad($fieldSearchInfo, 3, false);
						$fieldValue = Purifier::decodeHtml($fieldValue);
						[$fieldName, $moduleName, $sourceFieldName] = array_pad(explode(':', $fieldNameInfo), 3, false);
						if (!empty($sourceFieldName)) {
							$field = $this->getRelatedModuleField($fieldName, $moduleName);
						} else {
							$field = $this->getModuleField($fieldName);
						}
						if ($field && ('date_start' === $fieldName || 'due_date' === $fieldName || 'datetime' === $field->getFieldDataType())) {
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
			}
			++$groupIterator;
		}
		return $advFilterConditionFormat;
	}

	/**
	 * Parse search condition to standard condition.
	 *
	 * @param array $searchParams
	 *
	 * @return array
	 */
	public function parseSearchParams(array $searchParams): array
	{
		$glueOrder = ['AND', 'OR'];
		$searchParamsConditions = [];
		foreach ($searchParams as $key => $conditions) {
			if (empty($conditions)) {
				continue;
			}
			$searchParamsConditions['condition'] = $glueOrder[$key];
			$searchParamsConditions['rules'] = [];
			foreach ($conditions as $condition) {
				[$fieldName, , $sourceFieldName] = array_pad(explode(':', $condition[0]), 3, false);
				if (!$sourceFieldName) {
					$condition[0] = "{$fieldName}:{$this->getModule()}";
				}
				$searchParamsConditions['rules'][] = ['fieldname' => $condition[0], 'operator' => $condition[1], 'value' => $condition[2]];
			}
		}
		return $searchParamsConditions;
	}
}
