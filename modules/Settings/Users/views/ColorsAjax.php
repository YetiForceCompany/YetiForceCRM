<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Settings_Users_ColorsAjax_View extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPickListView');
		$this->exposeMethod('getPickListValueForField');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->get('mode');
		if ($this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	public function getPickListView(\App\Request $request)
	{
		$sourceModule = $request->get('source_module');
		$pickFieldId = $request->get('pickListFieldId');
		$pickListSupportedModules = Settings_Users_Module_Model::getPicklistSupportedModules();
		if ($sourceModule) {
			$moduleModel = Settings_Picklist_Module_Model::getInstance($sourceModule);
			$pickListFields = $moduleModel->getFieldsByType(array('picklist', 'multipicklist'));
		}
		if (!empty($pickFieldId)) {
			$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldId);
			$selectedFieldAllPickListValues = Users_Colors_Model::getPickListItems($fieldModel->getName());
			if (count($selectedFieldAllPickListValues)) {
				$firstRow = reset($selectedFieldAllPickListValues);
				if (!key_exists('color', $firstRow)) {
					$noColumn = true;
				} else {
					$noColumn = false;
				}
			} else {
				$noColumn = false;
			}
		} else {
			$noColumn = false;
		}
		$qualifiedName = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('PICKLIST_MODULES', $pickListSupportedModules);
		$viewer->assign('PICKLIST_NO_COLUMN', $noColumn);
		$viewer->assign('PICKLIST_FIELDS', $pickListFields);
		$viewer->assign('SELECTED_PICKLIST_FIELDMODEL', $fieldModel);
		$viewer->assign('SELECTED_PICKLIST_FIELD_ID', $pickFieldId);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('SELECTED_PICKLISTFIELD_ALL_VALUES', $selectedFieldAllPickListValues);
		$viewer->view('ColorsPickListView.tpl', $qualifiedName);
	}
}
