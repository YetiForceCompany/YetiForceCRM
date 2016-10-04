<?php

/**
 * List notifications
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_List_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$listRoles = Settings_Roles_Record_Model::getAll();
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('LIST_ROLES', $listRoles);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('List.tpl', $qualifiedModuleName);
	}

	public function getBreadcrumbTitle(Vtiger_Request $request)
	{
		return vtranslate('LBL_NOTIFICATIONS');
	}
}
