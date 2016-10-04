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

class Settings_Menu_CreateMenu_View extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
	}

	public function step1(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$settingsModel = Settings_Menu_Module_Model::getInstance();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $settingsModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ROLEID', $roleId);
		$viewer->view('CreateMenuStep1.tpl', $qualifiedModuleName);
	}

	public function step2(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$type = $request->get('mtype');
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', Settings_Menu_Module_Model::getInstance());
		$viewer->assign('RECORD', Settings_Menu_Record_Model::getCleanInstance());
		$viewer->assign('ICONS_LABEL', Settings_Menu_Record_Model::getIcons());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TYPE', $type);
		$viewer->view('CreateMenuStep2.tpl', $qualifiedModuleName);
	}
}
