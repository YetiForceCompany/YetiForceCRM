<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_TreesManager_Edit_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
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

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'libraries.jquery.jstree.jstree',
			"modules.Settings.$moduleName.resources.Edit",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = array(
			'libraries.jquery.jstree.themes.proton.style',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return array_merge($cssInstances, $headerCssInstances);
	}
}
