<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Inventory_MassSave_Action extends Vtiger_MassSave_Action {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordModels = $this->getRecordModelsFromRequest($request);
		foreach($recordModels as $recordId => $recordModel) {
			if(Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)) {
				//Inventory line items getting wiped out
				$_REQUEST['ajxaction'] = 'DETAILVIEW';
				$recordModel->save();
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
