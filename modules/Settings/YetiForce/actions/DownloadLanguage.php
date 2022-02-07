<?php

/**
 * YetiForce register action class file.
 *
 * @package   Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

/**
 * Class for YetiForce registration actions.
 */
class Settings_YetiForce_DownloadLanguage_Action extends Settings_Vtiger_Save_Action
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
		$result = \App\Installer\Languages::download($request->getByType('prefix'));
		$message = \App\Language::translate('LBL_DOWNLOADED_LANGUAGE', $request->getModule(false));
		$responseType = 'success';
		if (!$result) {
			$message = App\Language::translate(\App\Installer\Languages::getLastErrorMessage(), 'Other:Exceptions');
			$responseType = 'error';
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $result,
			'message' => $message,
			'type' => $responseType
		]);
		$response->emit();
	}
}
