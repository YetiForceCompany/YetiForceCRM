<?php

/**
 * Mail Mass delete action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_MassDelete_Action extends Vtiger_Mass_Action
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
		$configMaxMassDelete = App\Config::performance('maxMassDeleteRecords');
		if (count($recordIds) > $configMaxMassDelete) {
			$response = new Vtiger_Response();
			$response->setResult(['notify' => ['text' => \App\Language::translateArgs('LBL_SELECT_UP_TO_RECORDS', '_Base', $configMaxMassDelete), 'type' => 'warning']]);
			$response->emit();
			return;
		}
		foreach ($recordIds as $recordId) {
			$recordModel = Settings_Mail_Record_Model::getInstance($recordId);
			$recordModel->delete();
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
