<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/models/Alert.php';

class Mobile_WS_FetchAllAlerts extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		$response = new Mobile_API_Response();

		$current_user = $this->getActiveUser();
		
		$result = array();
		$result['alerts'] = $this->getAlertDetails();
		$response->setResult($result);

		return $response;
	}
	
	function getAlertDetails() {
		$alertModels = Mobile_WS_AlertModel::models();
		
		$alerts = array();
		foreach($alertModels as $alertModel) {
			$alerts[] = $alertModel->serializeToSend();;
		}
		return $alerts;
	}
}