<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class Vtiger_TreePopup_View extends Vtiger_Footer_View {
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	/**
	 * Function returns the module name for which the popup should be initialized
	 * @param Vtiger_request $request
	 * @return <String>
	 */
	function getModule(Vtiger_request $request) {
		$moduleName = $request->getModule();
		return $moduleName;
	}

	function process (Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $this->getModule($request);
		$template = $request->get('template');
		$srcField = $request->get('src_field');
		$srcRecord = $request->get('src_record');
		if(!empty($template)) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($template);
		} else {
			Vtiger_Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $moduleName));
		}
		if(!$recordModel)
			Vtiger_Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $moduleName));
		
		$tree = $recordModel->getTree();
		$viewer->assign('TREE', Zend_Json::encode($tree));
		$viewer->assign('SRC_RECORD', $srcRecord);
		$viewer->assign('SRC_FIELD', $srcField);
		$viewer->assign('TEMPLATE', $template);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('TRIGGER_EVENT_NAME', $request->get('triggerEventName'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TreePopup.tpl', $moduleName);
	}

	function postProcess(Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $this->getModule($request);
		$viewer->assign('FOOTER_SCRIPTS',$this->getFooterScripts($request));
		$viewer->view('PopupFooter.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getFooterScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'libraries.jquery.jquery_windowmsg',
			'~libraries/jquery/jstree/jstree.min.js',
			'modules.Vtiger.resources.TreePopup',
			"modules.$moduleName.resources.TreePopup",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);
		$moduleName = $request->getModule();
		$cssFileNames = array(
			'~libraries/jquery/jstree/themes/default/style.css',
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
