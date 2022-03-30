<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_LayoutEditor_Index_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showFieldLayout');
		$this->exposeMethod('showRelatedListLayout');
	}

	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if ($this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			//by default show field layout
			$this->showFieldLayout($request);
		}
	}

	public function showFieldLayout(App\Request $request)
	{
		$activeTab = 'detailViewLayout';
		if ($request->has('tab')) {
			$activeTab = $request->getByType('tab', \App\Purifier::ALNUM);
		}
		$sourceModule = $request->getByType('sourceModule', 2);
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
			$fieldName = $fieldModel->getName();
			$lastItem = strrchr($fieldName, '_');
			$firstItem = '';
			if($lastItem === '_extra'){
				$firstItem = str_replace($lastItem, '', $fieldName);
			}
			if((!empty($firstItem) && !empty($firstItemModuleModal = \Vtiger_Field_Model::getInstance($firstItem, \Vtiger_Module_Model::getInstance($sourceModule))) && $firstItemModuleModal->isActiveField() && $fieldModel->isActiveField()) && ($firstItemModuleModal->getUIType() == 11) && ($fieldModel->getUIType() == 1)){
				unset($fieldName);
			}
			if(isset($fieldName)){
				$blockIdFieldMap[$fieldModel->getBlockId()][$fieldName] = $fieldModel;
			}
			if (!$fieldModel->isActiveField()) {
				$inactiveFields[$fieldModel->getBlockId()][$fieldModel->getId()] = \App\Language::translate($fieldModel->get('label'), $sourceModule);
			}
		}
		foreach ($blockModels as $blockModel) {
			if (isset($blockIdFieldMap[$blockModel->get('id')])) {
				$fieldModelList = $blockIdFieldMap[$blockModel->get('id')];
				$blockModel->setFields($fieldModelList);
			}
		}
		$qualifiedModule = $request->getModule(false);
		$type = $moduleModel->isInventory() ? Vtiger_Module_Model::STANDARD_TYPE : Vtiger_Module_Model::ADVANCED_TYPE;
		$batchMethod = (new \App\BatchMethod([
			'method' => '\App\Module::changeType',
			'params' => [$sourceModule, $type],
		]));
		$viewer = $this->getViewer($request);
		$viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SUPPORTED_MODULES', $supportedModulesList);
		$viewer->assign('SELECTED_MODULE_MODEL', $moduleModel);
		$viewer->assign('BLOCKS', $blockModels);
		$viewer->assign('ADD_SUPPORTED_FIELD_TYPES', $moduleModel->getAddSupportedFieldTypes());
		$viewer->assign('DISPLAY_TYPE_LIST', Vtiger_Field_Model::showDisplayTypeList());
		$viewer->assign('MODULE', $qualifiedModule);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('IN_ACTIVE_FIELDS', $inactiveFields);
		$viewer->assign('IS_INVENTORY', $moduleModel->isInventory());
		$viewer->assign('CHANGE_MODULE_TYPE_DISABLED', $batchMethod->isExists());
		$viewer->assign('INVENTORY_MODEL', Vtiger_Inventory_Model::getInstance($sourceModule));
		$viewer->view('Index.tpl', $qualifiedModule);
	}

	public function showRelatedListLayout(App\Request $request)
	{
		$supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
		if ($request->isEmpty('sourceModule', true)) {
			//To get the first element
			$moduleName = reset($supportedModulesList);
			$sourceModule = Vtiger_Module_Model::getInstance($moduleName)->getName();
		} else {
			$sourceModule = $request->getByType('sourceModule', 2);
		}
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($sourceModule);
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SUPPORTED_MODULES', $supportedModulesList);
		$viewer->assign('RELATED_MODULES', $moduleModel->getRelations());
		$viewer->assign('MODULE', $qualifiedModule);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('BASE_CUSTOM_VIEW', [
			'relation' => \App\Language::translate('LBL_RECORDS_FROM_RELATION'),
			'private' => \App\Language::translate('LBL_RCV_PRIVATE', $qualifiedModule),
			'all' => \App\Language::translate('LBL_RCV_ALL', $qualifiedModule),
		]);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('RelatedList.tpl', $qualifiedModule);
	}

	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts(['libraries.clipboard.dist.clipboard']));
	}
}
