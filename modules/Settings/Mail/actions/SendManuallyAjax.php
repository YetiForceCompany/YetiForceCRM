<?php

/**
 * Sen mail manually action model class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_SendManuallyAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	/**
	 * Checking permission 
	 * @param Vtiger_Request $request
	 * @throws \Exception\NoPermittedForAdmin
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = \App\User::getCurrentUserModel();
		if (!$currentUserModel->isAdmin()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$record = $request->get('id');
		$db = \App\Db::getInstance('admin');
		$row = (new \App\Db\Query())->from('s_#__mail_queue')
				->where(['id' => $record])->one($db);
		$status = \App\Mailer::sendByRowQueue($row);
		if ($status) {
			$db->createCommand()->delete('s_#__mail_queue', ['id' => $row['id']])->execute();
		} else {
			$db->createCommand()->update('s_#__mail_queue', ['status' => 2], ['id' => $row['id']])->execute();
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_SEND_EMAIL_MANUALLY', $request->getModule(false))]);
		$response->emit();
	}

	/**
	 * Validate Request
	 * @param Vtiger_Request $request
	 */
	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateReadAccess();
	}
}
