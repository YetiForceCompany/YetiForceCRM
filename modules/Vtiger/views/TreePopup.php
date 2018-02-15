<?php

/**
 * Vtiger TreePopup view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_TreePopup_View extends Vtiger_Footer_View
{
	public function checkPermission(\App\Request $request)
	{
		if (!$request->isEmpty('src_record') && !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('src_record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Function returns the module name for which the popup should be initialized.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getModule(\App\Request $request)
	{
		$moduleName = $request->getModule();

		return $moduleName;
	}

	/**
	 * Tree in popup.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $this->getModule($request);
		$template = $request->getInteger('template');
		$srcField = $request->getByType('src_field', 1);
		$value = $request->get('value');
		$type = false;
		if (!empty($template)) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($template);
		} else {
			throw new \App\Exceptions\AppException(\App\Language::translate('ERR_TREE_NOT_FOUND', $moduleName));
		}
		if (!$recordModel) {
			throw new \App\Exceptions\AppException(\App\Language::translate('ERR_TREE_NOT_FOUND', $moduleName));
		}
		if ($request->get('multiple')) {
			$type = 'category';
		}
		$tree = $recordModel->getTree($type, $value);
		$viewer->assign('TREE', \App\Json::encode($tree));
		if (!$request->isEmpty('src_record')) {
			$viewer->assign('SRC_RECORD', $request->getInteger('src_record'));
		}
		$viewer->assign('SRC_FIELD', $srcField);
		$viewer->assign('TEMPLATE', $template);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('IS_MULTIPLE', $request->get('multiple'));
		$viewer->assign('TRIGGER_EVENT_NAME', $request->getByType('triggerEventName', 2));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TreePopup.tpl', $moduleName);
	}

	public function postProcess(\App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $this->getModule($request);
		$viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
		$viewer->view('PopupFooter.tpl', $moduleName);
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
		$moduleName = $request->getModule();
		$jsFileNames = ['~libraries/jstree/dist/jstree.js'];
		if ($request->get('multiple')) {
			$jsFileNames[] = '~layouts/resources/libraries/jstree.category.js';
			$jsFileNames[] = '~layouts/resources/libraries/jstree.checkbox.js';
		}
		$jsFileNames = array_merge($jsFileNames, [
			'libraries.js.jquery_windowmsg',
			'~libraries/clockpicker/dist/jquery-clockpicker.js',
			'modules.Vtiger.resources.TreePopup',
			"modules.$moduleName.resources.TreePopup",
		]);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}

	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($cssInstances, $headerCssInstances);

		return $headerCssInstances;
	}

	protected function showBodyHeader()
	{
		return false;
	}
}
