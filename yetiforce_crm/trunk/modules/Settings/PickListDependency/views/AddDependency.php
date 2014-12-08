<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PickListDependency_AddDependency_View extends Settings_Vtiger_IndexAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('GetPickListFields');
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && method_exists($this, $mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

		$qualifiedModule = $request->getModule(true);
		$viewer = $this->getViewer($request);
		$moduleModels = Vtiger_Module_Model::getEntityModules();

		$viewer->assign('MODULES', $moduleModels);
		echo $viewer->view('AddDependency.tpl', $qualifiedModule);
	}

	/**
	 * Function returns the picklist field for a module
	 * @param Vtiger_Request $request
	 */
	function GetPickListFields(Vtiger_Request $request) {
		$module = $request->get('sourceModule');

		$fieldList = Settings_PickListDependency_Module_Model::getAvailablePicklists($module);

		$response = new Vtiger_Response();
		$response->setResult($fieldList);
		$response->emit();
	}

	function CheckCyclicDependency() {

	}
}