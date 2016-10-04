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

class Settings_Widgets_Index_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$source = $request->get('source');
		$sourceModule = $request->get('sourceModule');
		if ($sourceModule != '')
			$source = vtlib\Functions::getModuleId($sourceModule);
		if ($source == '')
			$source = 6;
		$moduleModel = Settings_Widgets_Module_Model::getInstance($qualifiedModuleName);
		$RelatedModule = $moduleModel->getRelatedModule($source);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('SOURCE', $source);
		$viewer->assign('SOURCEMODULE', vtlib\Functions::getModuleName($source));
		$viewer->assign('WIDGETS', $moduleModel->getWidgets($source));
		$viewer->assign('RELATEDMODULES', $RelatedModule);
		$viewer->assign('FILTERS', json_encode($moduleModel->getFiletrs($RelatedModule)));
		$viewer->assign('CHECKBOXS', json_encode($moduleModel->getCheckboxs($RelatedModule)));
		$viewer->assign('SWITCHES_HEADER', json_encode($moduleModel->getHeaderSwitch()));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$moduleName = $request->getModule();
		$cssFileNames = [
			"modules.Settings.$moduleName.resources.$moduleName",
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.$moduleName"
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
