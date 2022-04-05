<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Vtiger_ShowWidget_View extends Vtiger_IndexAjax_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = $request->getModule();
		if ('Home' === $moduleName && !$userPrivilegesModel->hasModulePermission($moduleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ('Home' !== $moduleName && !$userPrivilegesModel->hasModuleActionPermission($moduleName, 'Dashboard')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$componentName = $request->getByType('name');
		if (!empty($componentName)) {
			$className = Vtiger_Loader::getComponentClassName('Dashboard', $componentName, $moduleName);
			if (!empty($className)) {
				$widget = null;
				if (!$request->isEmpty('linkid', true)) {
					$widget = new Vtiger_Widget_Model();
					$widget->set('linkid', $request->getInteger('linkid'));
					$widget->set('userid', App\User::getCurrentUserId());
					$widget->set('widgetid', $request->getInteger('widgetid'));
					$widget->set('active', $request->getInteger('active'));
					$widget->set('filterid', $request->getInteger('filterid', null));
					if ($request->has('data')) {
						$widget->set('data', $request->getByType('data', 'Text'));
					}
					$widget->show();
				}
				$classInstance = new $className();
				$classInstance->process($request, $widget);

				return;
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(['success' => false, 'message' => \App\Language::translate('NO_DATA')]);
		$response->emit();
	}

	/** {@inheritdoc} */
	public function validateRequest(App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
