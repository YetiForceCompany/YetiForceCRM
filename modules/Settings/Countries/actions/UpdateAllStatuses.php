<?php

/**
 * Update all statuses
 * @package YetiForce.Webservice
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Wojciech Brüggemann <w.bruggemann@yetiforce.com>
 */
class Settings_Countries_UpdateAllStatuses_Action extends Settings_Vtiger_Index_Action
{

	/**
	 * {@inheritDoc}
	 */
	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$status = (int) $request->getBoolean('status');

		$moduleModel = Settings_Countries_Module_Model::getInstance($qualifiedModuleName);

		$response = new Vtiger_Response();
		$result = $moduleModel->updateAllStatuses($status);
		$response->setResult($result > 0);
		$response->emit();
	}
}
