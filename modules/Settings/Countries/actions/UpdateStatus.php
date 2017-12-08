<?php

/**
 * Update status
 * @package YetiForce.Webservice
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Wojciech Bruggemann <w.bruggemann@yetiforce.com>
 */
class Settings_Countries_UpdateStatus_Action extends Settings_Vtiger_Index_Action
{

	/**
	 * {@inheritDoc}
	 */
	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$id = $request->getInteger('id');
		$status = (int) $request->getBoolean('status');

		$moduleModel = Settings_Countries_Module_Model::getInstance($qualifiedModuleName);

		$response = new Vtiger_Response();
		$result = $moduleModel->updateStatus($id, $status);
		$response->setResult($result > 0);
		$response->emit();
	}
}
