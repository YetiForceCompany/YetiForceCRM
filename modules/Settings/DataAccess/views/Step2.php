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

Class Settings_DataAccess_Step2_View extends Settings_Vtiger_Index_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();
		$baseModule = $request->get('base_module');
		$idTpl = $request->get('tpl_id');
		$viewer = $this->getViewer($request);

		if ($idTpl && $baseModule != 'All') {
			$docInfo = Settings_DataAccess_Module_Model::getDataAccessInfo($idTpl);
			$viewer->assign('BASE_INFO', $docInfo['basic_info']);
			$countRequiredConditions = count($docInfo['required_conditions']);
			for ($i = 0; $i < $countRequiredConditions; $i++) {
				$fieldModel = Vtiger_Field_Model::getInstance($docInfo['required_conditions'][$i]['fieldname'], Vtiger_Module_Model::getInstance($baseModule));
				$docInfo['required_conditions'][$i]['info'] = $fieldModel->getFieldInfo();
			}

			$viewer->assign('REQUIRED_CONDITIONS', $docInfo['required_conditions']);

			$countOptionalConditions = count($docInfo['optional_conditions']);
			for ($i = 0; $i < $countOptionalConditions; $i++) {

				$fieldModel = Vtiger_Field_Model::getInstance($docInfo['optional_conditions'][$i]['fieldname'], Vtiger_Module_Model::getInstance($baseModule));
				$docInfo['optional_conditions'][$i]['info'] = $fieldModel->getFieldInfo();
			}
			$viewer->assign('OPTIONAL_CONDITIONS', $docInfo['optional_conditions']);
			$viewer->assign('TPL_ID', $idTpl);
		}
		$viewer->assign('STEP', 2);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('REQUEST', $request);
		$viewer->assign('SUMMARY', $request->get('summary'));
		$viewer->assign('BASE_MODULE', $baseModule);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('FIELD_LIST', Settings_DataAccess_Module_Model::getListBaseModuleField($baseModule));
		$viewer->assign('CONDITION_BY_TYPE', Settings_DataAccess_Module_Model::getConditionByType());

		echo $viewer->view('Step2.tpl', $qualifiedModuleName, true);
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
