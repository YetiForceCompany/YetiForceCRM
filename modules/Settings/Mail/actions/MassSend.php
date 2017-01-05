<?php

/**
 * Mail Mass send email action model class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_MassSend_Action extends Vtiger_Mass_Action
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
		$selectedIds = $request->get('selected_ids');
		$recordIds = $this->getRecordsListFromRequest($request);
		$db = \App\Db::getInstance('admin');
		$dataReader = (new \App\Db\Query())->from('s_#__mail_queue')
				->where(['id' => $recordIds])
				->createCommand($db)->query();
		while ($rowQueue = $dataReader->read()) {
			$status = \App\Mailer::sendByRowQueue($rowQueue);
			if ($status) {
				$db->createCommand()->delete('s_#__mail_queue', ['id' => $rowQueue['id']])->execute();
			} else {
				$db->createCommand()->update('s_#__mail_queue', ['status' => 2], ['id' => $rowQueue['id']])->execute();
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
