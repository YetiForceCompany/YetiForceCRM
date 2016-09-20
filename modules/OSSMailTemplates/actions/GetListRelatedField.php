<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSMailTemplates_GetListRelatedField_Action extends Vtiger_Action_Controller
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
		$tplModule = $request->get('tpl_module');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$output = $moduleModel->getListFiledOfRelatedModule($tplModule);
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
