<?php

/**
 * MailSmtp SaveAjax action model class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_MailSmtp_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateSmtp');
	}

	/**
	 * Function updates smtp configuration 
	 * @param Vtiger_Request $request
	 */
	public function updateSmtp(Vtiger_Request $request)
	{
		$data = $request->get('param');
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
			$recordModel->set('authentication', (int) $data['authentication']);
			$recordModel->set('secure', $data['secure']);
			$recordModel->set('options', $data['options']);
			$recordModel->set('from_email', $data['from_email']);
			$recordModel->set('from_name', $data['from_name']);
			$recordModel->set('replay_to', $data['replay_to']);
			$recordModel->set('individual_delivery', (int) $data['individual_delivery']);
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
