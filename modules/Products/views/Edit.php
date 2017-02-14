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

Class Products_Edit_View extends Vtiger_Edit_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$recordModel = $this->record;
		if (!$recordModel) {
			if (!empty($recordId)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			} else {
				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			}
		}

		$baseCurrenctDetails = $recordModel->getBaseCurrencyDetails();

		$viewer = $this->getViewer($request);
		$viewer->assign('BASE_CURRENCY_NAME', 'curname' . $baseCurrenctDetails['currencyid']);
		$viewer->assign('BASE_CURRENCY_ID', $baseCurrenctDetails['currencyid']);
		$viewer->assign('BASE_CURRENCY_SYMBOL', $baseCurrenctDetails['symbol']);
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		parent::process($request);
	}

	public function getDuplicate($record, $moduleName)
	{
		$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$recordModel->set('id', '');
		$recordModel->set('qtyinstock', null);
		//While Duplicating record, If the related record is deleted then we are removing related record info in record model
		$mandatoryFieldModels = $recordModel->getModule()->getMandatoryFieldModels();
		foreach ($mandatoryFieldModels as $fieldModel) {
			if ($fieldModel->isReferenceField()) {
				$fieldName = $fieldModel->get('name');
				if (!\App\Record::isExists($recordModel->get($fieldName))) {
					$recordModel->set($fieldName, '');
				}
			}
		}
		return $recordModel;
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);

		$jsFileNames = array(
			'libraries.jquery.multiplefileupload.jquery_MultiFile'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
