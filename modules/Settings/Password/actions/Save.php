<?php

/**
 * Settings Password save action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$this->exposeMethod('pass');
	}

	/**
	 * Action change configuration for password.
	 *
	 * @param \App\Request $request
	 */
	public function pass(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$type = $request->getByType('type', 'Alnum');
		if (in_array($type, ['min_length', 'max_length', 'change_time', 'lock_time'])) {
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
	 * Action to set password and method for encryption.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function encryption(\App\Request $request)
	{
		$method = $request->isEmpty('methods') ? '' : $request->getByType('methods', 'Text');
		if ($method && !in_array($method, \App\Encryption::getMethods())) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||methods', 406);
		}
		$password = $request->getRaw('password');
		if ($method && strlen($password) !== App\Encryption::getLengthVector($method)) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||password', 406);
		}
		\AppConfig::set('securityKeys', 'encryptionMethod', $method);
		$instance = new App\Encryption();
		$instance->set('method', $method);
		$instance->set('vector', $password);
		$instance->set('pass', \AppConfig::securityKeys('encryptionPass'));
		$response = new Vtiger_Response();
		$encryption = $instance->encrypt('test');
		if (empty($encryption) || $encryption === 'test') {
			$response->setResult(App\Language::translate('LBL_NO_REGISTER_ENCRYPTION', $request->getModule(false)));
		} else {
			(new App\BatchMethod(['method' => '\App\Encryption::recalculatePasswords', 'params' => [$method, $password]]))->save();
			$response->setResult(App\Language::translate('LBL_REGISTER_ENCRYPTION', $request->getModule(false)));
		}
		$response->emit();
	}
}
