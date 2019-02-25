<?php

/**
 * Settings TreesManager save action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_Save_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Save tree.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$name = $request->getByType('name', 'Text');
		$tree = $request->getArray('tree', 'Text');
		$replace = $request->getMultiDimensionArray('replace', [['old' => ['Integer'], ['new' => ['Integer']]]]);
		$templatemodule = $request->getInteger('templatemodule');
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		if (!$request->isEmpty('record', true)) {
			$recordModel = Settings_TreesManager_Record_Model::getInstanceById($request->getInteger('record'));
		} else {
			$recordModel = new Settings_TreesManager_Record_Model();
		}
		$recordModel->set('name', $name);
		$recordModel->set('module', $templatemodule);
		$recordModel->set('tree', $tree);
		$recordModel->set('share', $request->getArray('share', 'Integer'));
		$recordModel->set('replace', $replace);
		$recordModel->save();
		header('location: ' . $moduleModel->getListViewUrl());
	}
}
