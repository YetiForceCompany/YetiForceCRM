<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
 
class Settings_WidgetsManagement_Configuration_View extends Settings_Vtiger_Index_View {

	function __construct() {
		$this->exposeMethod('showWidgetsManagement');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if($this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}else {
			//by default
			$this->showWidgetsManagement($request);
		}
	}

	public function showWidgetsManagement(Vtiger_Request $request) {
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Configuration_View::showWidgetsManagement() method ...");
		$sourceModule = $request->get('sourceModule');
		$dashboardModules = Settings_WidgetsManagement_Module_Model::getSelectableDashboard();

		if(empty($sourceModule))
			$sourceModule = 'Home';

		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		// get widgets list
		$widgets = $dashboardModules[$sourceModule];

		$role = Settings_WidgetsManagement_Module_Model::getRole();
		$widgetsStored = Settings_WidgetsManagement_Module_Model::getWidgets($sourceModule);
		if(!$widgetsStored['mandatory'])
			$widgetsStored['mandatory'] = array();
		if(!$widgetsStored['inactive'])
			$widgetsStored['inactive'] = array();
		
		$viewer->assign('MANDATORY_WIDGETS', $widgetsStored['mandatory']);
		$viewer->assign('INACTIVE_WIDGETS', $widgetsStored['inactive']);
		$viewer->assign('ROLES', $role);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SUPPORTED_MODULES', array_keys($dashboardModules));
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('MODULENAME', $request->getModule(false));
		echo $viewer->view('Configuration.tpl', $request->getModule(false), true);
		$log->debug("Exiting Settings_WidgetsManagement_Configuration_View::showWidgetsManagement() method ...");
	}

}