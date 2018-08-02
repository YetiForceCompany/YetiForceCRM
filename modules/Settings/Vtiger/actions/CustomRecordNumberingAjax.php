<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Vtiger_CustomRecordNumberingAjax_Action extends Settings_Vtiger_Index_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getModuleCustomNumberingData');
		$this->exposeMethod('saveModuleCustomNumberingData');
		$this->exposeMethod('updateRecordsWithSequenceNumber');
	}

	/**
	 * The function checks permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function checkPermission(\App\Request $request)
	{
		parent::checkPermission($request);
		$request->getModule(false);
		$sourceModule = $request->getByType('sourceModule', 2);

		if (!$sourceModule) {
			throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function to get Module custom numbering data.
	 *
	 * @param \App\Request $request
	 */
	public function getModuleCustomNumberingData(\App\Request $request)
	{
		$sourceModule = $request->getByType('sourceModule', 2);
		$moduleData = \App\Fields\RecordNumber::getNumber($sourceModule);

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($moduleData);
		$response->emit();
	}

	/**
	 * Function save module custom numbering data.
	 *
	 * @param \App\Request $request
	 */
	public function saveModuleCustomNumberingData(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($request->getByType('sourceModule', 2));
		$moduleModel->set('prefix', $request->get('prefix'));
		$moduleModel->set('sequenceNumber', $request->get('sequenceNumber'));
		$moduleModel->set('postfix', $request->get('postfix'));
		$result = $moduleModel->setModuleSequence();
		$response = new Vtiger_Response();
		if ($result['success']) {
			$response->setResult(App\Language::translate('LBL_SUCCESSFULLY_UPDATED', $qualifiedModuleName));
		} else {
			$message = App\Language::translate('LBL_PREFIX_IN_USE', $qualifiedModuleName);
			$response->setError($message);
		}
		$response->emit();
	}

	/**
	 * Function to update record with sequence number.
	 *
	 * @param \App\Request $request
	 */
	public function updateRecordsWithSequenceNumber(\App\Request $request)
	{
		$sourceModule = $request->getByType('sourceModule', 2);

		$moduleModel = Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($sourceModule);
		$result = $moduleModel->updateRecordsWithSequence();

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
