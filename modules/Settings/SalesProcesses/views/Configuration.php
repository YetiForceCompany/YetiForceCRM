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
class Settings_SalesProcesses_Configuration_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		global $log;
		$log->debug("Entering Settings_SalesProcesses_Configuration_View::process() method ...");
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$viewer = $this->getViewer($request);
		$config = Settings_SalesProcesses_Module_Model::getConfig();

		$viewer->assign('CONFIG', $config);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));

		echo $viewer->view('Configuration.tpl', $request->getModule(false), true);
		$log->debug("Exiting Settings_SalesProcesses_Configuration_View::process() method ...");
	}
}
