<?php

/**
 * Save module to recalculate permissions.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_AdvancedPermission_RecalculatePermission_Action extends Settings_Vtiger_Save_Action
{
	public function process(App\Request $request)
	{
		\App\PrivilegeUpdater::setUpdater($request->getByType('moduleName', 2));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
