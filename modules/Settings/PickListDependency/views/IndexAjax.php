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

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getDependencyGraph');
	}

	public function getDependencyGraph(App\Request $request)
	{
		$qualifiedName = $request->getModule(false);
		$selectedModule = $request->getByType('sourceModule', App\Purifier::STANDARD);
		$sourceField = $request->getByType('sourcefield', App\Purifier::STANDARD);
		$secondField = $request->getByType('secondField', App\Purifier::STANDARD);
		$thirdField = $request->isEmpty('thirdField') ? '' : $request->getByType('thirdField', \App\Purifier::ALNUM);
		$recordModel = Settings_PickListDependency_Record_Model::getInstance($selectedModule, $sourceField, $secondField, $thirdField);
		$valueMapping = $recordModel->getPickListDependency();
		$nonMappedSourceValues = $recordModel->getNonMappedSourcePickListValues();

		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_MODULE', $selectedModule);
		$viewer->assign('MAPPED_VALUES', $valueMapping);
		$viewer->assign('SOURCE_PICKLIST_VALUES', $recordModel->getPickListValues($recordModel->get('source_field')));
		$viewer->assign('TARGET_PICKLIST_VALUES', $recordModel->getPickListValues($recordModel->get('second_field')));
		$viewer->assign('THIRD_FIELD_PICKLIST_VALUES', $recordModel->getPickListValues($recordModel->get('third_field')));
		$viewer->assign('NON_MAPPED_SOURCE_VALUES', $nonMappedSourceValues);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('THIRD_FIELD', $thirdField);
		if ($thirdField) {
			$viewer->view('DependentFieldSettings.tpl', $qualifiedName);
		} else {
			$viewer->view('DependencyGraph.tpl', $qualifiedName);
		}
	}
}
