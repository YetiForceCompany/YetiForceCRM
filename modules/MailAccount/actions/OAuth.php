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

	// /** {@inheritdoc} */
	// public function checkPermission(App\Request $request)
	// {
	// 	// parent::checkPermission($request);
	// 	$this->recordModel = \Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
	// 	if (!$this->recordModel->isEditable()) {
	// 		throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
	// 	}
	// }

	// /** {@inheritdoc} */
	// public function validateRequest(App\Request $request)
	// {
	// 	$request->validateReadAccess();
	// }

	// /**
	//  * Main process.
	//  *
	//  * @param \App\Request $request
	//  *
	//  * @throws \App\Exceptions\AppException
	//  *
	//  * @return bool|void
	//  */
	// public function process(App\Request $request)
	// {
	// 	$recordId = $this->recordModel->getId();
	// 	$expireDate = date('Y-m-d H:i:s', strtotime('+30 minutes'));
	// 	$mailAccount = \App\Mail\Account::getInstanceById($recordId);
	// 	$oauthProvider = $mailAccount->getOAuthProvider();
	// 	$state = $oauthProvider->getState();
	// 	$url = $oauthProvider->getAuthorizationUrl();

	// 	$hash = sha1($state);
	// 	\App\Session::set("OAuth.State.{$hash}", [
	// 		'state' => $state,
	// 		'recordId' => $recordId,
	// 		'redirectUri' => \App\Config::main('site_URL') . $this->recordModel->getDetailViewUrl()
	// 	]);

	// 	header('location: ' . $url);
	// 	// $response = new Vtiger_Response();
	// 	// try {
	// 	// 	$ilon = $request->getByType('ilon', 'float');
	// 	// 	$ilat = $request->getByType('ilat', 'float');
	// 	// 	$routingConnector = \App\Map\Routing::getInstance();
	// 	// 	$routingConnector->setStart($request->getByType('flat', 'float'), $request->getByType('flon', 'float'));
	// 	// 	if (!empty($ilon) && !empty($ilat)) {
	// 	// 		foreach ($ilon as $key => $lon) {
	// 	// 			$routingConnector->addIndirectPoint($ilat[$key], $lon);
	// 	// 		}
	// 	// 	}
	// 	// 	$routingConnector->setEnd($request->getByType('tlat', 'float'), $request->getByType('tlon', 'float'));
	// 	// 	$routingConnector->calculate();
	// 	// 	$response->setResult([
	// 	// 		'geoJson' => $routingConnector->getGeoJson(),
	// 	// 		'properties' => [
	// 	// 			'description' => App\Purifier::purifyHtml($routingConnector->getDescription()),
	// 	// 			'traveltime' => $routingConnector->getTravelTime(),
	// 	// 			'distance' => $routingConnector->getDistance(),
	// 	// 		],
	// 	// 	]);
	// 	// } catch (\Throwable $th) {
	// 	// 	\App\Log::error($th->getMessage(), __CLASS__);
	// 	// 	$response->setException($th);
	// 	// }
	// 	// $response->emit();
	// }
}
