<?php

/**
 * Content of List 
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_ListContent_View extends Settings_Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$roleId = $request->get('roleId');
		$listContent = Settings_Notifications_Module_Model::getListContent($roleId);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('LIST_CONTENT', $listContent);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('ListContent.tpl', $qualifiedModuleName);
	}
}
