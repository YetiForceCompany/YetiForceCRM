<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Home_DashBoard_View extends Vtiger_DashBoard_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$moduleName = $request->getModule();
		$currentDashboard = $request->get('dashboardId');
		if (empty($currentDashboard)) {
			$currentDashboard = Settings_WidgetsManagement_Module_Model::getDefaultDashboard();
		}
		$viewer = $this->getViewer($request);
		$modulesWithWidget = Vtiger_DashBoard_Model::getModulesWithWidgets($moduleName, $currentDashboard);
		$viewer->assign('MODULES_WITH_WIDGET', $modulesWithWidget);
		$this->preProcessDisplay($request);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'~libraries/jquery/boxslider/jqueryBxslider.js'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
