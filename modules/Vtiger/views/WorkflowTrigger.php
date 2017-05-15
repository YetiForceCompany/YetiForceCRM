<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Vtiger_WorkflowTrigger_View extends Vtiger_IndexAjax_View
{

	public function checkPermission(\App\Request $request)
	{
		if (!(Users_Privileges_Model::isPermitted($request->getModule(), 'WorkflowTrigger', $request->get('record')))) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		vimport('~~modules/com_vtiger_workflow/include.php');
		$workflows = (new VTWorkflowManager(PearDatabase::getInstance()))->getWorkflowsForModule($moduleName, VTWorkflowManager::$TRIGGER);
		foreach ($workflows as $id => $workflow) {
			if (!$workflow->evaluate(Vtiger_Record_Model::getInstanceById($record))) {
				unset($workflows[$id]);
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('WORKFLOWS', $workflows);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('WorkflowTrigger.tpl', $moduleName);
	}
}
