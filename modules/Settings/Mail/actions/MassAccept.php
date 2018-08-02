<?php

/**
 * Mail Mass accept action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_MassAccept_Action extends Vtiger_Mass_Action
{
	/**
	 * Checking permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = \App\User::getCurrentUserModel();
		if (!$currentUserModel->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$recordIds = $this->getRecordsListFromRequest($request);

		foreach ($recordIds as $recordId) {
			Settings_Mail_Config_Model::acceptanceRecord($recordId);
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
