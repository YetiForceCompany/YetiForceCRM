<?php

/**
 * Save module to recalculate permissions
 * @package YetiForce.Settings.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_AdvancedPermission_RecalculatePermission_Action extends Settings_Vtiger_Save_Action
{

	public function process(\App\Request $request)
	{
		\App\PrivilegeUpdater::setUpdater($request->get('moduleName'));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
