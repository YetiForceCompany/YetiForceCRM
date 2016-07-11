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

class Settings_Widgets_Edit_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$wid = $request->get('id');
		$moduleModel = Settings_Widgets_Module_Model::getInstance($qualifiedModuleName);
		$WidgetInfo = $moduleModel->getWidgetInfo($wid);
		$RelatedModule = $moduleModel->getRelatedModule($WidgetInfo['tabid']);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('SOURCE', $WidgetInfo['tabid']);
		$viewer->assign('SOURCEMODULE', vtlib\Functions::getModuleName($WidgetInfo['tabid']));
		$viewer->assign('WID', $wid);
		$viewer->assign('WIDGETINFO', $WidgetInfo);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RELATEDMODULES', $RelatedModule);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Edit.tpl', $qualifiedModuleName);
	}
}
