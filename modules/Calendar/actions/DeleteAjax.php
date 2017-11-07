<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Calendar_DeleteAjax_Action extends Vtiger_Delete_Action
{

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$recordModel->delete();

		$cvId = $request->getByType('viewname', 2);
		$response = new Vtiger_Response();
		$response->setResult(['viewname' => $cvId, 'module' => $moduleName]);
		$response->emit();
	}
}
