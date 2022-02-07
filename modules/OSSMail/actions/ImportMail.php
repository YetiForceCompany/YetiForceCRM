<?php

/**
 * OSSMail ImportMail action class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMail_ImportMail_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$usersPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$usersPrivilegesModel->hasModulePermission($request->getModule()) || !$usersPrivilegesModel->hasModuleActionPermission('OSSMailView', 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool|void
	 */
	public function process(App\Request $request)
	{
		$uid = $request->getInteger('uid');
		$account = OSSMail_Record_Model::getAccountByHash($request->getForSql('rcId'));
		if (!$account) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED24', 406);
		}
		$folder = \App\Utils::convertCharacterEncoding($request->getRaw('folder'), 'UTF7-IMAP', 'UTF-8');
		$folder = \App\Purifier::decodeHtml(\App\Purifier::purifyByType($folder, 'Text'));
		$scannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$mailScanMail = $scannerModel->manualScanMail($uid, $folder, $account);
		$return = false;
		if (isset($mailScanMail['CreatedEmail'])) {
			$return = $mailScanMail['CreatedEmail']['mailViewId'];
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}
