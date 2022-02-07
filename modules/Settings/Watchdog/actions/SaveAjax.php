<?php

/**
 * YetiForce watchdog action class.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class Settings_Watchdog_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function process(App\Request $request)
	{
		if (!$request->has('flagName')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$flagName = $request->getByType('flagName', \App\Purifier::ALNUM);
		$response = new Vtiger_Response();
		$result = true;
		$message = App\Language::translate('LBL_SAVED', $request->getModule(false));
		if (isset(\App\YetiForce\Watchdog::$variables[$flagName])) {
			$config = new \App\ConfigFile('component', 'YetiForce');
			$config->set($flagName, $request->getRaw('newParam'));
			$config->create();
		} else {
			$result = false;
			$message = App\Language::translate('LBL_PARAM_NOT_ALLOWED', $request->getModule(false));
		}
		$response->setResult([
			'success' => $result,
			'message' => $message,
		]);
		$response->emit();
	}
}
