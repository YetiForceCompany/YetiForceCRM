<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * *********************************************************************************** */

class Settings_PickListDependency_Edit_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModelList = Settings_PickListDependency_Module_Model::getPicklistSupportedModules();
		if ($request->isEmpty('sourceModule')) {
			$selectedModule = $moduleModelList[0]->name;
		} else {
			$selectedModule = $request->getByType('sourceModule', 2);
		}
		$sourceField = $request->getByType('sourcefield', 2);
		$targetField = $request->getByType('targetfield', 2);
		$recordModel = Settings_PickListDependency_Record_Model::getInstance($selectedModule, $sourceField, $targetField);
		$dependencyGraph = false;
		$viewer = $this->getViewer($request);
		if (!empty($sourceField) && !empty($targetField)) {
			$viewer->assign('MAPPED_VALUES', $recordModel->getPickListDependency());
			$viewer->assign('SOURCE_PICKLIST_VALUES', $recordModel->getSourcePickListValues());
			$viewer->assign('TARGET_PICKLIST_VALUES', $recordModel->getTargetPickListValues());
			$viewer->assign('NON_MAPPED_SOURCE_VALUES', $recordModel->getNonMappedSourcePickListValues());
			$dependencyGraph = true;
		}
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('SELECTED_MODULE', $selectedModule);
		$viewer->assign('PICKLIST_FIELDS', $recordModel->getAllPickListFields());
		$viewer->assign('PICKLIST_MODULES_LIST', $moduleModelList);
		$viewer->assign('DEPENDENCY_GRAPH', $dependencyGraph);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js'
		]));
	}

	public function getHeaderCss(\App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'~libraries/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css'
		]));
	}
}
