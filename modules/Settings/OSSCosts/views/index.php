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

class Settings_OSSCosts_Index_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$Record_Model = Vtiger_Record_Model::getCleanInstance('OSSCosts');
		$config = $Record_Model->getConfig();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('CONFIG', $config);
		echo $viewer->view('settings.tpl', $moduleName, true);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{

		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$moduleOssCost = 'modules.' . $moduleName . '.resources.Edit';
		unset($headerScriptInstances[$moduleOssCost]);
		$jsFileNames = array(
			'modules.Inventory.resources.Edit',
			'modules.' . $moduleName . '.resources.Edit'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
