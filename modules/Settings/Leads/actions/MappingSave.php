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

class Settings_Leads_MappingSave_Action extends Settings_Vtiger_Index_Action
{
	public function process(App\Request $request)
	{
		$mapping = $request->get('mapping');
		$csrfKey = $GLOBALS['csrf']['input-name'] ?? '';
		if (\array_key_exists($csrfKey, $mapping)) {
			unset($mapping[$csrfKey]);
		}
		$mappingModel = Settings_Leads_Mapping_Model::getCleanInstance();

		$response = new Vtiger_Response();
		if ($mapping) {
			$mappingModel->save($mapping);
			$result = ['status' => true];
		} else {
			$result['status'] = false;
		}
		$response->setResult($result);

		return $response->emit();
	}
}
