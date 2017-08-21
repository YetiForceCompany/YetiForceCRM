<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_GetData_Action extends Vtiger_IndexAjax_View
{

	/**
	 * Check permission
	 * @param \App\Request $request
	 * @return boolean
	 * @throws \Exception\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->get('source_module'), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$record = $request->getInteger('record');
		$sourceModule = $request->get('source_module');
		$response = new Vtiger_Response();
		$permitted = Users_Privileges_Model::isPermitted($sourceModule, 'DetailView', $record);
		if ($permitted) {
			vglobal('showsAdditionalLabels', true);
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
			$labels = $data = $display = [];
			foreach ($recordModel->getModule()->getFields() as $fieldName => $fieldModel) {
				if ($fieldModel->isViewable()) {
					$data[$fieldName] = $recordModel->get($fieldName);
					$labels[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $recordModel->getModuleName());
					$display[$fieldName] = $fieldModel->getDisplayValue($recordModel->get($fieldName), $record, $recordModel, true);
				}
			}
			$response->setResult([
				'success' => true,
				'data' => array_map('App\Purifier::decodeHtml', $data),
				'displayData' => array_map('App\Purifier::decodeHtml', $display),
				'labels' => $labels
			]);
		} else {
			$response->setResult([
				'success' => false,
				'message' => \App\Language::translate('LBL_PERMISSION_DENIED')
			]);
		}
		$response->emit();
	}
}
