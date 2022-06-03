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
	/*
	public $baseTable = 'vtiger_picklist_dependency';
	public $baseIndex = 'id';
	public $name = 'PickListDependency';
	*/

	/**
	 * Base table.
	 *
	 * @var string
	 */
	public $baseTable = 's_#__picklist_dependency';
	/**
	 * Base index.
	 *
	 * @var string
	 */
	public $baseIndex = 'id';
	/**
	 * List fields.
	 *
	 * @var array
	 */
	public $listFields = [
		'moduleName' => 'LBL_MODULE',
		'sourceFieldLabel' => 'LBL_SOURCE_FIELD',
		'secondFieldLabel' => 'LBL_SECOND_FIELD',
		'thirdFieldLabel' => 'LBL_THIRD_FIELD'
	];
	/**
	 * Name fields.
	 *
	 * @var array
	 */
	public $nameFields = ['name'];
	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $name = 'PickListDependency';

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
		return 'javascript:Settings_PickListDependency_Js.triggerAdd(event)';
	}

	public function isPagingSupported()
	{
		return false;
	}

	public static function getPicklistSupportedModules()
	{
		$query = (new \App\Db\Query())->select(['vtiger_field.tabid', 'vtiger_tab.tablabel', 'tabname' => 'vtiger_tab.name'])->from('vtiger_field')
			->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
			->where(['uitype' => [15, 16], 'vtiger_field.displaytype' => 1, 'vtiger_field.presence' => [0, 2]])
			->andWhere(['<>', 'vtiger_field.tabid', 29])
			->andWhere(['not', ['vtiger_field.block' => null]])
			->groupBy('vtiger_field.tabid, vtiger_tab.tablabel, vtiger_tab.name')
			->having(['>', 'count(*)', 1])->distinct('vtiger_field.tabid');

		$dataReader = $query->createCommand()->query();

		while ($row = $dataReader->read()) {
			$modules[$row['tablabel']] = $row['tabname'];
		}

		ksort($modules);
		$modulesModelsList = [];
		foreach ($modules as $moduleLabel => $moduleName) {
			$instance = new Vtiger_Module_Model();
			$instance->name = $moduleName;
			$instance->label = $moduleLabel;
			$modulesModelsList[] = $instance;
		}
		$dataReader->close();

		return $modulesModelsList;
	}
}
