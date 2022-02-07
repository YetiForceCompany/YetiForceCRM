<?php

/**
 * Settings SharingAccess SaveAjax action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_SharingAccess_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	public function process(App\Request $request)
	{
		$modulePermissions = $request->getArray('permissions', 'Integer');
		$modulePermissions[4] = $modulePermissions[6];

		$postValues = [];
		$prevValues = [];
		foreach ($modulePermissions as $tabId => $permission) {
			$permission = (int) $permission;
			$moduleModel = Settings_SharingAccess_Module_Model::getInstance($tabId);
			$permissionOld = (int) $moduleModel->get('permission');
			$moduleModel->set('permission', $permission);
			if ($permissionOld !== $permission) {
				$prevValues[$tabId] = $permissionOld;
				$postValues[$tabId] = (int) $moduleModel->get('permission');
				if (3 === $permissionOld || 3 == (int) $moduleModel->get('permission')) {
					\App\Privilege::setUpdater(\App\Module::getModuleName($tabId));
				}
			}
			$moduleModel->save();
		}
		Settings_Vtiger_Tracker_Model::addDetail($prevValues, $postValues);
		Settings_SharingAccess_Module_Model::recalculateSharingRules();

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->emit();
	}
}
