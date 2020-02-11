<?php

/**
 * YetiForce register action class file.
 *
 * @package   Modules
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

/**
 * Class for YetiForce registration actions.
 */
class Settings_YetiForce_Register_Action extends Settings_Vtiger_Save_Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Class constructor, expose public action methods.
	 */
	public function __construct()
	{
		$this->exposeMethod('serial');
		$this->exposeMethod('online');
		$this->exposeMethod('check');
		parent::__construct();
	}

	/**
	 * Save serial provided by administrator.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \Exception
	 */
	public function serial(App\Request $request)
	{
		$serial = $request->getByType('key', 'Alnum');
		$responseType = 'success';
		$response = new Vtiger_Response();
		$result = true;
		$message = App\Language::translate('LBL_REGISTERED', $request->getModule(false));
		if (!\App\YetiForce\Register::verifySerial($serial) || !\App\YetiForce\Register::setSerial($serial)) {
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

	/**
	 * Save companies data and send it to YetiForce for instance registration.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public function online(App\Request $request)
	{
		$responseType = 'success';
		$response = new Vtiger_Response();
		$result = true;
		$message = App\Language::translate('LBL_REGISTERED', $request->getModule(false));
		if (!\App\Company::registerOnline($request->getByType('companies', 'Text'))) {
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

	/**
	 * Check register status.
	 *
	 * @param \App\Request $request
	 */
	public function check(App\Request $request)
	{
		$status = \App\YetiForce\Register::check(true);
		$label = 'LBL_REGISTRATION_PENDING';
		switch ($status) {
			case 3:
				$label = 'LBL_REGISTRATION_COMPANY_DETAILS_VARY';
				break;
			case 0:
			case 4:
				$label = 'ERR_OCCURRED_CHECK_LOGS';
				break;
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => 1 === $status,
			'message' => App\Language::translate($label, $request->getModule(false)),
		]);
		$response->emit();
	}
}
