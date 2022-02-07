<?php

/**
 * Settings menu index view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Menu_Index_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$roleId = !$request->isEmpty('roleid') ? $request->getByType('roleid', 'Alnum') : 0;
		$source = ($roleId && false === strpos($roleId, 'H')) ? Settings_Menu_Record_Model::SRC_API : Settings_Menu_Record_Model::SRC_ROLE;
		$settingsModel = Settings_Menu_Record_Model::getCleanInstance();
		$rolesContainMenu = $settingsModel->getRolesContainMenu();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $settingsModel);
		$viewer->assign('ROLES_CONTAIN_MENU', $rolesContainMenu);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ROLEID', $roleId);
		$viewer->assign('DATA', $settingsModel->getAll(filter_var($roleId, FILTER_SANITIZE_NUMBER_INT), $source));
		$viewer->assign('LASTID', Settings_Menu_Module_Model::getLastId());
		$viewer->assign('SOURCE', $source);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/jstree/dist/jstree.js',
		]));
	}

	public function getHeaderCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/jstree/dist/themes/default/style.css',
			'modules.Settings.' . $request->getModule() . '.Index',
		]), parent::getHeaderCss($request));
	}
}
