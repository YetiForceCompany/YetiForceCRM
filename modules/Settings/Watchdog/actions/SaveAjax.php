<?php

/**
 * YetiForce watchdog action class.
<<<<<<< HEAD:modules/Settings/YetiForce/actions/Watchdog.php
=======
 *
 * @package   Settings.Action
>>>>>>> developer:modules/Settings/Watchdog/actions/SaveAjax.php
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
<<<<<<< HEAD:modules/Settings/YetiForce/actions/Watchdog.php
class Settings_YetiForce_Watchdog_Action extends Settings_Vtiger_Save_Action
=======
class Settings_Watchdog_SaveAjax_Action extends Settings_Vtiger_Save_Action
>>>>>>> developer:modules/Settings/Watchdog/actions/SaveAjax.php
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
