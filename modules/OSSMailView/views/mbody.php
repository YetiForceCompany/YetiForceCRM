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

Class OSSMailView_mbody_View extends Vtiger_Index_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	public function postProcess(Vtiger_Request $request)
	{
		global $GLOBALS;
		$GLOBALS['csrf']['frame-breaker'] = false;
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$content = $recordModel->get('content');

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('CONTENT', vtlib\Functions::getHtmlOrPlainText($content));
		$viewer->assign('RECORD', $record);
		$viewer->view('mbody.tpl', 'OSSMailView');
	}
}
