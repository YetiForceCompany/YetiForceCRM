<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Profiles_EditAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('checkDuplicate');
	}

	/**
	 * Action to check duplicate records.
	 *
	 * @param \App\Request $request
	 */
	public function checkDuplicate(App\Request $request)
	{
		$recordModel = Settings_Profiles_Record_Model::getInstanceByName($request->getByType('profilename', 'Text'), false, $request->getInteger('record'));
		$response = new Vtiger_Response();
		if (!empty($recordModel)) {
			$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_DUPLICATES_EXIST', $request->getModule(false))]);
		} else {
			$response->setResult(['success' => false]);
		}
		$response->emit();
	}
}
