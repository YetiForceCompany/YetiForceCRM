<?php

/**
 * Two factor authentication class for save config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Settings_TwoFactorAuthentication_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$methods = $request->getByType('methods', 'Alnum');
		$config = new \App\ConfigFile('security');
		$config->set('USER_AUTHY_MODE', $methods);
		$config->create();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false))
		]);
		$response->emit();
	}
}
