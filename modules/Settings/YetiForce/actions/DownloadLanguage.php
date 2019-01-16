<?php

/**
 * YetiForce register action class file.
 *
 * @package   Modules
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
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
	public function process(\App\Request $request)
	{
		$result = \App\Installer\Languages::download($request->getByType('prefix'));
		$message = App\Language::translate('LBL_DOWNLOADED_LANGUAGE', $request->getModule(false));
		$responseType = 'success';
		if (!$result) {
			$message = App\Language::translate('LBL_NO_INTERNET_CONNECTION', $request->getModule(false));
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
