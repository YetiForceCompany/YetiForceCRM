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
    
class Settings_ApiAddress_Configuration_View extends Settings_Vtiger_Index_View {

    public function preProcess(Vtiger_Request $request) {
        parent::preProcess($request);
    }

	public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
		$config = Settings_ApiAddress_Module_Model::getConfig(true);
		$viewer->assign('CONFIG', $config);
        $viewer->assign('MODULENAME', $request->getModule(false));
		echo $viewer->view('Configuration.tpl', $request->getModule(false), true);
    }
}