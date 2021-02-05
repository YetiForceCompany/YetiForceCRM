<?php
/**
 * MailIntegration Activate action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * MailIntegration Activate action class.
 */
class Settings_MailIntegration_Activate_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if ('outlook' === $request->getByType('source') && !\App\YetiForce\Shop::check('YetiForceOutlook')) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		if ('outlook' === $request->getByType('source')) {
			$security = new \App\ConfigFile('security');
			$security->set('allowedScriptDomains', array_values(array_unique(array_merge((\Config\Security::$allowedScriptDomains), [
				'https://appsforoffice.microsoft.com', 'https://ajax.aspnetcdn.com'
			]))));
			$security->set('csrfFrameBreakerWindow', 'parent');
			$security->set('cookieForceHttpOnly', false);
			$security->set('cookieSameSite', 'None');
			$security->create();
			header('Location: index.php?parent=Settings&module=MailIntegration&view=Index');
		}
	}
}
