<?php

/**
 * Mail Mass delete action model class
 * @package YetiForce.Settings.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_MassDelete_Action extends Vtiger_Mass_Action
{

	/**
	 * Checking permission 
	 * @param \App\Request $request
	 * @throws \Exception\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = \App\User::getCurrentUserModel();
		if (!$currentUserModel->isAdmin()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$recordIds = $this->getRecordsListFromRequest($request);
		foreach ($recordIds as $recordId) {
			$recordModel = Settings_Mail_Record_Model::getInstance($recordId);
			$recordModel->delete();
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
