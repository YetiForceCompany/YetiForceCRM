<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
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
	 * @return number
	 */
	public function getId()
	{
		return '';
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
			'linkurl' => "javascript:Settings_PickListDependency_Js.triggerEdit(event, '$soureModule', '$sourceField', '$targetField')",
			'linklabel' => 'LBL_EDIT',
			'linkicon' => 'fas fa-edit',
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
			$dependency = Vtiger_DependencyPicklist::getPickListDependency($this->get('sourceModule'), $this->get('sourcefield'), $this->get('targetfield'));
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
			$this->targetPickListValues = $this->getPickListValues($this->get('targetfield'));
		}
		return $this->targetPickListValues;
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

	public function save($mapping)
	{
		$dependencyMap = [];
		$dependencyMap['sourcefield'] = $this->get('sourcefield');
		$dependencyMap['targetfield'] = $this->get('targetfield');
		$dependencyMap['valuemapping'] = $mapping;
		Vtiger_DependencyPicklist::savePickListDependencies($this->get('sourceModule'), $dependencyMap);

		return true;
	}

	public function delete()
	{
		Vtiger_DependencyPicklist::deletePickListDependencies($this->get('sourceModule'), $this->get('sourcefield'), $this->get('targetfield'));

		return true;
	}

	private function loadFieldLabels()
	{
		$tabId = \App\Module::getModuleId($this->get('sourceModule'));
		$fieldNames = [$this->get('sourcefield'), $this->get('targetfield')];
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
		$targetFieldLabel = $this->get('targetlabel');
		if (empty($targetFieldLabel)) {
			$this->loadFieldLabels();
		}
		return \App\Language::translate($this->get('targetlabel'), $this->get('sourceModule'));
	}

	public static function getInstance($module, $sourceField, $targetField)
	{
		$self = new self();
		$self->set('sourceModule', $module)
			->set('sourcefield', $sourceField)
			->set('targetfield', $targetField);

		return $self;
	}
}
