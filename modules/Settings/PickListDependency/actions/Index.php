<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_PickListDependency_Index_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('checkCyclicDependencyExists');
	}

	/**
	 * Check if dependency for fields exists.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function checkCyclicDependencyExists(App\Request $request)
	{
		$module = $request->getByType('sourceModule', App\Purifier::ALNUM);
		$sourceField = $request->getByType('sourcefield', App\Purifier::ALNUM);
		$secondField = $request->getByType('secondField', App\Purifier::ALNUM);
		$thirdField = $request->isEmpty('thirdField') ? null : $request->getByType('thirdField', App\Purifier::ALNUM);
		$result = Settings_PickListDependency_Record_Model::checkCyclicDependencyExists($module, $sourceField, $secondField, $thirdField);
		$response = new Vtiger_Response();
		$response->setResult(['result' => $result]);
		$response->emit();
	}
}
