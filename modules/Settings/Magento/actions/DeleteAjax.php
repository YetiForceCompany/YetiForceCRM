<?php
/**
 * Magento delete action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Magento delete action class.
 */
class Settings_Magento_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		Settings_Magento_Record_Model::getInstanceById($request->getInteger('record'))->delete();
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
