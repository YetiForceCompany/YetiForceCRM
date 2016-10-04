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

Class Settings_DataAccess_Step1_View extends Settings_Vtiger_Index_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();

		$idTpl = $request->get('tpl_id');

		$viewer = $this->getViewer($request);

		if ($idTpl) {
			$docInfo = Settings_DataAccess_Module_Model::getDataAccessInfo($idTpl);

			$viewer->assign('BASE_INFO', $docInfo['basic_info']);
			$viewer->assign('TPL_ID', $idTpl);
		}
		$viewer->assign('STEP', 1);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE_LIST', Settings_DataAccess_Module_Model::getSupportedModules());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		echo $viewer->view('Step1.tpl', $qualifiedModuleName, true);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.Conditions"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
