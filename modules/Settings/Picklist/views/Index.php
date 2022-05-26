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

class Settings_Picklist_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$sourceModule = $request->getByType('source_module', 2);
		$pickListSupportedModules = Settings_Picklist_Module_Model::getPicklistSupportedModules();
		if (empty($sourceModule)) {
			$sourceModule = $pickListSupportedModules[0]->name;
		}
		$moduleModel = Settings_Picklist_Module_Model::getInstance($sourceModule);
		$viewer = $this->getViewer($request);
		$qualifiedName = $request->getModule(false);

		$viewer->assign('PICKLIST_MODULES', $pickListSupportedModules);

		$pickListFields = $moduleModel->getFieldsByType(['picklist', 'multipicklist'], true);
		if (\count($pickListFields) > 0) {
			$selectedPickListFieldModel = reset($pickListFields);
			$viewer->assign('PICKLIST_FIELDS', $pickListFields);
			$viewer->assign('PICKLIST_INTERDEPENDENT', $moduleModel->listModuleInterdependentPickList(array_keys($pickListFields)));
			$viewer->assign('SELECTED_PICKLIST_FIELDMODEL', $selectedPickListFieldModel);
			$viewer->assign('ROLES_LIST', Settings_Roles_Record_Model::getAll());
		}

		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->view('Index.tpl', $qualifiedName);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.$moduleName.resources.$moduleName",
		]));
	}
}
