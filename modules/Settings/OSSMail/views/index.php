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

class Settings_OSSMail_Index_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$OSSMail_Record_Model = Vtiger_Record_Model::getCleanInstance('OSSMail');
		if (vtlib\Functions::getModuleId('OSSMailScanner')) {
			$OSSMailScanner_Record_Model = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
			$WidgetCfg = $OSSMailScanner_Record_Model->getConfig(false);
		}
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->assign('RecordModel', $OSSMail_Record_Model);
		$viewer->assign('WIDGET_CFG', $WidgetCfg);
		$viewer->assign('MODULENAME', $moduleName);
		echo $viewer->view('config.tpl', $moduleName, true);
	}
}
