<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Settings_SMSNotifier_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_SMSNotifier_Record_Model::getInstanceById($request->getInteger('record'), $qualifiedModuleName);
		} else {
			$recordModel = Settings_SMSNotifier_Record_Model::getCleanInstance($qualifiedModuleName);
		}
		foreach ($recordModel->getEditFields() as $fieldName => $fieldLabel) {
			$recordModel->set($fieldName, $request->get($fieldName));
		}
		$parameters = [];
		$provider = SMSNotifier_Module_Model::getProviderInstance($recordModel->get('providertype'));
		foreach ($provider->getSettingsEditFieldsModel() as $fieldModel) {
			$parameters[$fieldModel->getName()] = $request->get($fieldModel->getName());
		}
		$recordModel->set('parameters', \App\Json::encode($parameters));

		$response = new Vtiger_Response();
		try {
			$result = $recordModel->save();
			$response->setResult([$result]);
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
