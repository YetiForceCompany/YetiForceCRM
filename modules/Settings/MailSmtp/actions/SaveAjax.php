<?php

/**
 * MailSmtp SaveAjax action model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MailSmtp_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('preSaveValidation');
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			$recordModel = Settings_MailSmtp_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_MailSmtp_Record_Model::getInstanceById($request->getInteger('record'));
		}
		$recordModel->setDataFromRequest($request);
		return $recordModel;
	}

	/**
	 * PreSave validation function.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function preSaveValidation(App\Request $request)
	{
		$recordModel = $this->getRecordModelFromRequest($request);
		$response = new Vtiger_Response();
		$response->setResult($recordModel->validate());
		$response->emit();
	}

	/**
	 * Save function.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function save(App\Request $request)
	{
		try {
			$recordModel = $this->getRecordModelFromRequest($request);
			$recordId = $recordModel->getId();
			$recordModel->save();
			\Settings_Vtiger_Tracker_Model::addDetail($recordModel->getPreviousValue(), $recordId ? array_intersect_key($recordModel->getData(), $recordModel->getPreviousValue()) : $recordModel->getData());
			$result = ['success' => true, 'url' => $recordModel->getModule()->getDefaultUrl()];
		} catch (\App\Exceptions\AppException $e) {
			$result = ['success' => false, 'message' => $e->getDisplayMessage()];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function updates smtp configuration.
	 *
	 * @param \App\Request $request
	 */
	public function updateSmtp(App\Request $request)
	{
		$encryptInstance = \App\Encryption::getInstance();
		$data = [
			'mailer_type' => $request->getByType('mailer_type'),
			'password' => $encryptInstance->encrypt($request->getRaw('password')),
			'smtp_password' => $encryptInstance->encrypt($request->getRaw('smtp_password')),
			'default' => $request->isEmpty('default') ? 0 : $request->getInteger('default'),
			'name' => $request->getByType('name', 'Text'),
			'host' => $request->getByType('host', 'Text'),
			'port' => $request->getInteger('port'),
			'username' => $request->getByType('username', 'Text'),
			'authentication' => $request->isEmpty('authentication') ? 0 : $request->getInteger('authentication'),
			'secure' => $request->getByType('secure'),
			'options' => $request->getByType('options', 'Text'),
			'from_email' => $request->getByType('from_email', 'Text'),
			'from_name' => $request->getByType('from_name', 'Text'),
			'reply_to' => $request->getByType('reply_to', 'Text'),
			'priority' => $request->getByType('priority', 'Text'),
			'confirm_reading_to' => $request->getByType('confirm_reading_to', 'Text'),
			'organization' => $request->getByType('organization', 'Text'),
			'unsubscribe' => App\Json::encode($request->getArray('unsubscribe', 'Text')),
			'individual_delivery' => $request->isEmpty('individual_delivery') ? 0 : $request->getInteger('individual_delivery'),
			'smtp_username' => $request->getByType('smtp_username', 'Text'),
			'smtp_host' => $request->getByType('smtp_host', 'Text'),
			'smtp_port' => $request->isEmpty('smtp_port') ? '' : $request->getInteger('smtp_port'),
			'smtp_folder' => \App\Purifier::decodeHtml($request->getByType('smtp_folder', 'Text')),
			'save_send_mail' => $request->isEmpty('save_send_mail') ? 0 : $request->getInteger('save_send_mail'),
			'smtp_validate_cert' => $request->isEmpty('smtp_validate_cert') ? 0 : $request->getInteger('smtp_validate_cert'),
			'mail_account' => $request->isEmpty('mail_account') ? 0 : $request->getInteger('mail_account'),
		];
		$mailer = new \App\Mailer();
		if ($data['mail_account']) {
			$mailer->loadSmtpFromAccount($data['mail_account']);
		} else {
			$mailer->loadSmtp($data);
		}

		$testMailer = $mailer->test();
		if (isset($testMailer['result']) && false !== $testMailer['result']) {
			if (!empty($data['default'])) {
				App\Db::getInstance('admin')->createCommand()->update('s_#__mail_smtp', ['default' => 0])->execute();
			}
			if (!$request->isEmpty('record')) {
				$recordModel = Settings_MailSmtp_Record_Model::getInstanceById($request->getInteger('record'));
			} else {
				$recordModel = Settings_MailSmtp_Record_Model::getCleanInstance();
			}
			foreach ($data as $key => $value) {
				$recordModel->set($key, $value);
			}
			$recordModel->save();
			$result = ['success' => true, 'url' => $recordModel->getDetailViewUrl()];
		} else {
			$result = ['success' => false, 'message' => \App\Purifier::purify($testMailer['error'])];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
