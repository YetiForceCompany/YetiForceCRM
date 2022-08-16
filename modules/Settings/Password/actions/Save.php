<?php

/**
 * Settings Password save action class.
 *
 * @package Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Password_Save_Action extends Settings_Vtiger_Index_Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('encryption');
		$this->exposeMethod('checkEncryptionStatus');
		$this->exposeMethod('pass');
		$this->exposeMethod('pwned');
	}

	/**
	 * Action change configuration for pwned.
	 *
	 * @param \App\Request $request
	 */
	public function pwned(App\Request $request)
	{
		$osm = new \App\ConfigFile('module', 'Users');
		$osm->set('pwnedPasswordProvider', $request->getByType('vale', 'Text'));
		$osm->create();
		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_CHANGES_SAVED')]);
		$response->emit();
	}

	/**
	 * Action change configuration for password.
	 *
	 * @param \App\Request $request
	 */
	public function pass(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$type = $request->getByType('type', 'Alnum');
		if (\in_array($type, ['min_length', 'max_length', 'change_time', 'lock_time', 'pwned_time'])) {
			$vale = $request->getInteger('vale');
		} else {
			$vale = $request->getBoolean('vale') ? 'true' : 'false';
		}
		if (Settings_Password_Record_Model::validation($type, $vale)) {
			Settings_Password_Record_Model::setPassDetail($type, $vale);
			$resp = \App\Language::translate('LBL_SAVE_OK', $moduleName);
		} else {
			$resp = \App\Language::translate('LBL_ERROR', $moduleName);
		}
		$response = new Vtiger_Response();
		$response->setResult($resp);
		$response->emit();
	}

	/**
	 * Validation before saving.
	 *
	 * @param App\Request $request
	 */
	public function checkEncryptionStatus(App\Request $request)
	{
		$target = $request->getInteger('target', null);
		if (null === $target) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||target', 406);
		}
		$instance = \App\Encryption::getInstance($target);
		$message = '';
		if ($instance->isReady()) {
			$message = App\Language::translate('LBL_ENCRYPTION_WAITING', $request->getModule(false));
		} elseif ($instance->isRunning()) {
			$message = App\Language::translate('LBL_ENCRYPTION_RUN', $request->getModule(false));
		}

		$response = new \Vtiger_Response();
		$response->setResult(['result' => empty($message), 'message' => $message]);
		$response->emit();
	}

	/**
	 * Action to set password and method for encryption.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function encryption(App\Request $request)
	{
		$method = $request->isEmpty('methods') ? '' : $request->getByType('methods', 'Text');
		$vector = $request->getRaw('vector');
		$pass = $request->getRaw('password');
		$target = $request->getInteger('target', null);
		if (!$method) {
			$vector = $pass = '';
		}
		if ($method && !\in_array($method, \App\Encryption::getMethods())) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||methods', 406);
		}
		if ($method && \strlen($vector) !== App\Encryption::getLengthVector($method)) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||password', 406);
		}
		if (null === $target) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||target', 406);
		}
		$instance = clone \App\Encryption::getInstance($target);
		$instance->set('method', $method)
			->set('vector', $vector)
			->set('pass', $pass)
			->set('target', $target);
		$response = new \Vtiger_Response();
		$encryption = $instance->encrypt('test', true);
		if (!$method) {
			$response->setResult(App\Language::translate('LBL_DISABLE_ENCRYPTION', $request->getModule(false)));
			$instance->reload($method);
		} elseif (empty($encryption) || 'test' === $encryption) {
			$response->setResult(App\Language::translate('LBL_NO_REGISTER_ENCRYPTION', $request->getModule(false)));
		} else {
			$instance->reload($method);
			$response->setResult(App\Language::translate('LBL_REGISTER_ENCRYPTION', $request->getModule(false)));
		}
		$response->emit();
	}
}
