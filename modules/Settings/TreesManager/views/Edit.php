<?php

/**
 * Settings TreesManager edit view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_Edit_View extends Settings_Vtiger_Index_View
{

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->get('record');
		$sourceModuleId = '';
		$access = 1;
		if (!empty($record)) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($record);
			$sourceModuleId = $recordModel->get('module');
			$viewer->assign('MODE', 'edit');
			$access = $recordModel->get('access');
		} else {
			$recordModel = new Settings_TreesManager_Record_Model();
			$viewer->assign('MODE', '');
			$recordModel->set('lastId', 0);
		}
		$tree = $recordModel->getTree();
		$viewer->assign('TREE', \App\Json::encode($tree));
		$viewer->assign('LAST_ID', $recordModel->get('lastId'));
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ACCESS', $access);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('SOURCE_MODULE', $sourceModuleId);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}

	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = [
			'libraries.js.jstree.jstree',
			"modules.Settings.$moduleName.resources.Edit",
		];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'libraries.js.jstree.themes.proton.style',
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return array_merge($cssInstances, $headerCssInstances);
	}
}
