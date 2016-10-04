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

class OSSEmployees_Detail_View extends Vtiger_Detail_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);

		$moduleName = $request->getModule();

		//Added to remove the module specific js, as they depend on inventory files
		$modulePopUpFile = 'modules.' . $moduleName . '.resources.Popup';
		$moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';
		$moduleDetailFile = 'modules.' . $moduleName . '.resources.Detail';
		unset($headerScriptInstances[$modulePopUpFile]);
		unset($headerScriptInstances[$moduleEditFile]);
		unset($headerScriptInstances[$moduleDetailFile]);

		$jsFileNames = array(
			"modules.$moduleName.resources.Detail",
		);
		$jsFileNames[] = $moduleEditFile;
		$jsFileNames[] = $modulePopUpFile;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
