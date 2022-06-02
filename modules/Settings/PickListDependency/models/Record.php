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

	public function getRecordLinks()
	{
		$soureModule = $this->get('sourceModule');
		$sourceField = $this->get('sourcefield');
		$targetField = $this->get('targetfield');
		$editLink = [
			//'linkurl' => "javascript:Settings_PickListDependency_Js.triggerEdit(event, {$this->getId()})",
			'linkurl' => "javascript:Settings_PickListDependency_Js.triggerEdit(event, '$soureModule', '$sourceField', '$targetField')",
			'linklabel' => 'LBL_EDIT',
			'linkicon' => 'yfi yfi-full-editing-view',
			'linkclass' => 'btn btn-sm btn-info',
		];
		$editLinkInstance = Vtiger_Link_Model::getInstanceFromValues($editLink);

		$deleteLink = [
			'linkurl' => "javascript:Settings_PickListDependency_Js.triggerDelete(event, '$soureModule','$sourceField', '$targetField')",
			'linklabel' => 'LBL_DELETE',
			'linkicon' => 'fas fa-trash-alt',
			'linkclass' => 'btn btn-sm btn-danger',
		];
		$deleteLinkInstance = Vtiger_Link_Model::getInstanceFromValues($deleteLink);

		return [$editLinkInstance, $deleteLinkInstance];
	}

	public function getAllPickListFields()
	{
		$tabId = \App\Module::getModuleId($this->get('sourceModule'));

		$query = (new \App\Db\Query())->select(['vtiger_field.fieldlabel', 'vtiger_field.fieldname'])->from('vtiger_field')
			->where(['displaytype' => 1, 'vtiger_field.tabid' => $tabId, 'vtiger_field.uitype' => [15, 16], 'vtiger_field.presence' => [0, 2]])
			->andWhere(['not', ['vtiger_field.block' => null]])
			->andWhere(['<>', 'vtiger_field.block', 0]);
		$dataReader = $query->createCommand()->query();
		$fieldlist = [];
		while ($row = $dataReader->read()) {
			$fieldlist[$row['fieldname']] = $row['fieldlabel'];
		}
		$dataReader->close();

		return $fieldlist;
	}

	public function getPickListDependency()
	{
		if (empty($this->mapping)) {
			$dependency = Vtiger_DependencyPicklist::getPickListDependency($this->get('sourceModule'), $this->get('sourcefield'), $this->get('secondField'));
			$this->mapping = $dependency['valuemapping'];
		}
		return $this->mapping;
	}

	private function getPickListValues($fieldName)
	{
		return App\Fields\Picklist::getValuesName($fieldName);
	}

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
		/*
		if (empty($this->thirdPickListValues) && $this->get('thirdField') && '' !== $this->get('thirdField')) {
			$this->thirdPickListValues = $this->getPickListValues($this->get('thirdField'));
		}
		return $this->thirdPickListValues;
		*/
	}

	public function getNonMappedSourcePickListValues()
	{
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

			if ($picklistId = $this->getPicklistDependencyIfExist()) {
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
				if ($picklistId = $this->getPicklistDependencyIfExist()) {
					App\Db::getInstance()->createCommand()->update('vtiger_picklist_dependency', [
						'third_values' => App\Json::encode($thirdValues),
					], ['id' => $picklistId])->execute();
				} else {
					$db->createCommand()->insert('vtiger_picklist_dependency', [
						'groupId' => $dependencyPicklistId,
						'sourcevalue' => $sourceValue,
						'second_values' => $secondValue,
						'third_values' => App\Json::encode($thirdValues),
					])->execute();
				}
			}
		}
	}

	public function getPicklistDependencyIfExist()
	{
		$dependencyPicklistQuery = (new App\Db\Query())->select(['id'])->from('vtiger_picklist_dependency')
			->where(['groupId' => $this->get('id'), 'sourcevalue' => $this->get('sourceValue')]);

		if ($this->get('secondValue')) {
			$dependencyPicklistQuery->andWhere(['second_values' => $this->get('secondValue')]);
		}
		return $dependencyPicklistQuery->scalar();
	}

	public function delete()
	{
		Vtiger_DependencyPicklist::deletePickListDependencies($this->get('sourceModule'), $this->get('sourcefield'), $this->get('secondField'));

		return true;
	}

	private function loadFieldLabels()
	{
		$tabId = \App\Module::getModuleId($this->get('sourceModule'));
		$fieldNames = [$this->get('sourcefield'), $this->get('secondField')];
		$dataReader = (new App\Db\Query())->select(['fieldlabel', 'fieldname'])
			->from('vtiger_field')
			->where(['fieldname' => $fieldNames, 'tabid' => $tabId])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$fieldName = $row['fieldname'];
			if ($fieldName === $this->get('sourcefield')) {
				$this->set('sourcelabel', $row['fieldlabel']);
			} else {
				$this->set('targetlabel', $row['fieldlabel']);
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
		//s_yf_picklist_dependency join z vtiger_picklist_dependency
		$row = (new \App\Db\Query())->from('vtiger_picklist_dependency')->where(['id' => $id])->one();
		$instance = false;
		if ($row) {
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @param string $type
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		//$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:Magento');
		return new self();
	}

	public static function getInstance($module, $sourceField = '', $secondField = '', $thirdField = '')
	{
		$self = new self();
		$self->set('sourceModule', $module)
			->set('sourcefield', $sourceField)
			->set('secondField', $secondField)
			->set('thirdField', $thirdField);

		return $self;
	}
}
