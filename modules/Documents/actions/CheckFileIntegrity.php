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

class Documents_CheckFileIntegrity_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('multiple');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		foreach ($request->getArray('record', 'Integer') as $record) {
			if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $record)) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$moduleName = $request->getModule();
		$documentRecordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$resultVal = $documentRecordModel->checkFileIntegrity();
		$result = ['success' => $resultVal];
		if ($resultVal) {
			$documentRecordModel->updateFileStatus(1);
			$result['message'] = App\Language::translate('LBL_FILE_AVAILABLE', $moduleName);
		} else {
			$documentRecordModel->updateFileStatus(0);
			$result['message'] = App\Language::translate('LBL_FILE_NOT_AVAILABLE', $moduleName);
		}
		$result['url'] = $documentRecordModel->getDetailViewUrl();
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
