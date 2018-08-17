<?php

/**
 * YetiForce status action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class Settings_YetiForce_Status_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function process(\App\Request $request)
	{
		if (!$request->has('flagName')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$flagName = $request->getByType('flagName', 2);
		$response = new Vtiger_Response();
		$config = new \App\Configurator('yetiforce');
		$result = true;
		$message = App\Language::translate('LBL_SAVED', $request->getModule(false));
		if (isset(\App\YetiForce\Status::$variables[$flagName])) {
			switch (\App\YetiForce\Status::$variables[$flagName]) {
				case 'bool':
					$config->set($flagName, $request->getByType('newParam', 'Digital') === '1');
					break;
				default:
					$config->set($flagName, $request->getByType('newParam', 'Text'));
					break;
			}
		} else {
			$result = false;
			$message = App\Language::translate('LBL_PARAM_NOT_ALLOWED', $request->getModule(false));
		}
		$config->save();
		$response->setResult([
			'success' => $result,
			'message' => $message,
		]);
		$response->emit();
	}
}
