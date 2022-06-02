<?php

use App\Request;

class Settings_PickListDependency_DependentFields_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;
	use \App\Controller\Traits\SettingsPermission;

	public function process(Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleModelList = Settings_PickListDependency_Module_Model::getPicklistSupportedModules();
		if ($request->isEmpty('sourceModule')) {
			$selectedModule = $moduleModelList[0]->name;
		} else {
			$selectedModule = $request->getByType('sourceModule', \App\Purifier::ALNUM);
		}
		$thirdField = $request->isEmpty('thirdField') ? false : true;

		$recordModel = Settings_PickListDependency_Record_Model::getInstance($selectedModule);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('SELECTED_MODULE', $selectedModule);
		$viewer->assign('PICKLIST_FIELDS', $recordModel->getAllPickListFields());
		$viewer->assign('PICKLIST_MODULES_LIST', $moduleModelList);
		$viewer->assign('THIRD_FIELD', $thirdField);
		$viewer->view('DependentFields.tpl', $qualifiedModuleName);
	}
}
