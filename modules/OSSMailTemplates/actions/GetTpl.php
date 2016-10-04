<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSMailTemplates_GetTpl_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);

		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
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
