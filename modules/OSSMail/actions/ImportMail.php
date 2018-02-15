<?php

/**
 * OSSMail ImportMail action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function checkPermission(\App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$scannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$mailScanMail = $scannerModel->manualScanMail($request->get('params'));
		$return = false;
		if ($mailScanMail['CreatedEmail']) {
			$return = $mailScanMail['CreatedEmail']['mailViewId'];
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}
