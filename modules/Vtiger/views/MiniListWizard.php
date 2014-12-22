<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_MiniListWizard_View extends Vtiger_Index_View {

	function process (Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIZARD_STEP', $request->get('step'));

		switch ($request->get('step')) {
			case 'step1':
				$modules = Vtiger_Module_Model::getSearchableModules();
				//Since comments is not treated as seperate module 
                unset($modules['ModComments']);
				$viewer->assign('MODULES', $modules);
				break;
			case 'step2':
				$selectedModule = $request->get('selectedModule');
				$filters = CustomView_Record_Model::getAllByGroup($selectedModule);
				$viewer->assign('ALLFILTERS', $filters);
				break;
			case 'step3':
				$selectedModule = $request->get('selectedModule');
				$filterid = $request->get('filterid');

				$db = PearDatabase::getInstance();
				$generator = new QueryGenerator($selectedModule, $currentUser);
				$generator->initForCustomViewById($filterid);

				$listviewController = new ListViewController($db, $currentUser, $generator);
				$listviewController->getListViewHeaderFields();
				$viewer->assign('LIST_VIEW_CONTROLLER', $listviewController);
				$viewer->assign('SELECTED_MODULE', $selectedModule);
				break;
		}

		$viewer->view('dashboards/MiniListWizard.tpl', $moduleName);
	}
}