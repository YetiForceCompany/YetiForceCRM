<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Portal_DeleteAjax_Action extends Vtiger_DeleteAjax_Action
{

	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	public function process(\App\Request $request)
	{
		$recordId = $request->get('record');
		$module = $request->getModule();
		$moduleModel = new Portal_Module_Model();
		$moduleModel->deleteRecord($recordId);

		$response = new Vtiger_Response();
		$response->setResult(array('message' => \App\Language::translate('LBL_RECORD_DELETED_SUCCESSFULLY', $module)));
		$response->emit();
	}
}
