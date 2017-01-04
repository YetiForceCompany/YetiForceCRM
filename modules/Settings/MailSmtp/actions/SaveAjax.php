<?php

/**
 * MailSmtp SaveAjax action model class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_MailSmtp_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
	}

	public function save(Vtiger_Request $request)
	{
	//	$mailer = new \App\Mailer();
		//$result = $mailer->test();
		//if (isset($result['result']) && $result['result'] !== false) {
		if(1){
			$data = $request->get('param');
			$recordId = $request->get('record');
			if ($recordId) {
				$recordModel = Settings_MailSmtp_Record_Model::getInstanceById($recordId);
			} else {
				$recordModel = Settings_MailSmtp_Record_Model::getCleanInstance();
			}
			$dataFull = array_merge($recordModel->getData(), $data);
			$recordModel->setData($dataFull);
			$recordModel->save();
			$return = ['success' => 'true', 'message' => \App\Language::translate('LBL_SUCCESS')];
		} else {
			$return = ['success' => 'false', 'message' => \App\Language::translate('LBL_MAIL_TEST_FAILED')];
		}

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($return);
		$responceToEmit->emit();
	}
}
