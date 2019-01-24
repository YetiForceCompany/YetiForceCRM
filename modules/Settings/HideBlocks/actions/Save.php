<?php

/**
 * Settings HideBlocks save action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_HideBlocks_Save_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$blockId = $request->getInteger('blockid');
		$enabled = $request->getBoolean('enabled');
		$conditions = $request->getArray('conditions', 'Text');
		$views = $request->getForSql('views');
		$qualifiedModuleName = $request->getModule(false);
		if (!$request->isEmpty('record', true)) {
			$recordId = $request->getInteger('record');
			$recordModel = Settings_HideBlocks_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		} else {
			$recordModel = Settings_HideBlocks_Record_Model::getCleanInstance($qualifiedModuleName);
		}
		$recordModel->set('blockid', $blockId);
		$recordModel->set('enabled', $enabled);
		$recordModel->set('conditions', $conditions);
		$recordModel->set('views', $views);
		$recordModel->save();
		header('location: ' . Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName)->getListViewUrl());
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
