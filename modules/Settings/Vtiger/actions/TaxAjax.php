<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_TaxAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('checkDuplicateName');
	}

	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);

			return;
		}

		$taxId = $request->get('taxid');
		$type = $request->get('type');
		if (empty($taxId)) {
			$taxRecordModel = new Settings_Vtiger_TaxRecord_Model();
		} else {
			$taxRecordModel = Settings_Vtiger_TaxRecord_Model::getInstanceById($taxId, $type);
		}

		$fields = ['taxlabel', 'percentage', 'deleted'];
		foreach ($fields as $fieldName) {
			if ($request->has($fieldName)) {
				$taxRecordModel->set($fieldName, $request->get($fieldName));
			}
		}

		$taxRecordModel->setType($type);

		$response = new Vtiger_Response();
		try {
			$taxId = $taxRecordModel->save();
			$recordModel = Settings_Vtiger_TaxRecord_Model::getInstanceById($taxId, $type);
			$response->setResult(array_merge(['_editurl' => $recordModel->getEditTaxUrl(), 'type' => $recordModel->getType(), 'row_type' => \App\User::getCurrentUserModel()->getDetail('rowheight')], $recordModel->getData()));
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function checkDuplicateName(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$taxId = $request->get('taxid');
		$taxLabel = $request->get('taxlabel');
		$type = $request->get('type');

		$exists = Settings_Vtiger_TaxRecord_Model::checkDuplicate($taxLabel, $taxId, $type);

		if (!$exists) {
			$result = ['success' => false];
		} else {
			$result = ['success' => true, 'message' => \App\Language::translate('LBL_TAX_NAME_EXIST', $qualifiedModuleName)];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
