<?php
/**
 * Two factor authentication class for config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Settings_TwoFactorAuthentication_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('AVAILABLE_METHODS', Users_Totp_Authmethod::ALLOWED_USER_AUTHY_MODE);
		$viewer->assign('USER_AUTHY_MODE', App\Config::security('USER_AUTHY_MODE'));
		$viewer->view('Index.tpl', $request->getModule(false));
	}
}
