<?php

/**
 * Mail Mass accept action model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_MassAccept_Action extends Vtiger_Mass_Action
{
	use \App\Controller\Traits\SettingsPermission;

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
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
