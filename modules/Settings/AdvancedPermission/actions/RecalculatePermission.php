<?php

/**
 * Save module to recalculate permissions
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_AdvancedPermission_RecalculatePermission_Action extends Settings_Vtiger_Save_Action
{

	public function process(\Vtiger_Request $request)
	{
		\App\PrivilegeUpdater::setUpdater($request->get('moduleName'));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
