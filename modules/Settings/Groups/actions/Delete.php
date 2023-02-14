<?php
/**
 * Settings groups delete action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Settings groups delete action class.
 */
class Settings_Groups_Delete_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$transferRecordId = $request->getInteger('transfer_record');

		$recordModel = Settings_Groups_Record_Model::getInstance($recordId);
		if (\App\User::isExists($transferRecordId)) {
			$transferToOwner = Users_Record_Model::getInstanceById($transferRecordId, 'Users');
		} else {
			$transferToOwner = Settings_Groups_Record_Model::getInstance($transferRecordId);
		}
		if ($recordModel && $transferToOwner) {
			$recordModel->delete($transferToOwner);
		}

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
