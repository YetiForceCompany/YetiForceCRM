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

class Settings_Picklist_IndexAjax_View extends Settings_Vtiger_IndexAjax_View
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPickListDetailsForModule');
		$this->exposeMethod('getPickListValueForField');
		$this->exposeMethod('getPickListValueByRole');
	}

	/**
	 * Get picklist details for module.
	 *
	 * @param App\Request $request
	 */
	public function getPickListDetailsForModule(App\Request $request)
	{
		$qualifiedName = $request->getModule(false);
		$sourceModule = $request->getByType('source_module', 2);
		$moduleModel = Settings_Picklist_Module_Model::getInstance($qualifiedName)->setSourceModule($sourceModule);
		$pickListFields = $moduleModel->getFieldsByType(['picklist', 'multipicklist'], true);

		$viewer = $this->getViewer($request);
		$viewer->assign('PICKLIST_FIELDS', $pickListFields);
		$viewer->assign('PICKLIST_INTERDEPENDENT', $moduleModel->listModuleInterdependentPickList(array_keys($pickListFields)));
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->view('ModulePickListDetail.tpl', $qualifiedName);
	}

	/**
	 * Get picklist values for field.
	 *
	 * @param App\Request $request
	 */
	public function getPickListValueForField(App\Request $request)
	{
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$moduleName = $request->getModule();
		$qualifiedName = $request->getModule(false);
		$selectedFieldAllPickListValues = [];
		$fieldModel = null;
		if (!empty($pickFieldName)) {
			$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule));
			$selectedFieldAllPickListValues = App\Fields\Picklist::getValuesName($fieldModel->getName());
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_PICKLIST_FIELDMODEL', $fieldModel);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('ROLES_LIST', Settings_Roles_Record_Model::getAll());
		$viewer->assign('SELECTED_PICKLISTFIELD_ALL_VALUES', $selectedFieldAllPickListValues);
		$viewer->view('PickListValueDetail.tpl', $qualifiedName);
	}

	/**
	 * Get picklist value by role.
	 *
	 * @param App\Request $request
	 */
	public function getPickListValueByRole(App\Request $request)
	{
		$qualifiedName = $request->getModule(false);
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$userSelectedRoleId = $request->getByType('rolesSelected', \App\Purifier::ALNUM);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule));

		$pickListValuesForRole = $fieldModel->getPicklistValuesForRole([$userSelectedRoleId], 'CONJUNCTION');
		$allPickListValues = App\Fields\Picklist::getValuesName($fieldModel->getName());
		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_PICKLIST_FIELDMODEL', $fieldModel);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('ROLE_PICKLIST_VALUES', $pickListValuesForRole);
		$viewer->assign('ALL_PICKLIST_VALUES', $allPickListValues);
		$viewer->view('PickListValueByRole.tpl', $qualifiedName);
	}
}
