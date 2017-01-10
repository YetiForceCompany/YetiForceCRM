<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Settings_LayoutEditor_Index_View extends Settings_Vtiger_Index_View
{

	public function __construct()
	{
		$this->exposeMethod('showFieldLayout');
		$this->exposeMethod('showRelatedListLayout');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if ($this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			//by default show field layout
			$this->showFieldLayout($request);
		}
	}

	public function showFieldLayout(Vtiger_Request $request)
	{
		$sourceModule = $request->get('sourceModule');
		$supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();

		if (empty($sourceModule)) {
			//To get the first element
			$sourceModule = reset($supportedModulesList);
		}
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($sourceModule);
		$fieldModels = $moduleModel->getFields();
		$blockModels = $moduleModel->getBlocks();

		$blockIdFieldMap = [];
		$inactiveFields = [];
		foreach ($fieldModels as $fieldModel) {
			$blockIdFieldMap[$fieldModel->getBlockId()][$fieldModel->getName()] = $fieldModel;
			if (!$fieldModel->isActiveField()) {
				$inactiveFields[$fieldModel->getBlockId()][$fieldModel->getId()] = vtranslate($fieldModel->get('label'), $sourceModule);
			}
		}

		foreach ($blockModels as $blockLabel => $blockModel) {
			if (isset($blockIdFieldMap[$blockModel->get('id')])) {
				$fieldModelList = $blockIdFieldMap[$blockModel->get('id')];
				$blockModel->setFields($fieldModelList);
			}
		}

		$qualifiedModule = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SUPPORTED_MODULES', $supportedModulesList);
		$viewer->assign('SELECTED_MODULE_MODEL', $moduleModel);
		$viewer->assign('BLOCKS', $blockModels);
		$viewer->assign('ADD_SUPPORTED_FIELD_TYPES', $moduleModel->getAddSupportedFieldTypes());
		$viewer->assign('DISPLAY_TYPE_LIST', Vtiger_Field_Model::showDisplayTypeList());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE', $qualifiedModule);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('IN_ACTIVE_FIELDS', $inactiveFields);
		$viewer->assign('IS_INVENTORY', $moduleModel->isInventory());
		$viewer->assign('INVENTORY_MODEL', Vtiger_InventoryField_Model::getInstance($sourceModule));
		$viewer->view('Index.tpl', $qualifiedModule);
	}

	public function showRelatedListLayout(Vtiger_Request $request)
	{
		$sourceModule = $request->get('sourceModule');
		$supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();

		if (empty($sourceModule)) {
			//To get the first element
			$moduleName = reset($supportedModulesList);
			$sourceModule = Vtiger_Module_Model::getInstance($moduleName)->getName();
		}
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($sourceModule);
		$relatedModuleModels = $moduleModel->getRelations();

		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SUPPORTED_MODULES', $supportedModulesList);
		$viewer->assign('RELATED_MODULES', $relatedModuleModels);
		$viewer->assign('MODULE', $qualifiedModule);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('RelatedList.tpl', $qualifiedModule);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$jsFileNames = ['libraries.jquery.clipboardjs.clipboard'];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
