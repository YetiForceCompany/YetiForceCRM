<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Rss_GetHtml_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->isPermitted($moduleName, 'ListView', $record)) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$url = $request->get('url');
		$request = Requests::get($url);

		$response = new Vtiger_Response();
		$response->setResult(array('html' => $request->body));
		$response->emit();
	}
}
