<?php

namespace App;

/**
 * RecordConverter class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class RecordConverter extends Base
{
	/**
	 * Source module name.
	 *
	 * @var string
	 */
	public $sourceModule = '';
	/**
	 * Source module model.
	 *
	 * @var \Vtiger_Module_Model
	 */
	public $sourceModuleModel;
	/**
	 * Destiny module name.
	 *
	 * @var string
	 */
	public $destinyModule = '';
	/**
	 * Destiny module model.
	 *
	 * @var \Vtiger_Module_Model
	 */
	public $destinyModuleModel;
	/**
	 * Record modesl of created records.
	 *
	 * @var Vtiger_Record_Model[]
	 */
	public $cleanRecordModels = [];
	/**
	 * Source record models.
	 *
	 * @var Vtiger_Record_Model[]
	 */
	public $recordModels = [];
	/**
	 * Convert field mapping.
	 *
	 * @var array|string
	 */
	public $fieldMapping;
	/**
	 * Convert inventory mapping.
	 *
	 * @var array|string
	 */
	public $inventoryMapping;

	/**
	 * Function to get the instance of the record converter model.
	 *
	 * @return \self
	 */
	public static function getInstanceById($id, $moduleName = '')
	{
		if ($id) {
			$query = (new \App\Db\Query())->from('a_#__record_converter')->where(['id' => $id]);
		}
		if ($moduleName) {
			$query->andWhere(['source_module' => \App\Module::getModuleId($moduleName)]);
		}
		$row = $query->one(Db::getInstance('admin'));
		$self = new self();
		if ($row) {
			$self->setData($row);
		} else {
			\App\Log::error("Could not find record converter id: $id module name: $moduleName");
		}
		return $self;
	}

	/**
	 * Function check if convert for module and view exist.
	 *
	 * @param int    $moduleName
	 * @param string $view
	 *
	 * @return bool
	 */
	public static function checkIfModuleCanConverted($moduleName, $view = '')
	{
		$query = (new \App\Db\Query())->from('a_#__record_converter')->where(['source_module' => \App\Module::getModuleId($moduleName), 'status' => 1]);
		if ($view) {
			$query->andWhere(['or like', 'view',
				[
					$view,
					'%,' . $view . ',%',
					'%' . $view . ',',
					$view . ',%',
				], false,
			]);
		}
		return $query->exists(\App\Db::getInstance('admin'));
	}

	/**
	 * Function gets module converters.
	 *
	 * @param int    $moduleName
	 * @param string $view
	 *
	 * @return array
	 */
	public static function getModuleConverters($moduleName, $view = '')
	{
		$query = (new \App\Db\Query())->from('a_#__record_converter')->where(['source_module' => $moduleName, 'status' => 1]);
		if ($view) {
			$query->andWhere(['or like', 'view',
				[
					$view,
					'%,' . $view . ',%',
					'%' . $view . ',',
					$view . ',%',
				], false,
			]);
		}
		return $query->createCommand()->queryAllByGroup(1);
	}

	/**
	 * Function get number of created records.
	 *
	 * @param string $moduleName
	 * @param array  $records
	 * @param strign $fieldMerge
	 *
	 * @return int
	 */
	public static function countCreatedRecords($moduleName, $records, $fieldMerge)
	{
		$focus = \CRMEntity::getInstance($moduleName);
		if ($fieldMerge) {
			return count((new \App\Db\Query())->select([$focus->table_name . ".$fieldMerge", $focus->table_index])->from($focus->table_name)->where([$focus->table_index => $records])->createCommand()->queryAllByGroup(2));
		} else {
			return count($records);
		}
	}

	/**
	 * Function variable initializing.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function init()
	{
		$this->sourceModule = \App\Module::getModuleName($this->get('source_module'));
		$this->sourceModuleModel = \Vtiger_Module_Model::getInstance($this->sourceModule);
		$this->destinyModuleModel = \Vtiger_Module_Model::getInstance($this->destinyModule);
		$this->fieldMapping = $this->get('field_mappging') ? \App\Json::decode($this->get('field_mappging')) : '';
		$this->inventoryMapping = $this->get('inv_field_mapping') ? \App\Json::decode($this->get('inv_field_mapping')) : '';
	}

	/**
	 * Main function of class.
	 *
	 * @param array  $records
	 * @param string $destinyModule
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function process($records, $destinyModule)
	{
		$this->destinyModule = $destinyModule;
		$this->init();
		$recordsAmount = count($records);
		$isFieldMergeExists = $this->checkFieldMerge();
		if ($isFieldMergeExists && $recordsAmount > 1) {
			$this->getRecordsGroupBy($records);
		} else {
			$this->getRecordModelsWithoutMerge($records);
		}
		if ($this->fieldMapping && $this->fieldMapping['mapping'][$this->destinyModuleModel->getId()] && (!$isFieldMergeExists || $recordsAmount === 1)) {
			$this->processFieldMapping();
		}
		if ($this->inventoryMapping && $this->sourceModuleModel->isInventory() && $this->destinyModuleModel->isInventory()) {
			$this->processInventoryMapping();
		}
		$this->saveChanges();
	}

	/**
	 * Function to edit process.
	 *
	 * @param int    $record
	 * @param string $destinyModule
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function processToEdit($record, $destinyModule)
	{
		$this->destinyModule = $destinyModule;
		$this->init();
		$this->getRecordModelsWithoutMerge([$record]);
		$isFieldMergeExists = $this->checkFieldMerge();
		if ($this->fieldMapping && $this->fieldMapping['mapping'][$this->destinyModuleModel->getId()] && $isFieldMergeExists) {
			$this->processFieldMapping();
		}
		if ($this->inventoryMapping && $this->sourceModuleModel->isInventory() && $this->destinyModuleModel->isInventory()) {
			$this->processInventoryMapping();
		}
		return $this->cleanRecordModels;
	}

	/**
	 * Function prepare records model group by field merge.
	 *
	 * @param array $records
	 */
	public function getRecordsGroupBy($records)
	{
		$focus = \CRMEntity::getInstance($this->sourceModule);
		$groupRecords = (new \App\Db\Query())->select([$focus->table_name . ".{$this->get('field_merge')}", $focus->table_index])->from($focus->table_name)->where([$focus->table_index => $records])->createCommand()->queryAllByGroup(2);

		foreach ($groupRecords as $groupBy => $recordsId) {
			$this->cleanRecordModels[$groupBy] = \Vtiger_Record_Model::getCleanInstance($this->destinyModule);
			foreach ($recordsId as $recordId) {
				$this->recordModels[$groupBy][] = \Vtiger_Record_Model::getInstanceById($recordId, $this->sourceModule);
			}
		}
	}

	/**
	 * Function prepare records model.
	 *
	 * @param array $records
	 */
	public function getRecordModelsWithoutMerge($records)
	{
		foreach ($records as $recordId) {
			$this->cleanRecordModels[] = \Vtiger_Record_Model::getCleanInstance($this->destinyModule);
			$this->recordModels[] = \Vtiger_Record_Model::getInstanceById($recordId, $this->sourceModule);
		}
	}

	/**
	 * Function prepare mapping fields.
	 */
	public function processFieldMapping()
	{
		$this->checkFieldMappingFields();
		foreach ($this->recordModels as $key => $recordModel) {
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			foreach ($this->fieldMapping['mapping'][$this->destinyModuleModel->getId()] as $destinyField => $sourceField) {
				$textParser->setContent($sourceField);
				$this->cleanRecordModels[$key]->set($destinyField, $textParser->parse()->getContent());
			}
		}
	}

	/**
	 * Function save changes in new record models.
	 */
	public function saveChanges()
	{
		foreach ($this->cleanRecordModels as $recordModel) {
			$recordModel->save();
		}
	}

	/**
	 * Function prepare inventory mapping.
	 */
	public function processInventoryMapping()
	{
		if ($this->inventoryMapping) {
			$inventoryField = \Vtiger_InventoryField_Model::getInstance($this->sourceModule);
			$sourceInvFields = $inventoryField->getFields(true);
			$inventoryField = \Vtiger_InventoryField_Model::getInstance($this->destinyModule);
			$destinyInvFields = $inventoryField->getFields(true);
			$invData = [];
			$counter = 1;
			$inventoryDataForEdit = [];

			if ($this->inventoryMapping[0] === 'auto') {
				$inventoryFields = array_merge($sourceInvFields[1], $destinyInvFields[1]);
				foreach ($this->recordModels as $groupBy => $recordModel) {
					if (is_array($recordModel)) {
						foreach ($recordModel as $recordModelGroupBy) {
							foreach ($recordModelGroupBy->getInventoryData() as $inventoryRow) {
								foreach ($inventoryFields as $field) {
									$inventoryData[$groupBy][$counter][$field->get('columnname')] = $inventoryRow[$field->get('columnname')];
									$invData[$groupBy][$field->get('columnname') . $counter] = $inventoryRow[$field->get('columnname')];
								}
								$inventoryDataForEdit[$groupBy][$counter]['seq'] = $counter;
								$invData[$groupBy]['seq' . $counter] = $counter;
								$invData[$groupBy]['inventoryItemsNo'] = $counter;
								$counter++;
							}
						}
					} else {
						foreach ($recordModel->getInventoryData() as $inventoryRow) {
							foreach ($inventoryFields as $field) {
								$inventoryDataForEdit[$groupBy][$counter][$field->get('columnname')] = $inventoryRow[$field->get('columnname')];
								$invData[$field->get('columnname') . $counter] = $inventoryRow[$field->get('columnname')];
							}
							$inventoryDataForEdit[$groupBy][$counter]['seq'] = $counter;
							$invData['seq' . $counter] = $counter;
							$invData['inventoryItemsNo'] = $counter;
							$counter++;
						}
					}
					$this->cleanRecordModels[$groupBy]->setInventoryData($inventoryDataForEdit[$groupBy]);
					$this->cleanRecordModels[$groupBy]->setInventoryRawData(new \App\Request($invData[$groupBy], false));
				}
			} else {
				foreach ($this->recordModels as $groupBy => $recordModel) {
					if (is_array($recordModel)) {
						foreach ($recordModel as $recordModelGroupBy) {
							foreach ($recordModelGroupBy->getInventoryData() as $inventoryRow) {
								foreach ($this->inventoryMapping[$this->destinyModuleModel->getId()] as $destinyField => $sourceField) {
									$invData[$destinyField . $counter] = $inventoryRow[$sourceField];
									$inventoryDataForEdit[$groupBy][$counter][$destinyField] = $inventoryRow[$sourceField];
								}
								$inventoryDataForEdit[$groupBy][$counter]['seq'] = $counter;
								$invData['name' . $counter] = $inventoryRow['id'];
								$invData['seq' . $counter] = $counter;
								$invData['inventoryItemsNo'] = $counter++;
							}
						}
					} else {
						foreach ($recordModel->getInventoryData() as $inventoryRow) {
							foreach ($this->inventoryMapping[$this->destinyModuleModel->getId()] as $destinyField => $sourceField) {
								$invData[$destinyField . $counter] = $inventoryRow[$sourceField];
								$inventoryDataForEdit[$groupBy][$counter][$destinyField] = $inventoryRow[$sourceField];
							}
							$inventoryDataForEdit[$groupBy][$counter]['seq'] = $counter;
							$invData['name' . $counter] = $inventoryRow['id'];
							$invData['seq' . $counter] = $counter;
							$invData['inventoryItemsNo'] = $counter++;
						}
					}
					$this->cleanRecordModels[$groupBy]->setInventoryData($inventoryDataForEdit[$groupBy]);
					$this->cleanRecordModels[$groupBy]->setInventoryRawData(new \App\Request($invData[$groupBy], false));
				}
			}
		}
	}

	/**
	 * Function check mapped fields.
	 */
	public function checkFieldMappingFields()
	{
		//TODO sprawdzanie sourceField
		if (isset($this->fieldMapping['mapping'][$this->destinyModuleModel->getId()])) {
			foreach ($this->fieldMapping['mapping'][$this->destinyModuleModel->getId()] as $destinyField => $sourceField) {
				if (!$this->destinyModuleModel->getField($destinyField)) {
					unset($this->fieldMapping['mapping'][$this->destinyModuleModel->getId()][$destinyField]);
				}
			}
		}
	}

	/**
	 * Function check if merge can be execute.
	 *
	 * @return bool
	 */
	public function checkFieldMerge()
	{
		$destinyReferenceFields = $this->destinyModuleModel->getFieldsByReference();
		$sourceReferenceFields = $this->sourceModuleModel->getFieldsByReference();
		$referenceDestinyField = $this->fieldMapping['field_merge'][$this->destinyModuleModel->getId()];
		if ($referenceDestinyField) {
			if (!$this->destinyModuleModel->getField($referenceDestinyField) || !$this->sourceModuleModel->getField($this->get('field_merge')) || !isset($destinyReferenceFields[$referenceDestinyField]) || !isset($sourceReferenceFields[$referenceDestinyField])) {
				return false;
			} else {
				return true;
			}
		}
		return false;
	}
}
