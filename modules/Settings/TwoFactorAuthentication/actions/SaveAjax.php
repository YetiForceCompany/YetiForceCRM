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
		if (!in_array($methods, Users_Totp_Authmethod::ALLOWED_USER_AUTHY_MODE)) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $methods, 406);
		}
		$config = new \App\Configurator('security');
		$config->set('USER_AUTHY_MODE', $methods);
		$config->save();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false))
		]);
		$response->emit();
	}
}
