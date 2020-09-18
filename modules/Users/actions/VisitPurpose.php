<?php

/**
 * Visit purpose when logging in as an administrator.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Request visit purpose when logging in as an administrator - action.
 */
class Users_VisitPurpose_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$userModel = \App\User::getCurrentUserModel();
		if (!(\App\Session::get('showVisitPurpose') || (!$userModel->isAdmin() && $userModel->isSuperUser() && !(\App\Session::get('showedModalVisitPurpose')[$userModel->getId()] ?? null)))) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$visitPurpose = $request->getByType('visitPurpose', \App\Purifier::TEXT);
		$userModel = \App\User::getCurrentUserModel();
		$baseId = \App\User::getCurrentUserRealId();
		$result = \App\Db::getInstance('log')->createCommand()
			->insert('l_#__users_login_purpose', [
				'userid' => $userModel->getId(),
				'datetime' => date('Y-m-d H:i:s'),
				'purpose' => $visitPurpose,
				'baseid' => $userModel->getId() !== $baseId ? $baseId : 0,
			])->execute();
		if ($result && !$userModel->isAdmin() && $userModel->isSuperUser()) {
			$userData = \App\Session::get('showedModalVisitPurpose') ?? [];
			$userData[$userModel->getId()] = $userModel->getId();
			\App\Session::set('showedModalVisitPurpose', $userData);
		} elseif ($result) {
			\App\Session::delete('showVisitPurpose');
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
