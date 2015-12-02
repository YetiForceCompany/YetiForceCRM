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

class Settings_OSSDocumentControl_Condition_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleSettingsName = $request->getModule(false);
		$moduleName = $request->getModule();
		$baseModule = $request->get('base_module');

		$num = $request->get('num');

		if ("" == $num) {
			$num = 0;
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('NUM', ++$num);
		$viewer->assign('BASE_MODULE', $baseModule);
		$viewer->assign('FIELD_LIST', Settings_OSSDocumentControl_Module_Model::getListBaseModuleField($baseModule));
		echo $viewer->view('Condition.tpl', $moduleSettingsName, true);
	}
}
