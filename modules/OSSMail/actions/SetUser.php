<?php

/**
 * OSSMail SetUser action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMail_SetUser_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		if (!Users_Privileges_Model::isPermitted('OSSMail')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$user = $request->get('user');
		$_SESSION['AutoLoginUser'] = $user;
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
