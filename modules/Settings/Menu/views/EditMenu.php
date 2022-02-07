<?php

/**
 * Settings menu EditMenu view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Menu_EditMenu_View extends Settings_Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$id = $request->getInteger('id');
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', Settings_Menu_Module_Model::getInstance());
		$viewer->assign('RECORD', Settings_Menu_Record_Model::getInstanceById($id));
		$viewer->assign('ICONS_LABEL', Settings_Menu_Record_Model::getIcons());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ID', $id);
		$viewer->view('EditMenu.tpl', $qualifiedModuleName);
	}
}
