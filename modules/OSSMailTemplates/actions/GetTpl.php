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

class OSSMailTemplates_GetTpl_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if (!$permission) {
			throw new NoPermittedException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{

		$moduleName = $request->getModule();
		$tplId = $request->get('id');
		$record = $request->get('record_id');
		$selectModule = $request->get('select_module');

		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

		$output = $recordModel->getTemplete($tplId);
		if ($record && $selectModule) {
			$recordModel->findVar($output['content'], 0, $record, $selectModule, 'a', $request);
			$recordModel->findVar($output['content'], 0, $record, $selectModule, 'b', $request);
			$recordModel->findVar($output['content'], 0, $record, $selectModule, 'c', $request);
			$recordModel->findVar($output['content'], 0, $record, $selectModule, 'd', $request);
			$recordModel->findVar($output['content'], 0, $record, $selectModule, 's', $request);
		}
		if ('true' === $request->get('as_var')) {
			$output['content'] = to_html($output['content']);
			return $output;
		} else {
			$response = new Vtiger_Response();
			$response->setResult($output);
			$response->emit();
		}
	}
}
