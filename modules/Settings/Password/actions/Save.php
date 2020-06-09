<?php

/**
 * Settings Password save action class.
 *
 * @package Settings
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		$password = $request->getRaw('password');
		if (!$method) {
			$vector = $password = '';
		}
		if ($method && !\in_array($method, \App\Encryption::getMethods())) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||methods', 406);
		}
		if ($method && \strlen($vector) !== App\Encryption::getLengthVector($method)) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||password', 406);
		}
		\App\Config::set('securityKeys', 'encryptionMethod', $method);
		$instance = new App\Encryption();
		$instance->set('method', $method);
		$instance->set('vector', $vector);
		$instance->set('pass', $password);
		$response = new Vtiger_Response();
		$encryption = $instance->encrypt('test');
		if (!$method) {
			$response->setResult(App\Language::translate('LBL_DISABLE_ENCRYPTION', $request->getModule(false)));
			\App\BatchMethod::deleteByMethod('\App\Encryption::recalculatePasswords');
			(new App\BatchMethod(['method' => '\App\Encryption::recalculatePasswords', 'params' => [$method, $password, $vector]]))->save();
		} elseif (empty($encryption) || 'test' === $encryption) {
			$response->setResult(App\Language::translate('LBL_NO_REGISTER_ENCRYPTION', $request->getModule(false)));
		} else {
			\App\BatchMethod::deleteByMethod('\App\Encryption::recalculatePasswords');
			(new App\BatchMethod(['method' => '\App\Encryption::recalculatePasswords', 'params' => [$method, $password, $vector]]))->save();
			$response->setResult(App\Language::translate('LBL_REGISTER_ENCRYPTION', $request->getModule(false)));
		}
		$response->emit();
	}
}
