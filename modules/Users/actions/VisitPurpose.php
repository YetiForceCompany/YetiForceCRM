<?php

/**
 * Visit purpose when logging in as an administrator.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Request visit purpose when logging in as an administrator - action.
 */
class Users_VisitPurpose_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!(\App\Process::hasEvent('showVisitPurpose')) && !(\App\Process::hasEvent('showSuperUserVisitPurpose'))) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$userModel = \App\User::getCurrentUserModel();
		$baseId = \App\User::getCurrentUserRealId();
		$result = \App\Db::getInstance('log')->createCommand()
			->insert('l_#__users_login_purpose', [
				'userid' => $userModel->getId(),
				'datetime' => date('Y-m-d H:i:s'),
				'purpose' => $request->getByType('visitPurpose', \App\Purifier::TEXT),
				'baseid' => $userModel->getId() !== $baseId ? $baseId : 0,
			])->execute();
		if ($result) {
			if (\App\Process::hasEvent('showSuperUserVisitPurpose')) {
				\App\Session::set('showedModalVisitPurpose', 1);
				\App\Process::removeEvent('showSuperUserVisitPurpose');
			} else {
				\App\Process::removeEvent('showVisitPurpose');
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
