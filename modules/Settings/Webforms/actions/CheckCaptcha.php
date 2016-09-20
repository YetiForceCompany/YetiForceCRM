<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

// Switch the working directory to base
chdir(dirname(__FILE__) . '/../../../..');

include_once 'include/http/Response.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/recaptcha/recaptchalib.php';
include_once 'modules/Webforms/config.captcha.php';

class Webform_CheckCaptcha
{

	protected $PUBLIC_KEY = false;
	protected $PRIVATE_KEY = false;

	/**
	 * Function to intialize captch keys
	 */
	public function __construct()
	{
		global $captchaConfig;
		$this->PUBLIC_KEY = $captchaConfig['VTIGER_RECAPTCHA_PUBLIC_KEY'];
		$this->PRIVATE_KEY = $captchaConfig['VTIGER_RECAPTCHA_PRIVATE_KEY'];
	}

	public function checkCaptchaNow($request)
	{
		$request = AppRequest::init();
		// to store the response from reCAPTCHA
		$resp = null;

		if ($request->get('recaptcha_response_field')) {
			$resp = recaptcha_check_answer($this->PRIVATE_KEY, $_SERVER['REMOTE_ADDR'], $request->get('recaptcha_challenge_field'), $request->get('recaptcha_response_field'));

			if ($resp->is_valid) {
				$this->sendResponse(true, $request->get('callId'));
			} else {
				$this->sendResponse(false, $request->get('callId'));
			}
		} else {
			$this->sendResponse(false, $request->get('callId'));
		}
	}

	protected function sendResponse($success, $callId)
	{
		$response = new Vtiger_Response();
		if ($success)
			$response->setResult(array('success' => true, 'callId' => $callId));
		else
			$response->setResult(array('success' => false, 'callId' => $callId));

		// Support JSONP
		if (!AppRequest::isEmpty('callback')) {
			$callback = AppRequest::get('callback');
			$response->setEmitType('4');
			$response->setEmitJSONP($callback);
			$response->emit();
		} else {
			$response->emit();
		}
	}
}

$webformCheckCaptcha = new Webform_CheckCaptcha;
$webformCheckCaptcha->checkCaptchaNow();
