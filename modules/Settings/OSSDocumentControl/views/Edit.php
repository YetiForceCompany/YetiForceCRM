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

Class Settings_OSSDocumentControl_Edit_View extends Settings_OSSDocumentControl_Base_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(Vtiger_Request $request)
	{
		$moduleSettingsName = $request->getModule(false);
		$moduleName = $request->getModule();

		$idTpl = $request->get('tpl_id');

		$viewer = $this->getViewer($request);

		if ($idTpl) {
			$docInfo = Settings_OSSDocumentControl_Module_Model::getDocInfo($idTpl);

			$viewer->assign('BASE_INFO', $docInfo['basic_info']);
			$viewer->assign('TPL_ID', $idTpl);
		}

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE_LIST', Settings_OSSDocumentControl_Module_Model::getSupportedModules());
		$viewer->assign('SETTINGS_MODULE_NAME', $moduleSettingsName);
		echo $viewer->view('Edit.tpl', $moduleSettingsName, true);
	}
}
