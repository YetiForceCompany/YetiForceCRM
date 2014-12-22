<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Emails_BasicAjax_Action extends Vtiger_Action_Controller {

	public function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$searchValue = $request->get('searchValue');

		$emailsResult = array();
		if ($searchValue) {
			$emailsResult = $moduleModel->searchEmails($request->get('searchValue'));
		}

		$response = new Vtiger_Response();
		$response->setResult($emailsResult);
		$response->emit();
	}
}

?>
