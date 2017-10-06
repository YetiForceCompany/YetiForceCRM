<?php

/**
 * Settings SharingAccess SaveAjax action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
Class Settings_SharingAccess_SaveAjax_Action extends Settings_Vtiger_Save_Action
{

	public function process(\App\Request $request)
	{
		$modulePermissions = $request->get('permissions');
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
				if ($permissionOld === 3 || (int) $moduleModel->get('permission') == 3) {
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
