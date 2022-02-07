<?php

/**
 * Two factor authentication class for save config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Settings_TwoFactorAuthentication_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$methods = $request->getByType('methods', 'Alnum');
		$ipAddresses = array_filter($request->getArray('ip', 'Text'));
		$config = new \App\ConfigFile('security');
		$config->set('USER_AUTHY_MODE', $methods);
		$config->set('whitelistIp2fa', $ipAddresses);
		$config->create();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false))
		]);
		$response->emit();
	}
}
