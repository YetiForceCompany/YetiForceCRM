<?php

/**
 * MailSmtp SaveAjax action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_MailSmtp_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateSmtp');
	}

	/**
	 * Function updates smtp configuration.
	 *
	 * @param \App\Request $request
	 */
	public function updateSmtp(\App\Request $request)
	{
		$data = $request->get('param');
		$encryptInstance = \App\Encryption::getInstance();
		$data['password'] = $encryptInstance->encrypt($data['password']);
		$data['smtp_password'] = $encryptInstance->encrypt($data['smtp_password']);
		$mailer = new \App\Mailer();
		$mailer->loadSmtp($data);
		$testMailer = $mailer->test();
		if (isset($testMailer['result']) && $testMailer['result'] !== false) {
			$recordId = $data['record'];
			if ($data['default']) {
				App\Db::getInstance('admin')->createCommand()->update('s_#__mail_smtp', ['default' => 0])->execute();
			}

			if ($recordId) {
				$recordModel = Settings_MailSmtp_Record_Model::getInstanceById($recordId);
			} else {
				$recordModel = Settings_MailSmtp_Record_Model::getCleanInstance();
			}

			$recordModel->set('mailer_type', $data['mailer_type']);
			$recordModel->set('default', (int) $data['default']);
			$recordModel->set('name', $data['name']);
			$recordModel->set('host', $data['host']);
			$recordModel->set('port', $data['port']);
			$recordModel->set('username', $data['username']);
			$recordModel->set('password', $data['password']);
			$recordModel->set('authentication', empty($data['authentication']) ? 0 : 1);
			$recordModel->set('secure', $data['secure']);
			$recordModel->set('options', $data['options']);
			$recordModel->set('from_email', $data['from_email']);
			$recordModel->set('from_name', $data['from_name']);
			$recordModel->set('reply_to', $data['reply_to']);
			$recordModel->set('individual_delivery', empty($data['individual_delivery']) ? 0 : 1);
			$recordModel->set('smtp_username', $data['smtp_username']);
			$recordModel->set('smtp_password', $data['smtp_password']);
			$recordModel->set('smtp_host', $data['smtp_host']);
			$recordModel->set('smtp_port', $data['smtp_port']);
			$recordModel->set('smtp_folder', $data['smtp_folder']);
			$recordModel->set('save_send_mail', empty($data['save_send_mail']) ? 0 : 1);
			$recordModel->set('smtp_validate_cert', empty($data['smtp_validate_cert']) ? 0 : 1);
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
