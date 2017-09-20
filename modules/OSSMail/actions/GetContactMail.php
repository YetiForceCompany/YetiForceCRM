<?php

/**
 * OSSMail get contact mail action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMail_GetContactMail_Action extends Vtiger_Action_Controller
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$recordId = $request->getInteger('ids');
		if (!$recordId) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		if (!\App\Privilege::isPermitted($request->getByType('mod', 1), 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function process(\App\Request $request)
	{
		$ids = $request->get('ids');
		$mod = $request->getByType('mod', 1);
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
