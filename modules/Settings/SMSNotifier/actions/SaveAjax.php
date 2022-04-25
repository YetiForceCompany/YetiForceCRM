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

class Settings_SMSNotifier_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if ($request->isEmpty('providertype', true) || !\App\Integrations\SMSProvider::getProviderByName($request->getByType('providertype', \App\Purifier::ALNUM)) || (!$request->isEmpty('record') && !\App\Integrations\SMSProvider::getById($request->getInteger('record')))) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordId = $request->isEmpty('record') ? null : $request->getInteger('record');
		if ($recordId) {
			$recordModel = Settings_SMSNotifier_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_SMSNotifier_Record_Model::getCleanInstance($request->getByType('providertype', \App\Purifier::ALNUM));
		}
		$result = $recordModel->setDataFromRequest($request);

		$response = new Vtiger_Response();
		try {
			$result = $recordModel->save();
			$prev = $recordModel->anonymize($recordModel->getPreviousValue());
			$post = $recordId ? array_intersect_key($recordModel->anonymize($recordModel->getData()), $prev) : $recordModel->anonymize($recordModel->getData());
			\Settings_Vtiger_Tracker_Model::addDetail($prev, $post);
			$response->setResult([$result]);
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}
}
