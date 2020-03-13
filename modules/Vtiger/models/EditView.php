<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Vtiger EditView Model Class.
 */
class Vtiger_EditView_Model extends \App\Base
{
	/**
	 * Function to get the instance.
	 *
	 * @param string $moduleName - module name
	 * @param string $recordId   - record id
	 *
	 * @return <Vtiger_DetailView_Model>
	 */
	public static function getInstance($moduleName, $recordId)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'EditView', $moduleName);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		return $instance->set('module', $moduleModel);
	}

	/**
	 * Function to get the Module Model.
	 *
	 * @return Vtiger_Module_Model instance
	 */
	public function getModule()
	{
		return $this->get('module');
	}

	/**
	 * Function to get the list of listview links for the module.
	 *
	 * @param <Array> $linkParams
	 *
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getEditViewLinks($linkParams)
	{
		return Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['EDIT_VIEW_HEADER', 'EDIT_VIEW_RECORD_COLLECTOR'], $linkParams);
	}
}
