<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class PriceBooks_ListPriceUpdate_View extends Vtiger_BasicModal_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$recordId = $request->getInteger('record');
		if (!$recordId) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(App\Request $request)
	{
		parent::preProcess($request);
		$moduleName = $request->getModule();
		$priceBookId = $request->getInteger('record');
		$relId = $request->getInteger('relid');
		$currentPrice = $request->getByType('currentPrice', 'NumberInUserFormat');

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PRICEBOOK_ID', $priceBookId);
		$viewer->assign('REL_ID', $relId);
		$viewer->assign('CURRENT_PRICE', $currentPrice);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('ListPriceUpdate.tpl', $moduleName);
		parent::postProcess($request);
	}
}
