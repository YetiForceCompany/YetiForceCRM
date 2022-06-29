<?php

/**
 * Basic Inventory Model Class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Inventory_Model
{
	/**
	 * Field configuration table postfix.
	 */
	private const TABLE_POSTFIX_BASE = '_invfield';
	/**
	 * Data table postfix.
	 */
	private const TABLE_POSTFIX_DATA = '_inventory';
	/**
	 * Field mapping table postfix.
	 */
	private const TABLE_POSTFIX_MAP = '_invmap';

	/**
	 * @var string
	 */
	protected $moduleName;
	/**
	 * @var \Vtiger_Basic_InventoryField[] Inventory fields
	 */
	protected $fields;
	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * Gets inventory instance.
	 *
	 * @param string $moduleName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return self
	 */
	public static function getInstance(string $moduleName): self
	{
		if (\App\Cache::staticHas(__METHOD__, $moduleName)) {
			$instance = \App\Cache::staticGet(__METHOD__, $moduleName);
		} else {
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Inventory', $moduleName);
			$instance = new $modelClassName();
			$instance->setModuleName($moduleName);
			\App\Cache::staticSave(__METHOD__, $moduleName, $instance);
		}
		return $instance;
	}

	/**
	 * Function returns module name.
	 *
	 * @return string
	 */
	public function getModuleName(): string
	{
		return $this->moduleName;
	}

	/**
	 * Sets module name.
	 *
	 * @param string $name
	 */
	protected function setModuleName(string $name)
	{
		$this->moduleName = $name;
	}

	/**
	 * Gets table name.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function getTableName(string $type = self::TABLE_POSTFIX_BASE): string
	{
		if (!isset($this->tableName)) {
			$this->tableName = CRMEntity::getInstance($this->moduleName)->table_name;
		}
		return $this->tableName . $type;
	}

	/**
	 * Gets data table name.
	 *
	 * @return string
	 */
	public function getDataTableName(): string
	{
		return $this->getTableName(self::TABLE_POSTFIX_DATA);
	}

	/**
	 * Gets inventory fields.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \Vtiger_Basic_InventoryField[]
	 */
	public function getFields(): array
	{
		if (!isset($this->fields)) {
			$this->fields = [];
			$dataReader = (new \App\Db\Query())->from($this->getTableName())->indexBy('columnname')
				->orderBy(['block' => SORT_ASC, 'sequence' => SORT_ASC])->createCommand()->query();
			while ($row = $dataReader->read()) {
				$fieldModel = Vtiger_Basic_InventoryField::getInstance($this->moduleName, $row['invtype']);
				$this->setFieldData($fieldModel, $row);
				$this->fields[$row['columnname']] = $fieldModel;
			}
		}
		return $this->fields;
	}

	/**
	 * Gets inventory field model.
	 *
	 * @param string $fieldName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \Vtiger_Basic_InventoryField|null
	 */
	public function getField(string $fieldName): ?Vtiger_Basic_InventoryField
	{
		return $this->getFields()[$fieldName] ?? null;
	}

	/**
	 * Gets inventory field model by ID.
	 *
	 * @param int $fieldId
	 *
	 * @return \Vtiger_Basic_InventoryField|null
	 */
	public function getFieldById(int $fieldId): ?Vtiger_Basic_InventoryField
	{
		$fieldModel = null;
		if (\App\Cache::staticHas(__METHOD__, $fieldId)) {
			$fieldModel = \App\Cache::staticGet(__METHOD__, $fieldId);
		} else {
			$row = (new \App\Db\Query())->from($this->getTableName())->where(['id' => $fieldId])->one();
			if ($row) {
				$fieldModel = $this->getFieldCleanInstance($row['invtype']);
				$this->setFieldData($fieldModel, $row);
			}
			\App\Cache::staticSave(__METHOD__, $fieldId, $fieldModel);
		}
		return $fieldModel;
	}

	/**
	 * Function that returns all the fields by blocks.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function getFieldsByBlocks(): array
	{
		$fieldList = [];
		foreach ($this->getFields() as $fieldName => $fieldModel) {
			$fieldList[$fieldModel->get('block')][$fieldName] = $fieldModel;
		}
		return $fieldList;
	}

	/**
	 * Gets inventory fields by type.
	 *
	 * @param string $type
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \Vtiger_Basic_InventoryField[]
	 */
	public function getFieldsByType(string $type): array
	{
		$fieldList = [];
		foreach ($this->getFields() as $fieldName => $fieldModel) {
			if ($type === $fieldModel->getType()) {
				$fieldList[$fieldName] = $fieldModel;
			}
		}
		return $fieldList;
	}

	/**
	 * Gets the field for the view.
	 *
	 * @param string $view
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \Vtiger_Basic_InventoryField[]
	 */
	public function getFieldsForView(string $view): array
	{
		$fieldList = [];
		switch ($view) {
			case 'DetailPreview':
			case 'Detail':
				foreach ($this->getFields() as $fieldName => $fieldModel) {
					if ($fieldModel->isVisibleInDetail()) {
						$fieldList[$fieldModel->get('block')][$fieldName] = $fieldModel;
					}
				}
				break;
			default:
				break;
		}
		return $fieldList;
	}

	/**
	 * Getting summary fields name.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string[]
	 */
	public function getSummaryFields(): array
	{
		$summaryFields = [];
		foreach ($this->getFields() as $name => $field) {
			if ($field->isSummary()) {
				$summaryFields[$name] = $name;
			}
		}
		return $summaryFields;
	}

	/**
	 * Sets inventory field data.
	 *
	 * @param \Vtiger_Basic_InventoryField $fieldModel
	 * @param array                        $row
	 */
	public function setFieldData(Vtiger_Basic_InventoryField $fieldModel, array $row)
	{
		$fieldModel->set('id', (int) $row['id'])
			->set('columnName', $row['columnname'])
			->set('label', $row['label'])
			->set('presence', (int) $row['presence'])
			->set('defaultValue', $row['defaultvalue'])
			->set('sequence', (int) $row['sequence'])
			->set('block', (int) $row['block'])
			->set('displayType', (int) $row['displaytype'])
			->set('params', $row['params'])
			->set('colSpan', (int) $row['colspan']);
	}

	/**
	 * Checks if inventory field exists.
	 *
	 * @param string $fieldName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public function isField(string $fieldName): bool
	{
		return isset($this->getFields()[$fieldName]);
	}

	/**
	 * Gets clean inventory field instance.
	 *
	 * @param string $type
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \Vtiger_Basic_InventoryField
	 */
	public function getFieldCleanInstance(string $type): Vtiger_Basic_InventoryField
	{
		return \Vtiger_Basic_InventoryField::getInstance($this->getModuleName(), $type);
	}

	/**
	 * Function to get data of inventory for record.
	 *
	 * @param int                       $recordId
	 * @param string                    $moduleName
	 * @param \Vtiger_Paging_Model|null $pagingModel
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function getInventoryDataById(int $recordId, string $moduleName, ?Vtiger_Paging_Model $pagingModel = null): array
	{
		$inventory = self::getInstance($moduleName);
		$query = (new \App\Db\Query())->from($inventory->getTableName(self::TABLE_POSTFIX_DATA))->indexBy('id')->where(['crmid' => $recordId]);
		if ($inventory->isField('seq')) {
			$query->orderBy(['seq' => SORT_ASC]);
		}
		if ($pagingModel) {
			$pageLimit = $pagingModel->getPageLimit();
			if (0 !== $pagingModel->get('limit')) {
				$query->limit($pageLimit + 1)->offset($pagingModel->getStartIndex());
			}
			$rows = $query->all();
			$count = \count($rows);
			if ($count > $pageLimit) {
				array_pop($rows);
				$pagingModel->set('nextPageExists', true);
			} else {
				$pagingModel->set('nextPageExists', false);
			}
			$pagingModel->calculatePageRange($count);
			return $rows;
		}
		return $query->all();
	}

	/**
	 * Save inventory field.
	 *
	 * @param \Vtiger_Basic_InventoryField $fieldModel
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return bool
	 */
	public function saveField(Vtiger_Basic_InventoryField $fieldModel): bool
	{
		$db = \App\Db::getInstance();
		$tableName = $this->getTableName();
		if (!$fieldModel->has('sequence')) {
			$fieldModel->set('sequence', $db->getUniqueID($tableName, 'sequence', false));
		}
		if ($fieldModel->isEmpty('id') && !$fieldModel->isOnlyOne()) {
			$id = (new \App\Db\Query())->from($tableName)->where(['invtype' => $fieldModel->getType()])->max('id') + 1;
			$fieldModel->set('columnName', $fieldModel->getColumnName() . $id);
		}
		$transaction = $db->beginTransaction();
		try {
			$data = array_change_key_case($fieldModel->getData(), CASE_LOWER);
			if ($fieldModel->isEmpty('id')) {
				$table = $this->getTableName(self::TABLE_POSTFIX_DATA);
				vtlib\Utils::addColumn($table, $fieldModel->getColumnName(), $fieldModel->getDBType());
				foreach ($fieldModel->getCustomColumn() as $column => $criteria) {
					vtlib\Utils::addColumn($table, $column, $criteria);
				}
				$result = $db->createCommand()->insert($tableName, $data)->execute();
				$fieldModel->set('id', $db->getLastInsertID("{$tableName}_id_seq"));
			} else {
				$result = $db->createCommand()->update($tableName, $data, ['id' => $fieldModel->get('id')])->execute();
			}
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
			$result = false;
		}

		return (bool) $result;
	}

	/**
	 * Delete inventory field.
	 *
	 * @param string $fieldName
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return bool
	 */
	public function deleteField(string $fieldName): bool
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$transaction = $db->beginTransaction();
		$result = false;
		try {
			$fieldModel = $this->getField($fieldName);
			$columnsArray = array_keys($fieldModel->getCustomColumn());
			$columnsArray[] = $fieldName;
			if (isset($fieldModel->shared)) {
				foreach ($fieldModel->shared as $column => $columnShared) {
					if ($this->isField($columnShared) && false !== ($key = array_search($column, $columnsArray))) {
						unset($columnsArray[$key]);
					}
				}
			}
			$dbCommand->delete($this->getTableName(), ['columnname' => $fieldName])->execute();
			if ('seq' !== $fieldName) {
				foreach ($columnsArray as $column) {
					$dbCommand->dropColumn($this->getTableName(self::TABLE_POSTFIX_DATA), $column)->execute();
				}
			}
			$transaction->commit();
			$result = true;
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
		}
		return $result;
	}

	/**
	 * Save sequence field.
	 *
	 * @param int[] $sequenceList
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function saveSequence(array $sequenceList): int
	{
		$db = \App\Db::getInstance();
		$case = 'CASE id';
		foreach ($sequenceList as $sequence => $id) {
			$case .= " WHEN {$db->quoteValue($id)} THEN {$db->quoteValue($sequence)}";
		}
		$case .= ' END ';
		return $db->createCommand()->update($this->getTableName(), ['sequence' => new \yii\db\Expression($case)], ['id' => $sequenceList])->execute();
	}

	/**
	 * Retrieve list of all fields.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \Vtiger_Basic_InventoryField[] Fields instance
	 */
	public function getFieldsTypes(): array
	{
		$moduleName = $this->getModuleName();
		if (\App\Cache::has(__METHOD__, $moduleName)) {
			$inventoryTypes = \App\Cache::get(__METHOD__, $moduleName);
		} else {
			$fieldPaths = ["modules/$moduleName/inventoryfields/"];
			if ('Vtiger' !== $moduleName) {
				$fieldPaths[] = 'modules/Vtiger/inventoryfields/';
			}
			$inventoryTypes = [];
			foreach ($fieldPaths as $fieldPath) {
				if (!is_dir($fieldPath)) {
					continue;
				}
				foreach (new DirectoryIterator($fieldPath) as $object) {
					if ('php' === $object->getExtension() && 'Basic' !== ($type = $object->getBasename('.php')) && !isset($inventoryTypes[$type])) {
						$inventoryTypes[$type] = Vtiger_Basic_InventoryField::getInstance($moduleName, $type);
					}
				}
			}
			\App\Cache::save(__METHOD__, $moduleName, $inventoryTypes);
		}
		return $inventoryTypes;
	}

	/**
	 * Gets all columns.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return astring[]
	 */
	public function getAllColumns()
	{
		$columns = [];
		foreach ($this->getFields() as $field) {
			$columns[] = $field->getColumnName();
			foreach ($field->getCustomColumn() as $name => $field) {
				$columns[] = $name;
			}
		}
		return $columns;
	}

	/**
	 * Function return autocomplete fields.
	 *
	 * @return array
	 */
	public function getAutoCompleteFields()
	{
		$moduleName = $this->getModuleName();
		if (\App\Cache::has(__METHOD__, $moduleName)) {
			$fields = \App\Cache::get(__METHOD__, $moduleName);
		} else {
			$fields = [];
			$dataReader = (new \App\Db\Query())->from($this->getTableName(self::TABLE_POSTFIX_MAP))->createCommand()->query();
			while ($row = $dataReader->read()) {
				$fields[$row['module']][$row['tofield']] = $row;
			}
			App\Cache::save(__METHOD__, $moduleName, $fields);
		}
		return $fields;
	}

	/**
	 * Function to get custom values to complete in inventory.
	 *
	 * @param string              $sourceFieldName
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return array
	 */
	public function getCustomAutoComplete(string $sourceFieldName, Vtiger_Record_Model $recordModel)
	{
		$values = [];
		$inventoryMap = App\Config::module($this->getModuleName(), 'INVENTORY_ON_SELECT_AUTO_COMPLETE');
		if ($inventoryMap) {
			foreach ($inventoryMap as $fieldToComplete => $mapping) {
				if (isset($mapping[$sourceFieldName]) && method_exists($this, $mapping[$sourceFieldName])) {
					$methodName = $mapping[$sourceFieldName];
					$values[$fieldToComplete] = $this->{$methodName}($recordModel);
				}
			}
		}
		return $values;
	}

	/**
	 * Gets data from record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return float
	 */
	public function getInventoryPrice(Vtiger_Record_Model $recordModel)
	{
		return $recordModel->isEmpty('sum_total') ? 0 : $recordModel->get('sum_total');
	}

	/**
	 * Function to get list elements in iventory as html code.
	 *
	 * @param \Vtiger_Record_Model $recodModel
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public function getInventoryListName(Vtiger_Record_Model $recodModel)
	{
		$field = $this->getField('name');
		$html = '<ul>';
		foreach ($recodModel->getInventoryData() as $data) {
			$html .= '<li>';
			$html .= $field->getDisplayValue($data['name']);
			$html .= '</li>';
		}
		return $html . '</ul>';
	}

	/**
	 * Gets template to purify.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function getPurifyTemplate(): array
	{
		$template = [];
		foreach ($this->getFields() as $fieldModel) {
			$template += $fieldModel->getPurifyType();
		}
		return $template;
	}

	/**
	 * Get discounts configuration.
	 *
	 * @param string $key
	 *
	 * @return mixed config data
	 */
	public static function getDiscountsConfig(string $key = '')
	{
		if (\App\Cache::has('Inventory', 'DiscountConfiguration')) {
			$config = \App\Cache::get('Inventory', 'DiscountConfiguration');
		} else {
			$config = [];
			$dataReader = (new \App\Db\Query())->from('a_#__discounts_config')->createCommand(\App\Db::getInstance('admin'))->query();
			while ($row = $dataReader->read()) {
				$value = $row['value'];
				if (\in_array($row['param'], ['discounts'])) {
					$value = explode(',', $value);
				}
				$config[$row['param']] = $value;
			}
			\App\Cache::save('Inventory', 'DiscountConfiguration', $config, \App\Cache::LONG);
		}
		return $key ? $config[$key] : $config;
	}

	/**
	 * Get global discounts list.
	 *
	 * @return array discounts list
	 */
	public function getGlobalDiscounts()
	{
		if (\App\Cache::has('Inventory', 'Discounts')) {
			return \App\Cache::get('Inventory', 'Discounts');
		}
		$discounts = (new App\Db\Query())->from('a_#__discounts_global')->where(['status' => 0])
			->createCommand(App\Db::getInstance('admin'))->queryAllByGroup(1);
		\App\Cache::save('Inventory', 'Discounts', $discounts, \App\Cache::LONG);
		return $discounts;
	}

	/**
	 * Get tax configuration.
	 *
	 * @return array config data
	 */
	public static function getTaxesConfig()
	{
		if (\App\Cache::has('Inventory', 'TaxConfiguration')) {
			return \App\Cache::get('Inventory', 'TaxConfiguration');
		}
		$config = [];
		$dataReader = (new App\Db\Query())->from('a_#__taxes_config')->createCommand(App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$value = $row['value'];
			if (\in_array($row['param'], ['taxs'])) {
				$value = explode(',', $value);
			}
			$config[$row['param']] = $value;
		}
		\App\Cache::save('Inventory', 'TaxConfiguration', $config, \App\Cache::LONG);
		return $config;
	}

	/**
	 * Get global tax list.
	 *
	 * @return array tax list
	 */
	public static function getGlobalTaxes()
	{
		if (\App\Cache::has('Inventory', 'Taxes')) {
			return \App\Cache::get('Inventory', 'Taxes');
		}
		$taxes = (new App\Db\Query())->from('a_#__taxes_global')->where(['status' => 0])
			->createCommand(App\Db::getInstance('admin'))->queryAllByGroup(1);
		\App\Cache::save('Inventory', 'Taxes', $taxes, \App\Cache::LONG);
		return $taxes;
	}

	/**
	 * Get default global tax .
	 *
	 * @return array tax list
	 */
	public static function getDefaultGlobalTax()
	{
		if (\App\Cache::has('Inventory', 'DefaultTax')) {
			return \App\Cache::get('Inventory', 'DefaultTax');
		}
		$defaultTax = (new App\Db\Query())->from('a_#__taxes_global')->where(['status' => 0])->andWhere(['default' => 1])
			->one();
		\App\Cache::save('Inventory', 'DefaultTax', $defaultTax, \App\Cache::LONG);
		return $defaultTax;
	}

	/**
	 * Get discount from the account.
	 *
	 * @param string $moduleName    Module name
	 * @param int    $record        Record ID
	 * @param mixed  $relatedRecord
	 *
	 * @return array
	 */
	public function getAccountDiscount($relatedRecord)
	{
		$discount = 0;
		$discountField = 'discount';
		$recordName = '';
		if (!empty($relatedRecord)) {
			$accountRecordModel = Vtiger_Record_Model::getInstanceById($relatedRecord);
			$discount = $accountRecordModel->get($discountField);
			$recordName = $accountRecordModel->getName();
		}
		return ['discount' => $discount, 'name' => $recordName];
	}

	/**
	 * Get tax from the account.
	 *
	 * @param int $relatedRecord Record ID
	 *
	 * @return array
	 */
	public function getAccountTax($relatedRecord)
	{
		$sourceModule = 'Accounts';
		$recordName = '';
		$accountTaxes = [];
		if (!empty($relatedRecord) && \App\Record::isExists($relatedRecord, $sourceModule) && ($taxField = current(Vtiger_Module_Model::getInstance($sourceModule)->getFieldsByUiType(303))) && $taxField->isActiveField()) {
			$accountRecordModel = Vtiger_Record_Model::getInstanceById($relatedRecord, $sourceModule);
			$accountTaxes = Vtiger_Taxes_UIType::getValues($accountRecordModel->get($taxField->getName()));
			$recordName = $accountRecordModel->getName();
		}
		return ['taxes' => $accountTaxes, 'name' => $recordName];
	}

	/**
	 * Create inventory tables.
	 */
	public function createInventoryTables()
	{
		$db = \App\Db::getInstance();
		$importer = new \App\Db\Importers\Base();
		$focus = CRMEntity::getInstance($this->getModuleName());
		$dataTableName = $this->getTableName(self::TABLE_POSTFIX_DATA);
		$mapTableName = $this->getTableName(self::TABLE_POSTFIX_MAP);
		$tables = [
			$dataTableName => [
				'columns' => [
					'id' => $importer->primaryKey(10),
					'crmid' => $importer->integer(10),
					'seq' => $importer->integer(10),
				],
				'index' => [
					["{$dataTableName}_crmid_idx", 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
				'foreignKey' => [
					["{$dataTableName}_crmid_fk", $dataTableName, 'crmid', $focus->table_name, $focus->table_index, 'CASCADE', null]
				]
			],
			$this->getTableName(self::TABLE_POSTFIX_BASE) => [
				'columns' => [
					'id' => $importer->primaryKey(),
					'columnname' => $importer->stringType(30)->notNull(),
					'label' => $importer->stringType(50)->notNull(),
					'invtype' => $importer->stringType(30)->notNull(),
					'presence' => $importer->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $importer->stringType(),
					'sequence' => $importer->integer(10)->unsigned()->notNull(),
					'block' => $importer->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $importer->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $importer->text(),
					'colspan' => $importer->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			$mapTableName => [
				'columns' => [
					'module' => $importer->stringType(50)->notNull(),
					'field' => $importer->stringType(50)->notNull(),
					'tofield' => $importer->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					["{$mapTableName}_pk", ['module', 'field', 'tofield']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			]];
		$base = new \App\Db\Importer();
		$base->dieOnError = App\Config::debug('SQL_DIE_ON_ERROR');
		foreach ($tables as $tableName => $data) {
			if (!$db->isTableExists($tableName)) {
				$importer->tables = [$tableName => $data];
				$base->addTables($importer);
				if (isset($data['foreignKey'])) {
					$importer->foreignKey = $data['foreignKey'];
					$base->addForeignKey($importer);
				}
			}
		}
	}

	/**
	 * Load row data by record Id.
	 *
	 * @param int   $recordId
	 * @param array $params
	 *
	 * @return array
	 */
	public function loadRowData(int $recordId, array $params = []): array
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$recordModuleName = $recordModel->getModuleName();
		$data = [
			'name' => $recordId,
		];
		if (!$recordModel->isEmpty('description')) {
			$data['comment1'] = $recordModel->get('description');
		}
		if (\in_array($recordModuleName, ['Products', 'Services'])) {
			$currencyId = $params['currency'] ?? \App\Fields\Currency::getDefault()['id'];
			if (($fieldModel = $recordModel->getField('unit_price')) && $fieldModel->isActiveField()) {
				$data['price'] = $fieldModel->getUITypeModel()->getValueForCurrency($recordModel->get($fieldModel->getName()), $currencyId);
			}
			if (($fieldModel = $recordModel->getField('purchase')) && $fieldModel->isActiveField()) {
				$data['purchase'] = $fieldModel->getUITypeModel()->getValueForCurrency($recordModel->get($fieldModel->getName()), $currencyId);
			}
		}
		if ($autoCompleteField = ($this->getAutoCompleteFields()[$recordModuleName] ?? [])) {
			foreach ($autoCompleteField as $field) {
				$fieldModel = $recordModel->getField($field['field']);
				if ($fieldModel && ($fieldValue = $recordModel->get($field['field']))) {
					$data[$field['tofield']] = $fieldValue;
				}
			}
		}
		return $data;
	}
}
