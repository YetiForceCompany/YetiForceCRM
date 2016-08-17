<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class Mobile_WS_Login extends Mobile_WS_Controller {

	function requireLogin() {
		return false;
	}

	function process(Mobile_API_Request $request) {
		$response = new Mobile_API_Response();

		$username = $request->get('username');
		$password = $request->get('password');

		$current_user = CRMEntity::getInstance('Users');
		$current_user->column_fields['user_name'] = $username;

		if(\includes\Modules::isModuleActive('Mobile') === false) {
			$response->setError(1501, 'Service not available');
			return $response;
		}

		if(!$current_user->doLogin($password)) {

			$response->setError(1210, 'Authentication Failed');

		} else {
			// Start session now
			$sessionid = Mobile_API_Session::init();

			if($sessionid === false) {
				echo "Session init failed $sessionid\n";
			}

			$current_user->id = $current_user->retrieve_user_id($username);
			$current_user->retrieveCurrentUserInfoFromFile($current_user->id);
			$this->setActiveUser($current_user);

			$result = array();
			$result['login'] = array(
				'userid' => $current_user->id,
				'crm_tz' => DateTimeField::getDBTimeZone(),
				'user_tz' => $current_user->time_zone,
				'user_currency' => $current_user->currency_code,
				'session'=> $sessionid,
				'yetiforce_version' => Mobile_WS_Utils::getVtigerVersion(),
				'date_format' => $current_user->date_format, 
				'mobile_module_version' => Mobile_WS_Utils::getVersion()
			);
			$response->setResult($result);

			$this->postProcess($response);
		}
		return $response;
	}

	function postProcess(Mobile_API_Response $response) {
		return $response;
	}
}
