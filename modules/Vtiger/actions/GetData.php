<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_GetData_Action extends App\Controller\Action
{
	/**
	 * Check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \Exception\NoPermittedToRecord
	 *
	 * @return bool
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$record = $request->getInteger('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $request->getModule());
		if (!$recordModel->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$labels = $data = $display = $type = [];
		if ($request->has('fieldType')) {
			$fields = $recordModel->getModule()->getFieldsByType($request->getArray('fieldType', 'Standard'));
		} else {
			$fields = $recordModel->getModule()->getFields();
		}
		$fieldsDependency = \App\FieldsDependency::getByRecordModel('Detail', $recordModel);
		foreach ($fields as $fieldName => $fieldModel) {
			if ($fieldModel->isViewable() && (empty($fieldsDependency['hide']['backend']) || !\in_array($fieldName, $fieldsDependency['hide']['backend']))) {
				$data[$fieldName] = $recordModel->getRawValue($fieldName);
				$labels[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $recordModel->getModuleName());
				$display[$fieldName] = $fieldModel->getDisplayValue($recordModel->get($fieldName), $record, $recordModel, true);
				$type[$fieldName] = $fieldModel->getFieldDataType();
			}
		}

		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($recordModel);
		$eventHandler->setParams([
			'result' => [
				'success' => true,
				'data' => array_map('App\Purifier::decodeHtml', $data),
				'displayData' => array_map('App\Purifier::decodeHtml', $display),
				'labels' => $labels,
				'type' => $type,
			],
		]);
		$eventHandler->trigger('RecordGetData');

		$response = new Vtiger_Response();
		$response->setResult($eventHandler->getParam('result'));
		$response->emit();
	}
}
