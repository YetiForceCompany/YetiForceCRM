<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

Class Settings_OSSDocumentControl_Index_View extends Settings_OSSDocumentControl_Base_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(Vtiger_Request $request)
	{
		$moduleSettingsName = $request->getModule(false);
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DOC_TPL_LIST', Settings_OSSDocumentControl_Module_Model::getDocList());
		$viewer->assign('SUPPORTED_MODULE_MODELS', Settings_OSSDocumentControl_Module_Model::getEntityModulesList());
		$viewer->assign('SETTINGS_MODULE_NAME', $moduleSettingsName);
		$viewer->assign('DOCUMENT_LIST', $moduleSettingsName);

		echo $viewer->view('Index.tpl', $moduleSettingsName, true);
	}
}
