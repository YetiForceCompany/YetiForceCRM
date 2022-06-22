<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */
Vtiger_Loader::includeOnce('~~modules/PickList/DependentPickListUtils.php');

class Settings_PickListDependency_Record_Model extends Settings_Vtiger_Record_Model
{
	private $nonMappedSourcePickListValues = false;

	/** @var \Vtiger_Module_Model Source module model */
	private $sourceModuleModel;

	/**
	 * Function to get the Id.
	 *
	 * @return int|null Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to set the id of the record.
	 *
	 * @param int $value - id value
	 */
	public function setId($value)
	{
		return $this->set('id', (int) $value);
	}

	public function getName()
	{
		return '';
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_PickListDependency_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Set module Instance.
	 *
	 * @param Settings_PickListDependency_Module_Model $moduleModel
	 *
	 * @return $this
	 */
	public function setModule($moduleModel)
	{
		$this->module = $moduleModel;
		return $this;
	}

	/**
	 * Get source module model.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getSourceModule(): Vtiger_Module_Model
	{
		if (!$this->sourceModuleModel) {
			$this->sourceModuleModel = \Vtiger_Module_Model::getInstance($this->get('tabid'));
		}

		return $this->sourceModuleModel;
	}

	/**
	 * Get source module name.
	 *
	 * @return string
	 */
	public function getSourceModuleName(): string
	{
		return $this->getSourceModule()->getName();
	}

	/**
	 * Get edit view URL.
	 *
	 * @param int $recordId
	 *
	 * @return string
	 */
	public function getEditRecordUrl(int $recordId): string
	{
		return 'index.php?parent=Settings&module=PickListDependency&view=Edit&record=' . $recordId;
	}

	/**
	 * Get list view URL.
	 *
	 * @return string
	 */
	public function getListViewUrl(): string
	{
		return 'index.php?parent=Settings&module=PickListDependency&view=List';
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$editLink = [
			'linkurl' => $this->getEditRecordUrl($this->getId()),
			'linklabel' => 'LBL_EDIT',
			'linkicon' => 'yfi yfi-full-editing-view',
			'linkclass' => 'btn btn-sm btn-info',
		];
		$editLinkInstance = Vtiger_Link_Model::getInstanceFromValues($editLink);

		$deleteLink = [
			'linkurl' => 'javascript:Settings_PickListDependency_List_Js.deleteById(' . $this->getId() . ')',
			'linklabel' => 'LBL_DELETE',
			'linkicon' => 'fas fa-trash-alt',
			'linkclass' => 'btn btn-sm btn-danger',
		];
		$deleteLinkInstance = Vtiger_Link_Model::getInstanceFromValues($deleteLink);

		return [$editLinkInstance, $deleteLinkInstance];
	}

	/**
	 * Get picklist dependency.
	 *
	 * @return array
	 */
	public function getPickListDependency(): array
	{
		if (empty($this->mapping)) {
			$dataReader = (new App\Db\Query())->from('s_#__picklist_dependency_data')->where(['id' => $this->getId()])->createCommand()->query();
			$this->mapping = [];
			while ($row = $dataReader->read()) {
				['source_id' => $sourceId,'conditions' => $conditions] = $row;
				$this->mapping[$sourceId] = $conditions;
			}
			$dataReader->close();
		}

		return $this->mapping;
	}

	/**
	 * Get pickList values for field.
	 *
	 * @param string $fieldName
	 *
	 * @return array
	 */
	public function getPickListValuesByField(string $fieldName): array
	{
		$values = [];
		if ($this->get($fieldName) && $fieldModel = $this->getSourceModule()->getFieldByName($this->get($fieldName))) {
			$values = App\Fields\Picklist::getValuesName($fieldModel->getName());
		}

		return $values;
	}

	public function getNonMappedSourcePickListValues()
	{
		if (empty($this->nonMappedSourcePickListValues)) {
			$pickListValues = $this->getPickListValuesByField('source_field');
			$dependencyMapping = $this->getPickListDependency();
			foreach ($dependencyMapping as $mappingDetails) {
				unset($pickListValues[$mappingDetails['sourcevalue']]);
			}
			$this->nonMappedSourcePickListValues = $pickListValues;
		}
		return $this->nonMappedSourcePickListValues;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$this->saveToDb();
		\App\Cache::delete('Picklist::getDependencyForModule', $this->getSourceModuleName());
		$this->checkHandler();
	}

	/**
	 * Save data to the database.
	 */
	public function saveToDb()
	{
		$db = \App\Db::getInstance('admin');
		$tablesData = $this->getValuesForSave();
		$transaction = $db->beginTransaction();
		try {
			if ($tablesData) {
				$baseTable = $this->getModule()->baseTable;
				$baseTableIndex = 'id';
				foreach ($tablesData as $tableName => $tableData) {
					if (!$this->getId() && $baseTable === $tableName) {
						$db->createCommand()->insert($tableName, $tableData)->execute();
						$this->setId((int) $db->getLastInsertID("{$baseTable}_id_seq"));
					} elseif ($baseTable === $tableName) {
						$db->createCommand()->update($tableName, $tableData, [$baseTableIndex => $this->getId()])->execute();
					} else {
						$names = $tableData['names'];
						$names[] = 'id';
						foreach ($tableData['values'] as &$values) {
							$values[] = $this->getId();
						}
						$db->createCommand()->delete($tableName, ['id' => $this->getId()])->execute();
						$db->createCommand()->batchInsert($tableName, $names, $tableData['values'])->execute();
					}
				}
			}
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
			throw $ex;
		}
	}

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		if ($this->getId() && !\in_array($key, ['id']) && (\array_key_exists($key, $this->value) && $this->value[$key] != $value)) {
			$this->changes[$key] = $this->get($key);
		}
		return parent::set($key, $value);
	}

	/**
	 * Get values for save.
	 *
	 * @return array
	 */
	public function getValuesForSave(): array
	{
		$forSave = [];
		$tableName = $this->getModule()->baseTable;

		if (!$this->getId()) {
			$forSave[$tableName] = [
				'tabid' => \App\Module::getModuleId($this->get('tabid')),
				'source_field' => $this->getSourceModule()->getFieldByName($this->get('source_field'))->getId()
			];
		}
		$tableName = 's_#__picklist_dependency_data';
		$conditions = $this->get('conditions');
		if (null !== $conditions) {
			$data = [];
			foreach ($conditions as $key => $condition) {
				if ($condition && !empty(\App\Json::decode($condition)['rules'])) {
					$data[] = [$key, $condition];
				}
			}
			if ($data) {
				$names = ['source_id', 'conditions'];
				$forSave[$tableName] = ['names' => $names, 'values' => $data];
			}
		}

		return $forSave;
	}

	/**
	 * Recirsive parse data.
	 *
	 * @param array $data
	 * @param array $row
	 * @param array $global
	 *
	 * @return void
	 */
	public function recursiveMapping(array $data, array $row, array &$global)
	{
		foreach ($data as $key => $value) {
			$rowData = $row;
			$rowData[] = $key;
			if (\is_array($value)) {
				$this->recursiveMapping($value, $rowData, $global);
			} else {
				$global[] = $rowData;
			}
		}
	}

	/**
	 * Delete entry.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		$result = \App\Db::getInstance('admin')->createCommand()->delete($this->getModule()->baseTable, [
			'id' => $this->get('id')
		])->execute();
		$sourceModule = $this->get('tabid');
		\App\Cache::delete('Picklist::getDependencyForModule', $sourceModule);
		$this->checkHandler();
		return (bool) $result;
	}

	/**
	 * Get instance by id.
	 *
	 * @param int $id
	 *
	 * @return self
	 */
	public static function getInstanceById(int $id): self
	{
		$row = (new \App\Db\Query())->from('s_#__picklist_dependency')->where(['id' => $id])->one();
		$instance = false;
		if ($row) {
			$instance = self::getCleanInstance();
			$instance->setData($row);
		}
		return $instance;
	}

	/**
	 * Function to get clean instance.
	 *
	 * @return self
	 */
	public static function getCleanInstance(): self
	{
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:PickListDependency');
		$instance = new self();
		$instance->module = $moduleInstance;

		return $instance;
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
		$qualifiedModuleName = $this->getName(true);
		$tableName = $this->getModule()->baseTable;
		$labels = ['source_field' => 'LBL_SOURCE_FIELD'];
		switch ($name) {
			case 'tabid':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_SELECT_MODULE',
					'uitype' => 16,
					'typeofdata' => 'V~M',
					'maximumlength' => '50',
					'purifyType' => \App\Purifier::ALNUM,
					'picklistValues' => [],
					'table' => $tableName,
					'fieldvalue' => $this->has($name) ? $this->get($name) : ''
				];
				foreach (Settings_PickListDependency_Module_Model::getPicklistSupportedModules() as ['tabid' => $tabId, 'tablabel' => $label, 'name' => $name]) {
					$params['picklistValues'][$name] = \App\Language::translate($label, $name);
				}
				if ($this->getId()) {
					$params['isEditableReadOnly'] = true;
				}
				break;
			case 'source_field':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => $labels[$name],
					'uitype' => 16,
					'typeofdata' => 'I~M',
					'maximumlength' => '50',
					'purifyType' => \App\Purifier::ALNUM,
					'picklistValues' => [],
					'table' => $tableName,
					'fieldvalue' => $this->has($name) ? $this->get($name) : ''
				];
				$tabId = $this->get('tabid');
				if ($tabId) {
					foreach ($this->getSourceModule()->getFieldsByType('picklist') as $fieldModel) {
						if (15 === $fieldModel->getUIType() || 16 === $fieldModel->getUIType()) {
							$params['picklistValues'][$fieldModel->getName()] = $fieldModel->getFullLabelTranslation();
						}
					}
				}
				if ($this->getId()) {
					$params['isEditableReadOnly'] = true;
				}
				break;
			default:
				break;
		}

		return $params ? \Vtiger_Field_Model::init($qualifiedModuleName, $params, $name) : null;
	}

	/** {@inheritdoc} */
	public function setData($row)
	{
		$row['tabid'] = \App\Module::getModuleName($row['tabid']);
		$row['source_field'] = Vtiger_Module_Model::getInstance($row['tabid'])->getFieldsById()[$row['source_field']]->getName();
		return parent::setData($row);
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getDisplayValue(string $name)
	{
		switch ($name) {
			case 'tabid':
				return \App\Language::translate($this->get($name), $this->get($name));
			case 'source_field':
				$fieldInstance = $this->getFieldInstanceByName($name);
				return $fieldInstance->getPicklistValues()[$this->get($name)] ?? $this->get($name);
			default:
				break;
		}
		$fieldInstance = $this->getFieldInstanceByName($name);
		return $fieldInstance->getDisplayValue($this->get($name));
	}

	/**
	 * Validation duplicate.
	 *
	 * @return array
	 */
	public function validate(): array
	{
		$response = [];
		$isExists = (new App\Db\Query())->from($this->getModule()->baseTable)
			->where([
				'tabid' => \App\Module::getModuleId($this->get('tabid')),
				'source_field' => $this->getSourceModule()->getFieldByName($this->get('source_field'))->getId()])
			->andWhere(['not', [$this->getModule()->baseIndex => $this->getId()]])->exists();
		if ($isExists) {
			$response[] = [
				'result' => false,
				'message' => App\Language::translate('LBL_DUPLICATE', $this->getModule()->getName(true))
			];
		}
		return $response;
	}

	/**
	 * Check whether to activate/remove handler.
	 *
	 * @return void
	 */
	public function checkHandler(): void
	{
		$tableName = $this->getModule()->baseTable;
		$modules = (new \App\Db\Query())->select(['vtiger_tab.name'])
			->from($tableName)
			->innerJoin('vtiger_tab', "{$tableName}.tabid=vtiger_tab.tabid")
			->distinct()->column();
		if (!$modules) {
			\App\EventHandler::deleteHandler('Vtiger_PicklistDependency_Handler');
		} else {
			$type = 'EditViewChangeValue';
			$handler = (new \App\Db\Query())->from('vtiger_eventhandlers')
				->where(['handler_class' => 'Vtiger_PicklistDependency_Handler', 'event_name' => $type])
				->indexBy('event_name')->one();
			if ($handler) {
				$data = ['include_modules' => implode(',', $modules), 'is_active' => 1];
				\App\EventHandler::update($data, $handler['eventhandler_id']);
			} else {
				\App\EventHandler::registerHandler($type, 'Vtiger_PicklistDependency_Handler', implode(',', $modules), '', 5, true, 0, \App\EventHandler::SYSTEM);
			}
		}
	}
}
