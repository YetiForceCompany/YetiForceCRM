<?php
/**
 * Settings SlaPolicy DeleteAjax class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SlaPolicy_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$recordModel = Settings_SlaPolicy_Record_Model::getInstanceById($recordId);
		if ($recordModel && !empty($recordModel->delete())) {
			$result = $result = ['success' => true];
		} else {
			$result = ['success' => false, 'message' => "Not found SLA Policy record with this id: $recordId"];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
