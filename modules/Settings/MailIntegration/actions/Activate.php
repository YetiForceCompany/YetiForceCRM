<?php
/**
 * MailIntegration Activate action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * MailIntegration Activate action class.
 */
class Settings_MailIntegration_Activate_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('activate');
		$this->exposeMethod('deactivate');
	}

	/**
	 * Activate service.
	 *
	 * @param App\Request $request
	 */
	public function activate(App\Request $request)
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

			$moduleName = 'MailIntegration';
			$batchMethod = (new \App\BatchMethod(['method' => '\vtlib\Module::toggleModuleAccess', 'params' => [$moduleName, false]]));
			if (!\App\Module::isModuleActive($moduleName) || $batchMethod->isExists()) {
				$batchMethod->deleteDuplicate();
				$batchMethod->set('params', \App\Json::encode([$moduleName, true]))->save();
			}

			if ($request->isAjax()) {
				$response = new Vtiger_Response();
				$response->setResult(true);
				$response->emit();
			} else {
				header('Location: index.php?parent=Settings&module=MailIntegration&view=Index');
			}
		}
	}

	/**
	 * Deactivate service.
	 *
	 * @param App\Request $request
	 */
	public function deactivate(App\Request $request)
	{
		if ('outlook' === $request->getByType('source')) {
			$security = new \App\ConfigFile('security');
			$security->set('allowedScriptDomains', array_values(array_diff((\Config\Security::$allowedScriptDomains), [
				'https://appsforoffice.microsoft.com', 'https://ajax.aspnetcdn.com'
			])));
			$security->set('csrfFrameBreakerWindow', 'top');
			$security->set('cookieForceHttpOnly', true);
			$security->set('cookieSameSite', 'Strict');
			$security->create();

			$moduleName = 'MailIntegration';
			$batchMethod = (new \App\BatchMethod(['method' => '\vtlib\Module::toggleModuleAccess', 'params' => [$moduleName, true]]));
			if (\App\Module::isModuleActive($moduleName) || $batchMethod->isExists()) {
				$batchMethod->deleteDuplicate();
				$batchMethod->set('params', \App\Json::encode([$moduleName, false]))->save();
			}

			if ($request->isAjax()) {
				$response = new Vtiger_Response();
				$response->setResult(true);
				$response->emit();
			} else {
				header('Location: index.php?parent=Settings&module=MailIntegration&view=Index');
			}
		}
	}
}
