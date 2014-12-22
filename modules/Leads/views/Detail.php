<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Leads_Detail_View extends Accounts_Detail_View {
	function preProcess(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleInstance = CRMEntity::getInstance($moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('CONVERSION_AVAILABLE_STATUS', Zend_Json::encode($moduleInstance->conversion_available_status));
		parent::preProcess($request);
	}
}
