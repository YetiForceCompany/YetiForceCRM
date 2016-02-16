<?php
/**
 *
 * @package YetiForce.actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

class OSSMailTemplates_GetTemplates_Action extends Vtiger_Action_Controller
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
		$output = array();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$output = $moduleModel->getTemplates();

		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
