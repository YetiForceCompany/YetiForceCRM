<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Products_SubProducts_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$productId = $request->get('record');
		$values = [];
		if (isRecordExists($productId)) {
			$productModel = Vtiger_Record_Model::getInstanceById($productId);
			$subProducts = $productModel->getSubProducts();

			foreach ($subProducts as $subProduct) {
				$values[$subProduct->getId()] = $subProduct->getName();
			}
		}

		$response = new Vtiger_Response();
		$response->setResult($values);
		$response->emit();
	}
}
