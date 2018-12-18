<?php

/**
 * YetiForce register action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class Settings_YetiForce_Register_Action extends Settings_Vtiger_Save_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		$this->exposeMethod('offline');
		$this->exposeMethod('online');
		parent::__construct();
	}

	public function offline(\App\Request $request)
	{
		$serial = $request->getByType('key', 'Alnum');
		$responseType = 'success';
		$response = new Vtiger_Response();
		$result = true;
		$message = App\Language::translate('LBL_REGISTERED', $request->getModule(false));
		if (!\App\YetiForce\Register::verifySerial($serial) || !\App\Company::setOfflineSerial($serial)) {
			$result = false;
			$message = App\Language::translate('LBL_INVALID_OFFLINE_KEY', $request->getModule(false));
			$responseType = 'error';
		}
		$response->setResult([
			'success' => $result,
			'message' => $message,
			'type' => $responseType
		]);
		$response->emit();
	}

	public function online(\App\Request $request)
	{
		$responseType = 'success';
		$response = new Vtiger_Response();
		$result = true;
		$message = App\Language::translate('LBL_REGISTERED', $request->getModule(false));
		if (!\App\Company::registerOnline($request)) {
			$result = false;
			$message = App\Language::translate('LBL_ONLINE_REGISTRATION_FAILED', $request->getModule(false));
			$responseType = 'error';
		}
		$response->setResult([
			'success' => $result,
			'message' => $message,
			'type' => $responseType
		]);
		$response->emit();
	}
}
