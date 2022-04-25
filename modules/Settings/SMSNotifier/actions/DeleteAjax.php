<?php
/**
 * SMSNotifier delete file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * SMSNotifier delete class.
 */
class Settings_SMSNotifier_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$result = true;
		$recordModel = Settings_SMSNotifier_Record_Model::getInstanceById($request->getInteger('record'));
		if ($recordModel) {
			$result = (bool) $recordModel->delete();
		}
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult(['success' => $result]);
		$responceToEmit->emit();
	}
}
