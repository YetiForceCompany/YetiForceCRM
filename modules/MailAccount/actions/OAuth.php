<?php

/**
 * OAuth authorization action file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * OAuth authorization action class.
 */
class MailAccount_OAuth_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$this->recordModel = \Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$permission = $this->recordModel->isEditable() && !$this->recordModel->isReadOnly()
			&& ($mailServer = \App\Mail\Server::getInstanceById((int) $this->recordModel->get('mail_server_id')))
			&& $mailServer->isViewable() && $mailServer->isOAuth();
		if (!$permission) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$mailAccount = \App\Mail\Account::getInstanceById($this->recordModel->getId());
		$oauthProvider = $mailAccount->getOAuthProvider();
		$state = $oauthProvider->getState();
		$url = $oauthProvider->getAuthorizationUrl(['login_hint' => $mailAccount->getLogin()]);

		$hash = sha1($state);
		\App\Session::set("OAuth.State.{$hash}", [
			'state' => $state,
			'recordId' => $this->recordModel->getId(),
			'redirectUri' => \App\Config::main('site_URL') . $this->recordModel->getDetailViewUrl()
		]);

		header('location: ' . $url);
		exit;
	}

	/** {@inheritdoc} */
	public function validateRequest(App\Request $request)
	{
		$request->validateReadAccess();
	}
}
