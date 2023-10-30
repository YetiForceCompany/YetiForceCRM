<?php
/**
 * YetiForce verify email action class file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Klaudia Łozowska <k.lozowska@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

use App\YetiForce\EmailVerification;

/**
 * Class for YetiForce verify email actions.
 */
class Settings_Companies_VerifyEmail_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('request');
		$this->exposeMethod('confirm');
	}

	/**
	 * Send email with a verification code to the given email address.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \yii\db\Exception
	 */
	public function request(App\Request $request): void
	{
		$result = false;
		$responseType = 'error';
		$message = '';
		if ($request->has('terms_agreement') && !$request->getBoolean('terms_agreement')) {
			$message = \App\Language::translate('LBL_TERMS_AGREEMENT_REQUIRED', $request->getModule(false));
		} else {
			try {
				$email = $request->getByType('email', \App\Purifier::EMAIL);
				$registration = (new EmailVerification());
				$result = $registration->setEmail($email)->setType(EmailVerification::PROCESS_INIT)->send();

				if ($error = $registration->getError()) {
					throw new \App\Exceptions\AppException($error);
				}
			} catch (\App\Exceptions\AppException $e) {
				$result = false;
				$responseType = 'error';
				$message = $e->getDisplayMessage();
			}
		}

		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $result,
			'message' => $message,
			'type' => $responseType
		]);
		$response->emit();
	}

	/**
	 * Register an email address.
	 *
	 * @param \App\Request $request
	 */
	public function confirm(App\Request $request): void
	{
		$result = false;
		$responseType = 'error';
		$message = \App\Language::translate('LBL_INVALID_VERIFICATION_CODE', $request->getModule(false));

		try {
			if ($request->has('terms_agreement') && !$request->getBoolean('terms_agreement')) {
				throw new \App\Exceptions\AppException(\App\Language::translate('LBL_TERMS_AGREEMENT_REQUIRED', $request->getModule(false), null, false));
			}
			$email = $request->getByType('email', \App\Purifier::EMAIL);
			$code = $request->getByType('code', \App\Purifier::TEXT);
			$newsletterAgreement = $request->getBoolean('newsletter_agreement');

			$registration = (new EmailVerification());
			$result = $registration->setEmail($email)
				->setNewsletter($newsletterAgreement)
				->setCode($code)
				->setType(EmailVerification::PROCESS_REGISTER)
				->send();

			if ($result) {
				$registration->postProcess();
				$responseType = 'success';
				$message = \App\Language::translate('LBL_EMAIL_VERIFIED', $request->getModule(false));
				App\Process::removeEvent(Settings_Companies_EmailVerificationModal_View::MODAL_EVENT['name']);
			} elseif ($error = $registration->getError()) {
				throw new \App\Exceptions\AppException($error);
			}
		} catch (\App\Exceptions\AppException $e) {
			$result = false;
			$message = $e->getDisplayMessage();
		}

		$response = new \Vtiger_Response();
		$response->setResult([
			'success' => $result,
			'message' => $message,
			'type' => $responseType
		]);
		$response->emit();
	}
}
