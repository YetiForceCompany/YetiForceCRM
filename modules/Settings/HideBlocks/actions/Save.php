<?php

/**
 * Settings HideBlocks save action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_HideBlocks_Save_Action extends Settings_Vtiger_Index_Action
{

	public function process(\App\Request $request)
	{
		$recordId = $request->get('record');
		$blockId = $request->get('blockid');
		$enabled = $request->get('enabled');
		$conditions = $request->get('conditions');
		$views = $request->get('views');
		$qualifiedModuleName = $request->getModule(false);
		if ($recordId) {
			$recordModel = Settings_HideBlocks_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		} else {
			$recordModel = Settings_HideBlocks_Record_Model::getCleanInstance($qualifiedModuleName);
		}
		$recordModel->set('blockid', $blockId);
		$recordModel->set('enabled', $enabled);
		$recordModel->set('conditions', $conditions);
		$recordModel->set('views', $views);
		$recordModel->save();
		header("Location: " . Settings_HideBlocks_Module_Model::getListViewUrl());
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
