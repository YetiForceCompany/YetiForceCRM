<?php
/**
 * Settings SLAPolicy DeleteAjax class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SLAPolicy_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$recordModel = Settings_SLAPolicy_Record_Model::getInstanceById($request->getInteger('record'));
		if ($recordModel) {
			$result = $recordModel->delete();
		} else {
			$result = ['success' => false];
			\App\Log::error('Not found SLA Policy record with this id :' . $request->getInteger('record'));
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
