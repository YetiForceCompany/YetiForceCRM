<?php

/**
 * OSSMail getContactMail action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMail_getContactMail_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleName)) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$ids = $request->get('ids');
		$mod = $request->get('mod');
		$emailFields = [];
		$searchList = OSSMailScanner_Record_Model::getEmailSearch($mod);
		$recordModel = Vtiger_Record_Model::getInstanceById($ids, $mod);
		$name = $recordModel->getName();
		foreach ($searchList as &$emailField) {
			$email = $recordModel->get($emailField['fieldname']);
			if ($email != '') {
				$fieldlabel = \App\Language::translate($emailField['fieldlabel'], $emailField['name']);
				$emailFields[] = array('name' => $name, 'fieldlabel' => $fieldlabel, 'email' => $email);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($emailFields);
		$response->emit();
	}
}
