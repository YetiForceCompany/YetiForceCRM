<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class RecycleBin_ListView_Model extends Vtiger_ListView_Model
{

	/**
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
	 * @param string $moduleName - Module Name
	 * @param string $sourceModule - Source Module Name
	 * @return Vtiger_ListView_Model instance
	 */
	public static function getInstance($moduleName, $sourceModule = 0)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $moduleName);
		$instance = new $modelClassName();

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$queryGenerator = new \App\QueryGenerator($sourceModuleModel->get('name'));
		$cvidObj = CustomView_Record_Model::getAllFilterByModule($sourceModuleModel->get('name'));
		$viewId = $cvidObj->getId('cvid');
		$queryGenerator->initForCustomViewById($viewId);
		return $instance->set('module', $sourceModuleModel)->set('query_generator', $queryGenerator);
	}

	/**
	 * Load list view conditions
	 * @param string $moduleName
	 */
	public function loadListViewCondition()
	{
		$queryGenerator = $this->get('query_generator');
		$queryGenerator->deletedCondition = false;
		$queryGenerator->addNativeCondition(['vtiger_crmentity.deleted' => 1]);
		parent::loadListViewCondition();
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewCount()
	{
		$queryGenerator = $this->get('query_generator');
		$queryGenerator->deletedCondition = false;
		$queryGenerator->addNativeCondition(['vtiger_crmentity.deleted' => 1]);
		return parent::getListViewCount();
	}
}
