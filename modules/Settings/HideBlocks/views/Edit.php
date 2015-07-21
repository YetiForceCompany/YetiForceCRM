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

Class Settings_HideBlocks_Edit_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$mode = '';
		$enabled = 0;
		$views = array();
		$viewer = $this->getViewer($request);
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		if ($recordId) {
			$mode = 'edit';
			$recordModel = Settings_HideBlocks_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
			$enabled = $recordModel->get('enabled');
			if ($recordModel->get('view') != '')
				$views = explode(',', $recordModel->get('view'));
			$viewer->assign('BLOCK_ID', $recordModel->get('blockid'));
		}

		$viewer->assign('MODE', $mode);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('ENABLED', $enabled);
		$viewer->assign('SELECTED_VIEWS', $views);
		$viewer->assign('MODULE', 'HideBlocks');
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('BLOCKS', $moduleModel->getAllBlock());
		$viewer->assign('VIEWS', $moduleModel->getViews());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}
}
