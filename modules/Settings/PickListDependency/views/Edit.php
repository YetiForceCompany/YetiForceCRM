<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_PickListDependency_Edit_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

        $moduleModelList = Settings_PickListDependency_Module_Model::getPicklistSupportedModules();
        
		$selectedModule = $request->get('sourceModule');
        if(empty($selectedModule)) {
            $selectedModule = $moduleModelList[0]->name;
        }
		$sourceField = $request->get('sourcefield');
		$targetField = $request->get('targetfield');
		$recordModel = Settings_PickListDependency_Record_Model::getInstance($selectedModule, $sourceField, $targetField);
        
        $dependencyGraph = false;
        if(!empty($sourceField) && !empty($targetField)) {
            $dependencyGraph = $this->getDependencyGraph($request);
        }
        
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('SELECTED_MODULE',$selectedModule);
        $viewer->assign('PICKLIST_FIELDS',$recordModel->getAllPickListFields());
        $viewer->assign('PICKLIST_MODULES_LIST',$moduleModelList);
        $viewer->assign('DEPENDENCY_GRAPH', $dependencyGraph);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}
    
    public function getDependencyGraph(Vtiger_Request $request) {
        $qualifiedName = $request->getModule(false);
        $module = $request->get('sourceModule');
        $sourceField = $request->get('sourcefield');
        $targetField = $request->get('targetfield');
        $recordModel = Settings_PickListDependency_Record_Model::getInstance($module, $sourceField, $targetField);
        $valueMapping = $recordModel->getPickListDependency();
        $nonMappedSourceValues = $recordModel->getNonMappedSourcePickListValues();
        
        $viewer = $this->getViewer($request);
        $viewer->assign('MAPPED_VALUES', $valueMapping);
        $viewer->assign('SOURCE_PICKLIST_VALUES', $recordModel->getSourcePickListValues());
        $viewer->assign('TARGET_PICKLIST_VALUES', $recordModel->getTargetPickListValues());
        $viewer->assign('NON_MAPPED_SOURCE_VALUES', $nonMappedSourceValues);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('RECORD_MODEL', $recordModel);
		
        return $viewer->view('DependencyGraph.tpl',$qualifiedName, true);
    }
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'~libraries/jquery/malihu-custom-scrollbar/js/jquery.mCustomScrollbar.concat.min.js',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = array(
			'~/libraries/jquery/malihu-custom-scrollbar/css/jquery.mCustomScrollbar.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}
}