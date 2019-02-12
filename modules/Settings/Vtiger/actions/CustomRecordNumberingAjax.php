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
		$instance = \App\Fields\RecordNumber::getInstance($sourceModule);
		$moduleData = $instance->getData();
		if (empty($moduleData['reset_sequence'])) {
			$moduleData['reset_sequence'] = 'n';
		}
		$picklistsModels = Vtiger_Module_Model::getInstance($sourceModule)->getFieldsByType(['picklist']);
		foreach ($picklistsModels as $fieldModel) {
			$moduleData['picklists'][$fieldModel->getName()] = App\Language::translate($fieldModel->getFieldLabel(), $sourceModule);
		}
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
		$moduleModel->set('prefix', $request->getByType('prefix', 'Text'));
		$moduleModel->set('leading_zeros', $request->getByType('leading_zeros', 'Integer'));
		$moduleModel->set('sequenceNumber', $request->getByType('sequenceNumber', 'Integer'));
		$moduleModel->set('postfix', $request->getByType('postfix', 'Text'));
		if (!$request->isEmpty('reset_sequence') && in_array($request->getByType('reset_sequence'), ['Y', 'M', 'D'])) {
			$moduleModel->set('reset_sequence', $request->getByType('reset_sequence'));
		} else {
			$moduleModel->set('reset_sequence', '');
		}
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
		$result = App\Fields\RecordNumber::getInstance($request->getByType('sourceModule', 2))->updateRecords();
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
