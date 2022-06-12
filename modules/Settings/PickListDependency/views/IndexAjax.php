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

class Settings_PickListDependency_IndexAjax_View extends Settings_PickListDependency_Edit_View
{
	use App\Controller\ClearProcess;
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getDependencyGraph');
		$this->exposeMethod('dependentFields');
	}

	/**
	 * Get dependency graph.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function getDependencyGraph(App\Request $request)
	{
		$qualifiedName = $request->getModule(false);
		$recordModel = Settings_PickListDependency_Record_Model::getCleanInstance();
		foreach (['tabid', 'source_field'] as $fieldName) {
			if ($request->has($fieldName)) {
				$recordModel->set($fieldName, $request->getByType($fieldName, $recordModel->getFieldInstanceByName($fieldName)->get('purifyType')));
			}
		}

		$valueMapping = $recordModel->getPickListDependency();
		$picklistValues = [];
		$selectedFieldName = '';
		$sourceModuleModel = $recordModel->getSourceModule();
		$sourceField = $recordModel->getFieldInstanceByName('source_field');
		$picklistValues = $recordModel->getPickListValuesByField($sourceField->getName());
		if ($sourceValue = $recordModel->get($sourceField->getName())) {
			$selectedFieldName = $sourceValue ? $sourceModuleModel->getFieldByName($sourceValue)->getName() : '';
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MAPPED_VALUES', $valueMapping);
		$viewer->assign('SOURCE_PICKLIST_VALUES', $picklistValues);
		$viewer->assign('SOURCE_MODULE', $sourceModuleModel->getName());
		$viewer->assign('OPERATORS', ['e']);
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', []);
		$viewer->assign('RECORD_STRUCTURE', Settings_PickListDependency_Module_Model::getConditionBuilderStructure($sourceModuleModel, $selectedFieldName));
		$viewer->assign('SELECTED_MODULE', $sourceModuleModel->getName());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->view('ConditionList.tpl', $qualifiedName);
	}

	/**
	 * Get dependency fields.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function dependentFields(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$selectedModule = $request->getByType('tabid', App\Purifier::ALNUM);
		$recordModel = Settings_PickListDependency_Record_Model::getCleanInstance();
		$recordModel->set('tabid', $selectedModule);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('STRUCTURE', $recordModel->getModule()->getEditViewStructure($recordModel));
		$viewer->view('DependentFields.tpl', $qualifiedModuleName);
	}
}
