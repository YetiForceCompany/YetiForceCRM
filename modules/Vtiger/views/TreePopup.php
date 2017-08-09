<?php

/**
 * Vtiger TreePopup view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Vtiger_TreePopup_View extends Vtiger_Footer_View
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function returns the module name for which the popup should be initialized
	 * @param \App\Request $request
	 * @return string
	 */
	public function getModule(\App\Request $request)
	{
		$moduleName = $request->getModule();
		return $moduleName;
	}

	/**
	 * Tree in popup
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $this->getModule($request);
		$template = $request->get('template');
		$srcField = $request->get('src_field');
		$srcRecord = $request->get('src_record');
		$value = $request->get('value');
		$type = false;
		if (!empty($template)) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($template);
		} else {
			throw new \Exception\AppException(\App\Language::translate('ERR_TREE_NOT_FOUND', $moduleName));
		}
		if (!$recordModel) {
			throw new \Exception\AppException(\App\Language::translate('ERR_TREE_NOT_FOUND', $moduleName));
		}
		if ($request->get('multiple')) {
			$type = 'category';
		}
		$tree = $recordModel->getTree($type, $value);
		$viewer->assign('TREE', \App\Json::encode($tree));
		$viewer->assign('SRC_RECORD', $srcRecord);
		$viewer->assign('SRC_FIELD', $srcField);
		$viewer->assign('TEMPLATE', $template);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('IS_MULTIPLE', $request->get('multiple'));
		$viewer->assign('TRIGGER_EVENT_NAME', $request->get('triggerEventName'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TreePopup.tpl', $moduleName);
	}

	public function postProcess(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $this->getModule($request);
		$viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
		$viewer->view('PopupFooter.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param \App\Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$jsFileNames = array('~libraries/jquery/jstree/jstree.js');
		if ($request->get('multiple')) {
			$jsFileNames[] = '~libraries/jquery/jstree/jstree.category.js';
			$jsFileNames[] = '~libraries/jquery/jstree/jstree.checkbox.js';
		}
		$jsFileNames = array_merge($jsFileNames, array(
			'libraries.jquery.jquery_windowmsg',
			'~libraries/jquery/clockpicker/jquery-clockpicker.js',
			'modules.Vtiger.resources.TreePopup',
			"modules.$moduleName.resources.TreePopup",
		));

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = array(
			'~libraries/jquery/jstree/themes/proton/style.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($cssInstances, $headerCssInstances);
		return $headerCssInstances;
	}

	protected function showBodyHeader()
	{
		return false;
	}
}
