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
class Settings_MarketingProcesses_Index_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		global $log;
		$log->debug("Entering Settings_MarketingProcesses_Index_View::process() method ...");
		$qualifiedModule = $request->getModule(false);
		$state = Settings_Leads_ConvertToAccount_Model::getState();
		$processes = new Settings_MarketingProcesses_Processes_Model();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('USER_MODEL', $currentUser);
		$viewer->assign('STATE', $state);
		$viewer->assign('LEADSTATUS', Vtiger_Util_Helper::getPickListValues('leadstatus'));
		$viewer->assign('PROCESSES', $processes);
		$viewer->view('Index.tpl',$qualifiedModule);
		$log->debug("Exiting Settings_MarketingProcesses_Index_View::process() method ...");
	}
	
	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.Index",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
