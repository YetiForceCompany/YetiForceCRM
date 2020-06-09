<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Roles_Index_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$rootRole = Settings_Roles_Record_Model::getBaseRole();
		$allRoles = Settings_Roles_Record_Model::getAll();

		$viewer->assign('ROOT_ROLE', $rootRole);
		$viewer->assign('ROLES', $allRoles);
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('TYPE', $request->getByType('type', 'Alnum'));
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
