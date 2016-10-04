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

class Settings_TreesManager_Save_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$currentUser->isAdminUser()) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');
		$name = $request->get('name');
		$tree = $request->get('tree');
		$replace = $request->get('replace');
		$templatemodule = $request->get('templatemodule');

		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		if (!empty($recordId)) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = new Settings_TreesManager_Record_Model();
		}
		$recordModel->set('name', $name);
		$recordModel->set('module', $templatemodule);
		$recordModel->set('tree', $tree);
		$recordModel->set('replace', $replace);
		$recordModel->save();
		header('Location: ' . $moduleModel->getListViewUrl());
	}
}
