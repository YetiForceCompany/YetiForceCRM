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

class Settings_PickListDependency_Module_Model extends Settings_Vtiger_Module_Model
{
	/** @var string Module name. */
	public $name = 'PickListDependency';
	/** @var string Module name. */
	public $parent = 'Settings';
	/** @var string Base table. */
	public $baseTable = 's_#__picklist_dependency';
	/** @var string Base index. */
	public $baseIndex = 'id';

	/** @var array List fields. */
	public $listFields = [
		'tabid' => 'LBL_MODULE',
		'source_field' => 'LBL_SOURCE_FIELD'
	];
	/** @var array Name fields. */
	public $nameFields = ['name'];
	/** @var array Name fields. */
	public $editFields = ['tabid', 'source_field'];

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string - url
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=PickListDependency&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for Adding Dependency.
	 *
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?parent=Settings&module=PickListDependency&view=Edit';
	}

	/** {@inheritdoc} */
	public function getListFields(): array
	{
		if (!isset($this->listFieldModels)) {
			foreach (parent::getListFields() as $fieldName => $fieldModel) {
				if ('tabid' !== $fieldName) {
					$fieldModel->set('sort', true);
				}
			}
		}

		return $this->listFieldModels;
	}

	/**
	 * Get picklist supported modules.
	 *
	 * @return array
	 */
	public static function getPicklistSupportedModules(): array
	{
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.tabid', 'vtiger_tab.tablabel', 'name' => 'vtiger_tab.name'])->from('vtiger_field')
			->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
			->where(['uitype' => [15, 16], 'vtiger_field.displaytype' => 1, 'vtiger_field.presence' => [0, 2]])
			->andWhere(['<>', 'vtiger_field.tabid', 29])
			->andWhere(['not', ['vtiger_field.block' => null]])
			->andWhere(['vtiger_tab.presence' => 0])
			->groupBy('vtiger_field.tabid, vtiger_tab.tablabel, vtiger_tab.name')
			->having(['>', 'count(*)', 1])->distinct('vtiger_field.tabid')->createCommand()->query();

		$modules = [];
		while ($row = $dataReader->read()) {
			$tabId = $row['tabid'] = (int) $row['tabid'];
			$modules[$tabId] = $row;
		}

		return $modules;
	}

	/**
	 * Get structure fields.
	 *
	 * @param Settings_PickListDependency_Record_Model|null $recordModel
	 *
	 * @return array
	 */
	public function getEditViewStructure($recordModel): array
	{
		$structure = [];
		foreach ($this->editFields as $fieldName) {
			$fieldModel = $recordModel->getFieldInstanceByName($fieldName);
			$structure[$fieldName] = $fieldModel;
		}

		return $structure;
	}

	/**
	 * Get condition builder structure.
	 *
	 * @param Vtiger_Module_Model $moduleModel
	 * @param string|null         $skipfield
	 *
	 * @return voiarrayd
	 */
	public static function getConditionBuilderStructure(Vtiger_Module_Model $moduleModel, ?string $skipfield): array
	{
		$structure = [];
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			foreach ($blockModel->getFields() as $fieldName => $fieldModel) {
				if ($fieldName !== $skipfield && \in_array($fieldModel->getUIType(), [15, 16]) && $fieldModel->isActiveField()) {
					$structure[$blockLabel][$fieldName] = $fieldModel;
				}
			}
		}
		return $structure;
	}
}
