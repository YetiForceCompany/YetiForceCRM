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
	private $mapping = false;
	private $sourcePickListValues = false;
	private $targetPickListValues = false;
	private $nonMappedSourcePickListValues = false;

	/**
	 * Function to get the Id.
	 *
	 * @return int|null Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	public function getName()
	{
		return '';
	}

	public function getEditRecordUrl(int $recordId): string
	{
		return 'index.php?parent=Settings&module=PickListDependency&view=Edit&recordId=' . $recordId;
	}

	public function getRecordLinks()
	{
		$editLink = [
			'linkurl' => $this->getEditRecordUrl($this->getId()),
			'linklabel' => 'LBL_EDIT',
			'linkicon' => 'yfi yfi-full-editing-view',
			'linkclass' => 'btn btn-sm btn-info',
		];
		$editLinkInstance = Vtiger_Link_Model::getInstanceFromValues($editLink);

		$deleteLink = [
			'linkurl' => "javascript:Settings_PickListDependency_Js.triggerDelete(event, {$this->get('id')})",
			'linklabel' => 'LBL_DELETE',
			'linkicon' => 'fas fa-trash-alt',
			'linkclass' => 'btn btn-sm btn-danger',
		];
		$deleteLinkInstance = Vtiger_Link_Model::getInstanceFromValues($deleteLink);

		return [$editLinkInstance, $deleteLinkInstance];
	}

	/**
	 * Get picklist fields for module.
	 *
	 * @return array
	 */
	public function getAllPickListFields(): array
	{
		if ($this->get('sourceModule')) {
			$tabId = \App\Module::getModuleId($this->get('sourceModule'));
		} else {
			$tabId = $this->get('tabid');
		}
		$query = (new \App\Db\Query())->select(['vtiger_field.fieldlabel', 'vtiger_field.fieldname'])->from('vtiger_field')
			->where(['displaytype' => 1, 'vtiger_field.tabid' => $tabId, 'vtiger_field.uitype' => [15, 16], 'vtiger_field.presence' => [0, 2]])
			->andWhere(['not', ['vtiger_field.block' => null]])
			->andWhere(['<>', 'vtiger_field.block', 0]);
		$dataReader = $query->createCommand()->query();
		$fields = [];
		while ($row = $dataReader->read()) {
			$fields[$row['fieldname']] = $row['fieldlabel'];
		}
		$dataReader->close();

		return $fields;
	}

	public function getPickListDependency()
	{
		if (empty($this->mapping)) {
			$query = (new App\Db\Query())->from('vtiger_picklist_dependency')->where(['groupId' => $this->getId()]);
			$dataReader = $query->createCommand()->query();
			$valueMapping = [];
			while ($row = $dataReader->read()) {
				$valueMapping[] = [
					'sourcevalue' => $row['sourcevalue'],
					'secondValues' => \App\Json::decode($row['second_values']),
					//	'thirdValues' => \App\Json::decode($row['third_values'])
				];
			}
			//var_dump($valueMapping);
			$dataReader->close();
			$this->mapping = $valueMapping;
		}

		return $this->mapping;
	}

	public function getPickListDependencyForThree()
	{
		$query = (new App\Db\Query())->from('vtiger_picklist_dependency')->where(['groupId' => $this->getId()]);
		$dataReader = $query->createCommand()->query();
		$valueMapping = [];
		while ($row = $dataReader->read()) {
			$secondValue = \App\Json::decode($row['second_values'])[0];
			$valueMapping[$row['sourcevalue']][$secondValue] =
				\App\Json::decode($row['third_values']);
		}
		$dataReader->close();
		return $valueMapping;
	}

	public function getPickListValues($fieldName)
	{
		$picklistValues = [];
		if ($fieldName) {
			$picklistValues = App\Fields\Picklist::getValuesName($fieldName);
		}
		return $picklistValues;
	}

	/*
	public function getSourcePickListValues()
	{
		if (empty($this->sourcePickListValues)) {
			$this->sourcePickListValues = $this->getPickListValues($this->get('sourcefield'));
		}
		return $this->sourcePickListValues;
	}

	public function getTargetPickListValues()
	{
		if (empty($this->targetPickListValues)) {
			$this->targetPickListValues = $this->getPickListValues($this->get('secondField'));
		}
		return $this->targetPickListValues;
	}

	public function getPickListValuesForField()
	{
		if ($this->get('thirdField') && '' !== $this->get('thirdField')) {
			return $this->getPickListValues($this->get('thirdField'));
		}
		return [];
		if (empty($this->thirdPickListValues) && $this->get('thirdField') && '' !== $this->get('thirdField')) {
			$this->thirdPickListValues = $this->getPickListValues($this->get('thirdField'));
		}
		return $this->thirdPickListValues;

	}
	*/

	public function getNonMappedSourcePickListValues()
	{
		//todo
		return false;
		if (empty($this->nonMappedSourcePickListValues)) {
			$pickListValues = $this->getSourcePickListValues();
			$dependencyMapping = $this->getPickListDependency();
			foreach ($dependencyMapping as $mappingDetails) {
				unset($pickListValues[$mappingDetails['sourcevalue']]);
			}
			$this->nonMappedSourcePickListValues = $pickListValues;
		}
		return $this->nonMappedSourcePickListValues;
	}

	public function save()
	{
		if (!$dependencyPicklistId = $this->checkIsDependencyExists()) {
			$dependencyPicklistId = $this->createNewDependency();
		}
		$this->set('id', $dependencyPicklistId);
		if ($this->get('thirdField')) {
			$this->saveForThreeFields();
		} else {
			$this->saveForTwoFields();
		}

		\App\Cache::delete('picklistDependencyFields', $this->get('sourceModule'));
		\App\Cache::delete('getPicklistDependencyDatasource', $this->get('sourceModule'));

		return true;
	}

	public function checkIsDependencyExists()
	{
		$dependencyExistsQuery = (new \App\Db\Query())->select(['id'])->from('s_yf_picklist_dependency')->where([
			'tabid' => App\Module::getModuleId($this->get('sourceModule')),
			'source_field' => $this->get('sourceField'),
			'second_field' => $this->get('secondField')
		]);
		if ($thirdField = $this->get('thirdField')) {
			$dependencyExistsQuery->andWhere(['third_field' => $thirdField]);
		} else {
			$dependencyExistsQuery->andWhere(['third_field' => '']);
		}
		return $dependencyExistsQuery->scalar();
	}

	public function createNewDependency()
	{
		$db = App\Db::getInstance();
		$db->createCommand()->insert('s_yf_picklist_dependency', [
			'tabid' => App\Module::getModuleId($this->get('sourceModule')),
			'source_field' => $this->get('sourceField'),
			'second_field' => $this->get('secondField'),
			'third_field' => $this->get('thirdField')
		])->execute();
		return $db->getLastInsertID('s_yf_picklist_dependency_id_seq');
	}

	public function saveForTwoFields()
	{
		$db = App\Db::getInstance();
		$dependencyPicklistId = $this->get('id');
		$valueMapping = $this->get('picklistDependencies');
		$countValueMapping = \count($valueMapping);

		for ($dependencyCounter = 0; $dependencyCounter < $countValueMapping; ++$dependencyCounter) {
			$mapping = $valueMapping[$dependencyCounter];
			$sourceValue = $mapping['sourcevalue'];
			$targetValues = $mapping['targetvalues'];
			$serializedTargetValues = \App\Json::encode($targetValues);
			/*
			TODO DO usunięcia?
			$optionalsourcefield = $mapping['optionalsourcefield'] ?? '';
			$optionalsourcevalues = $mapping['optionalsourcevalues'] ?? '';
			if (!empty($optionalsourcefield)) {
				$criteria = [];
				$criteria['fieldname'] = $optionalsourcefield;
				$criteria['fieldvalues'] = $optionalsourcevalues;
				$serializedCriteria = \App\Json::encode($criteria);
			} else {
				$serializedCriteria = null;
			}
			*/

			if ($picklistId = $this->getPicklistDependencyIfExist($sourceValue, $targetValues)) {
				App\Db::getInstance()->createCommand()->update('vtiger_picklist_dependency', [
					'targetvalues' => $serializedTargetValues,
					//'criteria' => $serializedCriteria,
				], ['id' => $picklistId])->execute();
			} else {
				//	$db->createCommand()->insert('s_yf_picklist_dependency', ['test' => 7])->execute();
				//	$dependentGroupId = $db->getLastInsertID('s_yf_picklist_dependency_id_seq');

				$db->createCommand()->insert('vtiger_picklist_dependency', [
					/*
					'tabid' => $tabId,
					'sourcefield' => $sourceField,
					'targetfield' => $secondField,
					'criteria' => $serializedCriteria,
					*/
					'groupId' => $dependencyPicklistId,
					'sourcevalue' => $sourceValue,
					'targetvalues' => $serializedTargetValues,
				])->execute();
			}
		}
	}

	public function saveForThreeFields()
	{
		$db = App\Db::getInstance();
		$dependencyPicklistId = $this->get('id');
		foreach ($this->get('picklistDependencies') as $sourceValue => $secondValues) {
			foreach ($secondValues as $secondValue => $thirdValues) {
				if ($picklistId = $this->getPicklistDependencyIfExist($sourceValue, $secondValue)) {
					App\Db::getInstance()->createCommand()->update('vtiger_picklist_dependency', [
						'third_values' => App\Json::encode($thirdValues),
					], ['id' => $picklistId])->execute();
				} else {
					$db->createCommand()->insert('vtiger_picklist_dependency', [
						'groupId' => $dependencyPicklistId,
						'sourcevalue' => $sourceValue,
						'second_values' => App\Json::encode([$secondValue]),
						'third_values' => App\Json::encode($thirdValues),
					])->execute();
				}
			}
		}
	}

	public function getPicklistDependencyIfExist($sourceValue, $secondValues)
	{
		$dependencyPicklistQuery = (new App\Db\Query())->select(['id'])->from('vtiger_picklist_dependency')
			->where(['groupId' => $this->get('id'), 'sourcevalue' => $sourceValue]);

		if ($this->get('thirdField')) {
			$dependencyPicklistQuery->andWhere(['second_values' => App\Json::encode([$secondValues])]);
		}
		return $dependencyPicklistQuery->scalar();
	}

	public function delete()
	{
		App\Db::getInstance()->createCommand()->delete('s_yf_picklist_dependency', [
			'id' => $this->get('id')
		])->execute();
		$sourceModule = $this->get('sourceModule');
		\App\Cache::delete('picklistDependencyFields', $sourceModule);
		\App\Cache::delete('getPicklistDependencyDatasource', $sourceModule);
	}

	private function loadFieldLabels()
	{
		if ($this->get('sourceModule')) {
			$tabId = \App\Module::getModuleId($this->get('sourceModule'));
		} else {
			$tabId = $this->get('tabid');
		}
		$fieldNames = [$this->get('source_field'), $this->get('second_field'), $this->get('third_field')];  //puste thirdfield?
		$dataReader = (new App\Db\Query())->select(['fieldlabel', 'fieldname'])
			->from('vtiger_field')
			->where(['fieldname' => $fieldNames, 'tabid' => $tabId])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$fieldName = $row['fieldname'];
			if ($fieldName === $this->get('source_field')) {
				$this->set('sourcelabel', $row['fieldlabel']);
			} elseif ($fieldName === $this->get('second_field')) {
				$this->set('targetlabel', $row['fieldlabel']);
			} else {
				$this->set('thirdlabel', $row['fieldlabel']);
			}
		}

		$dataReader->close();
	}

	public function getSourceFieldLabel()
	{
		$sourceFieldLabel = $this->get('sourcelabel');
		if (empty($sourceFieldLabel)) {
			$this->loadFieldLabels();
		}
		//	var_dump($this);
		//	exit;
		return \App\Language::translate($this->get('sourcelabel'), $this->get('sourceModule'));
	}

	public function getTargetFieldLabel()
	{
		$secondFieldLabel = $this->get('targetlabel');
		if (empty($secondFieldLabel)) {
			$this->loadFieldLabels();
		}
		return \App\Language::translate($this->get('targetlabel'), $this->get('sourceModule'));
	}

	public static function getInstanceById(int $id)
	{
		//\App\Db::getInstance('admin') ????
		$row = (new \App\Db\Query())->from('s_yf_picklist_dependency')->where(['id' => $id])->one();
		$instance = false;
		if ($row) {
			$instance = new self();
			$row['sourceModule'] = App\Module::getModuleName($row['tabid']);
			$instance->setData($row);
		}
		return $instance;
	}

	/**
	 * function to get clean instance.
	 *
	 * @return \static
	 */
	public static function getCleanInstance(): self
	{
		return new static();
	}

	public static function getInstance($module, $sourceField = '', $secondField = '', $thirdField = '')
	{
		$self = new self();
		$self->set('sourceModule', $module)
			->set('source_field', $sourceField)
			->set('second_field', $secondField)
			->set('third_field', $thirdField);

		return $self;
	}

	public static function checkCyclicDependencyExists(string $module, string $sourceField, string $secondField, ?string $thirdField): bool
	{
		$query = (new App\Db\Query())->from('s_yf_picklist_dependency')
			->where(['tabid' => \App\Module::getModuleId($module), 'source_field' => $sourceField, 'second_field' => $secondField]);
		if ($thirdField) {
			$query->andWhere(['third_field' => $thirdField]);
		} else {
			$query->andWhere(['third_field' => '']);
		}
		return $query->exists();
	}
}
