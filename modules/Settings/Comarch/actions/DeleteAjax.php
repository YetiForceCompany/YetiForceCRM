<?php
/**
 * Settings Comarch delete action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings Comarch delete action class.
 */
class Settings_Comarch_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		Settings_Comarch_Record_Model::getInstanceById($request->getInteger('record'))->delete();
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
