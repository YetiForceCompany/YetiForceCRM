<?php

/**
 * Get mails adress class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_GetMail_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleName)) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$sourceRecord = $request->get('sourceRecord');
		$sourceModule = $request->get('sourceModule');
		$maxEmails = $request->get('maxEmails');

		$emails = [];
		$emailFields = OSSMailScanner_Record_Model::getEmailSearch($sourceModule);
		$recordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
		$name = $recordModel->getName();
		foreach ($emailFields as &$emailField) {
			$email = $recordModel->get($emailField['fieldname']);
			if (!empty($email)) {
				$emails[] = [
					'name' => $name,
					'fieldlabel' => vtranslate($emailField['fieldlabel'], $emailField['name']),
					'email' => $email
				];
				if ($maxEmails == 1) {
					break;
				}
			}
		}

		if (count($emails) > 1) {
			$viewController = new Vtiger_Index_View();
			$viewer = $viewController->getViewer($request);
			$viewer->assign('EMAILS', $emails);
			$viewer->view('GetMails.tpl', $moduleName);
		} else {
			$email = '';
			if (count($emails) == 1) {
				$email = $emails[0]['email'];
			}
			$response = new Vtiger_Response();
			$response->setResult($email);
			$response->emit();
		}
	}
}
