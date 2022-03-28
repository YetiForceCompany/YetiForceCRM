F<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_SetReadRecord_Action extends Vtiger_SaveAjax_Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPriviligesModel->hasModuleActionPermission($request->getModule(), 'ReadRecord')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$this->saveRecord($request);

		$cvId = $request->getByType('viewname', 2);
		$response = new Vtiger_Response();
		$response->setResult(['viewname' => $cvId, 'module' => $moduleName]);
		$response->emit();
	}
}
