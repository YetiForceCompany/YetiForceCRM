<?php

/**
 * Settings menu index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Menu_Index_View extends Settings_Vtiger_Index_View
{
	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$roleId = $request->get('roleid');
		if (empty($roleId)) {
			$roleId = 0;
		}
		$settingsModel = Settings_Menu_Record_Model::getCleanInstance();
		$rolesContainMenu = $settingsModel->getRolesContainMenu();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $settingsModel);
		$viewer->assign('ROLES_CONTAIN_MENU', $rolesContainMenu);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ROLEID', $roleId);
		$viewer->assign('DATA', $settingsModel->getAll(filter_var($roleId, FILTER_SANITIZE_NUMBER_INT)));
		$viewer->assign('LASTID', Settings_Menu_Module_Model::getLastId());
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$jsFileNames = [
			'~libraries/jstree/dist/jstree.js',
		];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}

	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$moduleName = $request->getModule();
		$cssFileNames = [
			'~libraries/jstree/dist/themes/default/style.css',
			"modules.Settings.$moduleName.Index",
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($cssInstances, $headerCssInstances);

		return $headerCssInstances;
	}
}
