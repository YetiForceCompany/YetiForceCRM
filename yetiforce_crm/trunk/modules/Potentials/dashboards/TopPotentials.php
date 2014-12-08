<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Potentials_TopPotentials_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$linkId = $request->get('linkid');
		$page = $request->get('page');
		if(empty($page)) {
			$page = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$models = $moduleModel->getTopPotentials($pagingModel);

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODELS', $models);

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/TopPotentialsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TopPotentials.tpl', $moduleName);
		}
	}
}
