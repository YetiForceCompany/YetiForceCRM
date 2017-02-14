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

class Settings_HideBlocks_Save_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
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
		$returnUrl = $recordModel->getDetailViewUrl();
		header("Location: " . Settings_HideBlocks_Module_Model::getListViewUrl());
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
