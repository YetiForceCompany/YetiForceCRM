<?php

/**
 * Mail Mass accept action model class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_MassAccept_Action extends Vtiger_Mass_Action
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
		
		foreach ($recordIds as $recordId) {
			Settings_Mail_Config_Model::acceptanceRecord($recordId);
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
