<?php

/**
 * MailClient SaveAjax action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class Settings_MailClient_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateClient');
	}

	/**
	 * Function updates smtp configuration.
	 *
	 * @param \App\Request $request
	 */
	public function updateClient(App\Request $request)
	{
		$data = [
			'validate_cert' => $request->getInteger('validate_cert'),
			'add_connection_type' => $request->getInteger('add_connection_type'),
			'default_host' => $request->getByType('default_host', 'Text'),
			'default_port' => $request->getInteger('default_port'),
			'smtp_server' => $request->getByType('smtp_server', 'Text'),
			'smtp_port' => $request->getInteger('smtp_port'),
			'language' => $request->getByType('language', 'Text'),
			'username_domain' => $request->getByType('username_domain', 'Text'),
			'ip_check' => $request->getInteger('ip_check'),
			'enable_spellcheck' => $request->getInteger('enable_spellcheck'),
			'identities_level' => $request->getInteger('identities_level'),
			'session_lifetime' => $request->getInteger('session_lifetime'),
		];
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_MailClient_Record_Model::getInstanceById($request->getInteger('record'));
		} else {
			$recordModel = Settings_MailClient_Record_Model::getCleanInstance();
		}
		foreach ($data as $key => $value) {
			$recordModel->set($key, $value);
		}
		if ($recordModel->save()) {
			$result = ['success' => true, 'url' => $recordModel->getDetailViewUrl()];
		} else {
			$result = ['success' => false, 'message' => 'Error'];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
