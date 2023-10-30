<?php
/**
 * YetiForce registration status check action file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Klaudia Łozowska <k.lozowska@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * YetiForce registration status check action class.
 */
class Settings_Companies_CheckStatus_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Check registration status.
	 *
	 * {@inheritdoc}
	 */
	public function process(App\Request $request): void
	{
		try {
			$message = '';
			$responseType = 'success';
			$registration = new \App\YetiForce\Register();
			$result = $registration->check();
			if ($error = $registration->getError()) {
				throw new \App\Exceptions\AppException($error);
			}
		} catch (\App\Exceptions\AppException $e) {
			$result = false;
			$responseType = 'error';
			$message = $e->getDisplayMessage();
		}

		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $result ?? false,
			'message' => $message ?? '',
			'type' => $responseType
		]);
		$response->emit();
	}
}
