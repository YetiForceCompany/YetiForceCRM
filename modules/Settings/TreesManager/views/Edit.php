<?php

/**
 * Settings TreesManager edit view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_Edit_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->getInteger('record');
		$sourceModuleId = '';
		$access = 1;
		if (!empty($record)) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($record);
			$sourceModuleId = $recordModel->get('tabid');
			$viewer->assign('MODE', 'edit');
			$access = $recordModel->get('access');
		} else {
			$recordModel = Settings_TreesManager_Record_Model::getCleanInstance();
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
		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/jstree/dist/jstree.js',
			"modules.Settings.{$request->getModule()}.resources.Edit",
		]));
	}

	/** {@inheritdoc} */
	public function getHeaderCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
		]), parent::getHeaderCss($request));
	}
}
