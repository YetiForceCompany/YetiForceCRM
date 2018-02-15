<?php

/**
 * Settings mail SaveAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Mail_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateUsers');
		$this->exposeMethod('updateConfig');
		$this->exposeMethod('updateSignature');
		$this->exposeMethod('acceptanceRecord');
	}

	public function updateUsers(\App\Request $request)
	{
		$id = $request->get('id');
		$user = $request->get('user');
		Settings_Mail_Autologin_Model::updateUsersAutologin($id, $user);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => App\Language::translate('LBL_SAVED_CHANGES', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function updateConfig(\App\Request $request)
	{
		$name = $request->get('name');
		$val = $request->get('val');
		$type = $request->get('type');
		Settings_Mail_Config_Model::updateConfig($name, $val, $type);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => App\Language::translate('LBL_SAVED_CHANGES', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function updateSignature(\App\Request $request)
	{
		$val = $request->getForHtml('val');
		Settings_Mail_Config_Model::updateConfig('signature', $val, 'signature');
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => App\Language::translate('LBL_SAVED_SIGNATURE', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function acceptanceRecord(\App\Request $request)
	{
		Settings_Mail_Config_Model::acceptanceRecord($request->get('id'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_RECORD_ACCEPTED', $request->getModule(false)),
		]);
		$response->emit();
	}
}
