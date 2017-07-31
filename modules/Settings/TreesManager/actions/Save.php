<?php

/**
 * Settings TreesManager save action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_TreesManager_Save_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$currentUser->isAdminUser()) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Save tree
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
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
		$recordModel->set('share', $request->get('share'));
		$recordModel->set('replace', $replace);
		$recordModel->save();
		header('Location: ' . $moduleModel->getListViewUrl());
	}
}
