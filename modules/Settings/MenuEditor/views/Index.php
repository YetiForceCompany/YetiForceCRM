<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MenuEditor_Index_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$allModelsList = Vtiger_Menu_Model::getAll(true);
		$menuModelStructure = Vtiger_MenuStructure_Model::getInstanceFromMenuList($allModelsList);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		
		$viewer = $this->getViewer($request);
		$viewer->assign('ALL_MODULES', $menuModelStructure->getMore());
		$viewer->assign('SELECTED_MODULES', $menuModelStructure->getTop());
		$viewer->assign('MODULE_NAME', $moduleName);
		
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
