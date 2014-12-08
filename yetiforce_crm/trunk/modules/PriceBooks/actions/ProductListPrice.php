<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_ProductListPrice_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function process(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleModel = $request->getModule();
		$priceBookModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
		$listPrice = $priceBookModel->getProductsListPrice($request->get('itemId'));

		$response = new Vtiger_Response();
		$response->setResult(array($listPrice));
		$response->emit();
	}
}

?>
