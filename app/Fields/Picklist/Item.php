<?php
/**
 * Picklist value item.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Fields\Picklist;

/**
 * Picklist value item class.
 */
class Item extends \App\Base
{
	/** @var int Item ID */
	protected $id;
	/** @var string Item name */
	protected $name;
	/** @var int Permission level */
	protected $presence = 1;
	/** @var string Description */
	protected $description;
	/** @var string Prefix for numbering */
	protected $prefix;
	/** @var string Icon */
	protected $icon;
	/** @var string Color */
	protected $color;
	/** @var int Sort ID */
	protected $sortorderid;
	/** @var int Item ID for role */
	protected $valueid;
	/** @var int Record state */
	protected $record_state;
	/** @var int Time counting */
	protected $time_counting;
	/** @var int State for the record */
	protected $close_state;
	/** @var \Settings_Picklist_Field_Model */
	protected $fieldModel;
	/** @var array Changes */
	protected $changes = [];
	/** @var string[] Role IDs */
	protected $roles;

	/**
	 * Get instance.
	 *
	 * @param \Vtiger_Field_Model $fieldModel
	 * @param int|null            $id
	 *
	 * @return self
	 */
	public static function getInstance(\Vtiger_Field_Model $fieldModel, ?int $id): self
	{
		$instance = new self();
		$instance->fieldModel = $fieldModel;
		if ($id) {
			$data = \App\Fields\Picklist::getValues($fieldModel->getName())[$id];
			$instance->id = $id;
			$instance->name = $data['picklistValue'];
			$instance->presence = (int) $data['presence'];
			$instance->description = $data['description'] ?? null;
			$instance->prefix = $data['prefix'] ?? null;
			$instance->icon = $data['icon'] ?? null;
			$instance->color = $data['color'] ?? null;
			$instance->sortorderid = (int) $data['sortorderid'];
			$instance->valueid = isset($data['picklist_valueid']) ? (int) $data['picklist_valueid'] : null;
			$instance->record_state = isset($data['record_state']) ? (int) $data['record_state'] : null;
			$instance->time_counting = isset($data['time_counting']) ? (int) $data['time_counting'] : null;
			$instance->close_state = (int) (new \App\Db\Query())->from('u_#__picklist_close_state')->where(['valueid' => $instance->valueid, 'fieldid' => $fieldModel->getId()])->exists();
		}

		return $instance;
	}

	/**
	 * Function to get the Id.
	 *
	 * @return int
	 */
	public function getId(): int
	{
		return (int) $this->id;
	}

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		$propertyExists = \property_exists($this, $key);
		if ($this->getId() && !\in_array($key, ['id']) && ($propertyExists && $this->{$key} !== $value && (null !== $this->{$key} || '' !== $value)) && !\array_key_exists($key, $this->changes)) {
			$this->changes[$key] = $this->get($key);
		}
		return $propertyExists ? $this->{$key} = $value : parent::set($key, $value);
	}

	/** {@inheritdoc} */
	public function get($key)
	{
		return \property_exists($this, $key) ? $this->{$key} : parent::get($key);
	}

	/**
	 * Gets field datatypes.
	 *
	 * @return array
	 */
	public function getDbTypes(): array
	{
		return [
			'description' => \yii\db\Schema::TYPE_TEXT,
			'prefix' => [\yii\db\Schema::TYPE_STRING, 30],
			'color' => [\yii\db\Schema::TYPE_STRING, 25],
			'icon' => [\yii\db\Schema::TYPE_STRING, 255]
		];
	}

	/**
	 * Save.
	 *
	 * @return bool
	 */
	public function save(): bool
	{
		try {
			$this->validate();
			$result = $this->saveToDb();
			if ($this->getPreviousValue('name')) {
				$this->rename();
			}
		} catch (\Throwable $e) {
			\App\Log::error($e->__toString());
			throw $e;
		}
		\App\Fields\Picklist::clearCache($this->fieldModel->getName(), $this->fieldModel->getModuleName());

		return $result;
	}

	/**
	 * Save data to database.
	 *
	 * @return bool
	 */
	public function saveToDb(): bool
	{
		$db = \App\Db::getInstance();
		$result = false;
		$fieldName = $this->fieldModel->getName();
		$primaryKey = \App\Fields\Picklist::getPickListId($fieldName);
		$baseTable = $this->getTableName();

		$dataForSave = $this->getValuesToSave();
		foreach ($this->getDbTypes() as $column => $type) {
			if (isset($dataForSave[$baseTable][$column])) {
				$length = null;
				if (\is_array($type)) {
					[$type, $length] = $type;
				}
				$criteria = $db->getSchema()->createColumnSchemaBuilder($type, $length)->defaultValue('');
				\vtlib\Utils::addColumn($baseTable, $column, $criteria);
			}
		}

		$transaction = $db->beginTransaction();
		try {
			foreach ($dataForSave as $tableName => $tableData) {
				if (!$this->getId() && $baseTable === $tableName) {
					if ($this->fieldModel->isRoleBased()) {
						$vId = $db->getUniqueID('vtiger_picklistvalues');
						$tableData['picklist_valueid'] = $vId;
						$this->set('valueid', $vId);
					}
					$result = $db->createCommand()->insert($tableName, $tableData)->execute();
					$this->id = $db->getLastInsertID($tableName . '_' . $primaryKey . '_seq');
				} elseif ($baseTable === $tableName) {
					$db->createCommand()->update($tableName, $tableData, [$primaryKey => $this->getId()])->execute();
				} elseif ('u_#__picklist_close_state' === $tableName) {
					$db->createCommand()->delete($tableName, ['fieldid' => $this->fieldModel->getId(), 'valueid' => $this->valueid])->execute();
					if ($this->close_state) {
						$db->createCommand()->insert($tableName, ['fieldid' => $this->fieldModel->getId(), 'valueid' => $this->valueid, 'value' => $this->name])->execute();
					}
				}
			}
			$this->updateRolePermissions();

			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
			throw $ex;
		}
		return (bool) $result;
	}

	/**
	 * Update role permissions.
	 *
	 * @return void
	 */
	public function updateRolePermissions()
	{
		if ($this->valueid && null !== $this->roles) {
			$dbCommand = \App\Db::getInstance()->createCommand();
			$dbCommand->delete('vtiger_role2picklist', ['picklistvalueid' => $this->valueid])->execute();
			if ($this->roles) {
				$picklistId = \App\Fields\Picklist::getPicklistIdNr($this->fieldModel->getName());
				if ($insertValueList = array_map(fn ($roleid) => [$roleid, $this->valueid, $picklistId], $this->roles)) {
					$dbCommand->batchInsert('vtiger_role2picklist', ['roleid', 'picklistvalueid', 'picklistid'], $insertValueList)->execute();
				}
			}
		}
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public function getTableName(): string
	{
		return \App\Fields\Picklist::getPickListTableName($this->fieldModel->getName());
	}

	/**
	 * Get next sequence number.
	 *
	 * @return int
	 */
	public function getNextSeq(): int
	{
		return (int) (new \App\Db\Query())->from($this->getTableName())->max('sortorderid') + 1;
	}

	/**
	 * Get writable fields.
	 *
	 * @return array
	 */
	public function getWritableFields(): array
	{
		return ['name', 'presence', 'sortorderid', 'presence', 'description', 'prefix', 'icon', 'record_state', 'time_counting', 'close_state'];
	}

	/**
	 * Get field model.
	 *
	 * @return \Settings_Picklist_Field_Model
	 */
	public function getFieldModel(): \Settings_Picklist_Field_Model
	{
		return $this->fieldModel;
	}

	/**
	 * Check if item is deletable.
	 *
	 * @return bool
	 */
	public function isDeletable(): bool
	{
		return 1 === $this->presence && $this->fieldModel->isEditable();
	}

	/**
	 * Rename item value in other data.
	 *
	 * @return void
	 */
	public function rename()
	{
		$newValue = $this->name;
		$previousValue = $this->getPreviousValue('name');
		$fieldName = $this->fieldModel->getName();

		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = (new \App\Db\Query())->select(['tablename', 'columnname', 'fieldid', 'tabid'])
			->from('vtiger_field')
			->where(['fieldname' => $fieldName, 'uitype' => [15, 16, 33]])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			$dbCommand->update($tableName, [$columnName => $newValue], [$columnName => $previousValue])
				->execute();
			$dbCommand->update('vtiger_field', ['defaultvalue' => $newValue], ['defaultvalue' => $previousValue, 'fieldid' => $row['fieldid']])
				->execute();
			$moduleName = \App\Module::getModuleName($row['tabid']);

			\App\Fields\Picklist::clearCache($fieldName, $moduleName);
			$eventHandler = new \App\EventHandler();
			$eventHandler->setParams([
				'fieldname' => $fieldName,
				'oldvalue' => $previousValue,
				'newvalue' => $newValue,
				'module' => $moduleName,
				'id' => $this->getId(),
			]);
			$eventHandler->trigger('PicklistAfterRename');
		}
		\App\Fields\Picklist::clearCache($fieldName, $this->fieldModel->getModuleName());
	}

	/**
	 * Delete item.
	 *
	 * @param int $replaceId Item ID
	 *
	 * @return void
	 */
	public function delete(int $replaceId)
	{
		$db = \App\Db::getInstance();
		$fieldName = $this->fieldModel->getName();
		$transaction = $db->beginTransaction();
		try {
			$dbCommand = $db->createCommand();

			$primaryKey = \App\Fields\Picklist::getPickListId($this->fieldModel->getName());
			$replaceValue = \App\Purifier::decodeHtml((new \App\Db\Query())->select([$this->fieldModel->getName()])
				->from($this->getTableName())
				->where([$primaryKey => $replaceId])
				->scalar());

			if ($this->fieldModel->isRoleBased()) {
				$dbCommand->delete('vtiger_role2picklist', ['picklistvalueid' => $this->valueid])->execute();
				$dbCommand->delete('u_#__picklist_close_state', ['valueid' => $this->valueid])->execute();
			}
			$dbCommand->delete($this->getTableName(), [$primaryKey => $this->getId()])->execute();
			$dependencyId = (new \App\Db\Query())->select(['s_#__picklist_dependency.id'])->from('s_#__picklist_dependency')->innerJoin('s_#__picklist_dependency_data', 's_#__picklist_dependency_data.id = s_#__picklist_dependency.id')
				->where(['source_field' => $this->fieldModel->getId(), 'source_id' => $this->getId()])->column();
			if ($dependencyId) {
				$dbCommand->delete('s_#__picklist_dependency_data', ['id' => $dependencyId, 'source_id' => $this->getId()])->execute();
			}

			$dataReader = (new \App\Db\Query())->select(['tablename', 'columnname', 'fieldid', 'tabid'])
				->from('vtiger_field')
				->where(['fieldname' => $fieldName, 'uitype' => [15, 16, 33]])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				$moduleName = \App\Module::getModuleName($row['tabid']);
				$tableName = $row['tablename'];
				$columnName = $row['columnname'];
				$dbCommand->update($tableName, [$columnName => $replaceValue], [$columnName => $this->name])
					->execute();
				$dbCommand->update('vtiger_field', ['defaultvalue' => $replaceValue], ['defaultvalue' => $this->name, 'fieldid' => $row['fieldid']])
					->execute();

				\App\Fields\Picklist::clearCache($fieldName, $moduleName);
				$eventHandler = new \App\EventHandler();
				$eventHandler->setParams([
					'fieldname' => $fieldName,
					'valuetodelete' => [$this->name],
					'replacevalue' => $replaceValue,
					'module' => $moduleName,
				]);
				$eventHandler->trigger('PicklistAfterDelete');
			}
			$dataReader->close();

			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
			throw $ex;
		}

		\App\Fields\Picklist::clearCache($fieldName, $this->fieldModel->getModuleName());
	}

	/**
	 * Function formats data for saving.
	 *
	 * @return array
	 */
	private function getValuesToSave(): array
	{
		$forSave = [];
		$tableName = $this->getTableName();
		if (!$this->getId()) {
			$forSave[$this->getTableName()] = [
				'sortorderid' => $this->getNextSeq(),
				'presence' => $this->presence,
			];
		}
		$fields = $this->getId() ? array_keys($this->changes) : $this->getWritableFields();
		foreach ($fields as $name) {
			$itemPropertyModel = $this->getFieldInstanceByName($name);
			if ($itemPropertyModel && isset($this->{$name}) && ($this->getId() || (!$this->getId() && '' !== $this->{$name}))) {
				$this->validateValue($name, $this->{$name});
				$forSave[$itemPropertyModel->getTableName()][$itemPropertyModel->getColumnName()] = $this->{$name};
			} elseif (isset($this->{$name}) && ($this->getId() || (!$this->getId() && '' !== $this->{$name}))) {
				$this->validateValue($name, $this->{$name});
				$forSave[$tableName][$name] = $this->{$name};
			}
		}

		return $forSave;
	}

	/**
	 * Get pervious value by field.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getPreviousValue(?string $fieldName = '')
	{
		return $fieldName ? ($this->changes[$fieldName] ?? null) : $this->changes;
	}

	/** {@inheritdoc} */
	public function getData()
	{
		$data = [];
		foreach (get_object_vars($this) as $name => $value) {
			if (\is_object($value) || 'value' === $name || 'changes' === $name || null === $value) {
				continue;
			}
			if (\is_array($value)) {
				$value = implode(',', $value);
			}
			$data[$name] = $value;
		}

		return $data;
	}

	/**
	 * Get fields for edit.
	 *
	 * @return array
	 */
	public function getEditFields(): array
	{
		$fields = [];
		$editFields = ['name'];
		$editFields[] = 'icon';
		if ($this->fieldModel->getModule()->isEntityModule()) {
			if (!$this->getId()) {
				$editFields[] = 'roles';
			}
			$editFields[] = 'description';
			$editFields[] = 'prefix';
			if ($this->fieldModel->getFieldParams()['isProcessStatusField'] ?? false) {
				$editFields[] = 'time_counting';
				$editFields[] = 'record_state';
			}
			if (15 === $this->fieldModel->getUIType()) {
				$editFields[] = 'close_state';
			}
		}

		foreach ($editFields as $fieldName) {
			$propertyModel = $this->getFieldInstanceByName($fieldName);
			if (null !== $this->get($fieldName)) {
				$propertyModel->set('fieldvalue', $this->get($fieldName));
			} elseif (($defaultValue = $propertyModel->get('defaultvalue')) !== null) {
				$propertyModel->set('fieldvalue', $defaultValue);
			}
			$fields[$fieldName] = $propertyModel;
		}

		return $fields;
	}

	/**
	 * Basic validation.
	 *
	 * @return array
	 */
	public function validate(): array
	{
		$response = [];
		if ($this->isDuplicateValue()) {
			$response[] = [
				'result' => false,
				'message' => \App\Language::translate('LBL_DUPLICATE', 'Settings:Picklist')
			];
		}
		return $response;
	}

	/**
	 * Check if picklist value exists.
	 *
	 * @return bool
	 */
	public function isDuplicateValue(): bool
	{
		$picklistValues = \App\Fields\Picklist::getValuesName($this->fieldModel->getName());
		if ($this->id) {
			unset($picklistValues[$this->id]);
		}

		return \in_array(strtolower($this->name), array_map('strtolower', $picklistValues));
	}

	/**
	 * Validate item data.
	 *
	 * @param string $fieldName
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function validateValue(string $fieldName, $value)
	{
		switch ($fieldName) {
			case 'name':
				$itemPropertyModel = $this->getFieldInstanceByName($fieldName);
				$itemPropertyModel->getUITypeModel()->validate($value, false);
				if (empty($value)) {
					throw new \App\Exceptions\IllegalValue("LBL_NOT_FILLED_MANDATORY_FIELDS||{$fieldName}", 512);
				}
				if (preg_match('/[\<\>\"\#]/', $value)) {
					throw new \App\Exceptions\IllegalValue("ERR_SPECIAL_CHARACTERS_NOT_ALLOWED||{$fieldName}||{$value}", 512);
				}
				if ($itemPropertyModel->getMaxValue() && \strlen($value) > $itemPropertyModel->getMaxValue()) {
					throw new \App\Exceptions\IllegalValue("ERR_EXCEEDED_NUMBER_CHARACTERS||{$fieldName}||{$value}", 406);
				}
				if ($this->isDuplicateValue($value, $this->getId())) {
					throw new \App\Exceptions\IllegalValue("ERR_DUPLICATES_VALUES_FOUND||{$fieldName}||{$value}", 513);
				}
				break;
			case 'color':
			case 'description':
			case 'prefix':
			case 'close_state':
			case 'icon':
				$itemPropertyModel = $this->getFieldInstanceByName($fieldName);
				$itemPropertyModel->getUITypeModel()->validate($value, false);
				break;
			case 'time_counting':
			case 'record_state':
			case 'sortorderid':
				if (!\App\Validator::integer($value)) {
					throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$fieldName}||{$value}", 406);
				}
				break;
			case 'presence':
				if (1 !== $value && 0 !== $value) {
					throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$fieldName}||{$value}", 406);
				}
				break;
			default:
				throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$fieldName}||{$value}", 406);
		}
	}

	/**
	 * Get fields instance by name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$params = [];
		$qualifiedModuleName = 'Settings:Picklist';
		$tableName = $this->getTableName();
		switch ($name) {
			case 'name':
				$params = [
					'name' => $name,
					'column' => $this->fieldModel->getName(),
					'label' => 'LBL_ITEM_VALUE',
					'uitype' => 1,
					'typeofdata' => 'V~M',
					'maximumlength' => $this->fieldModel->getMaxValue(),
					'purifyType' => \App\Purifier::TEXT,
					'table' => $tableName,
					'validator' => [['name' => 'FieldLabel']]
				];
				if (1 !== $this->presence || !$this->fieldModel->isEditable()) {
					$params['isEditableReadOnly'] = true;
				}
				break;
			case 'description':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_DESCRIPTION',
					'uitype' => 300,
					'typeofdata' => 'V~O',
					'maximumlength' => '65535',
					'purifyType' => \App\Purifier::HTML,
					'tooltip' => 'LBL_DESCRIPTION_VALUE_LIST',
					'table' => $tableName
				];
				break;
			case 'prefix':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_PREFIX',
					'uitype' => 1,
					'typeofdata' => 'V~O',
					'maximumlength' => '25',
					'purifyType' => \App\Purifier::TEXT,
					'tooltip' => 'LBL_DESCRIPTION_PREFIXES',
					'table' => $tableName
				];
				break;
			case 'close_state':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_CLOSES_RECORD',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '5',
					'purifyType' => \App\Purifier::BOOL,
					'tooltip' => 'LBL_BLOCKED_RECORD_INFO',
					'table' => 'u_#__picklist_close_state'
				];
				break;
			case 'icon':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_ICON',
					'uitype' => 62,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => \App\Purifier::TEXT,
					'table' => $tableName
				];
				break;
			case 'time_counting':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_TIME_COUNTING',
					'uitype' => 16,
					'typeofdata' => 'V~M',
					'maximumlength' => '250',
					'purifyType' => \App\Purifier::INTEGER,
					'tooltip' => 'LBL_TIME_COUNTING_INFO',
					'defaultvalue' => 0,
					'picklistValues' => [
						0 => \App\Language::translate('LBL_NONE', '_Base'),
						\App\RecordStatus::TIME_COUNTING_REACTION => \App\Language::translate('LBL_TIME_COUNTING_REACTION', $qualifiedModuleName),
						\App\RecordStatus::TIME_COUNTING_RESOLVE => \App\Language::translate('LBL_TIME_COUNTING_RESOLVE', $qualifiedModuleName),
						\App\RecordStatus::TIME_COUNTING_IDLE => \App\Language::translate('LBL_TIME_COUNTING_IDLE', $qualifiedModuleName)
					],
					'table' => $tableName
				];
				break;
			case 'record_state':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_RECORD_STATE',
					'uitype' => 16,
					'typeofdata' => 'V~M',
					'maximumlength' => '250',
					'purifyType' => \App\Purifier::INTEGER,
					'tooltip' => 'LBL_RECORD_STATE_INFO',
					'defaultvalue' => \App\RecordStatus::RECORD_STATE_NO_CONCERN,
					'picklistValues' => [],
					'table' => $tableName
				];
				foreach (\App\RecordStatus::getLabels() as $key => $value) {
					$params['picklistValues'][$key] = \App\Language::translate($value, $qualifiedModuleName);
				}
				break;
			case 'roles':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_ASSIGN_TO_ROLE',
					'uitype' => 33,
					'typeofdata' => 'V~O',
					'maximumlength' => '500',
					'purifyType' => \App\Purifier::TEXT,
					'defaultvalue' => 'all',
					'picklistValues' => [
						'all' => \App\Language::translate('LBL_ALL_ROLES', $qualifiedModuleName)
					],
					'table' => $tableName
				];
				foreach (\Settings_Roles_Record_Model::getAll() as $key => $roleModel) {
					$params['picklistValues'][$key] = \App\Language::translate($roleModel->get('rolename'), 'Settings:Roles');
				}
				break;
			default:
				break;
		}

		return $params ? \Vtiger_Field_Model::init($qualifiedModuleName, $params, $name)->set('sourceFieldModel', $this->fieldModel) : null;
	}
}
