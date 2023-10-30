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

class Vtiger_SaveAjax_Action extends Vtiger_Save_Action
{
	/**
	 * Function process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$this->saveRecord($request);
		$fieldModelList = $this->record->getModule()->getFields();
		$result = [];
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if (!$fieldModel->isViewable()) {
				continue;
			}
			$prevDisplayValue = false;
			if (false !== ($recordFieldValuePrev = $this->record->getPreviousValue($fieldName))) {
				$prevDisplayValue = $fieldModel->getDisplayValue($recordFieldValuePrev, $this->record->getId(), $this->record);
			}
			$result[$fieldName] = [
				'value' => \App\Purifier::encodeHtml($this->record->getRawValue($fieldName)),
				'display_value' => $fieldModel->getDisplayValue($this->record->get($fieldName), $this->record->getId(), $this->record),
				'prev_display_value' => $prevDisplayValue,
			];
		}
		$result['_recordLabel'] = $this->record->getName();
		$result['_recordId'] = $this->record->getId();
		$this->record->clearPrivilegesCache();
		$result['_isEditable'] = $this->record->isEditable();
		$result['_isViewable'] = $this->record->isViewable();
		$result['_reload'] = \count($this->record->getPreviousValue()) > 1;
		if (method_exists($this, 'addCustomResult')) {
			$this->addCustomResult($result);
		}
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(App\Request $request)
	{
		if ('QuickEdit' !== $request->getByType('fromView') && !$request->isEmpty('record')) {
			$recordModel = $this->record ?: Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
			$fieldModel = $recordModel->getModule()->getFieldByName($request->getByType('field', 2));
			if ($fieldModel && $fieldModel->isEditable()) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $recordModel, 'value');
				if ($request->getBoolean('setRelatedFields') && $fieldModel->isReferenceField()) {
					$recordModel = $this->setRelatedFieldsInHierarchy($recordModel, $fieldModel->getName());
				}
			}
		} else {
			$recordModel = parent::getRecordModelFromRequest($request);
		}
		return $this->record = $recordModel;
	}

	/**
	 * Replenishment of related fields.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param string               $fieldName
	 *
	 * @return \Vtiger_Record_Model
	 */
	public function setRelatedFieldsInHierarchy(Vtiger_Record_Model $recordModel, $fieldName)
	{
		$fieldValue = $recordModel->get($fieldName);
		$relatedModules = \App\ModuleHierarchy::getRelationFieldByHierarchy($recordModel->getModuleName(), $fieldName);
		if ($relatedModules && !empty($fieldValue) && $recordModel->getPreviousValue($fieldName) !== $fieldValue) {
			$sourceModule = \App\Record::getType($fieldValue);
			foreach ($relatedModules as $relatedModule => $relatedFields) {
				if ($relatedModule === $sourceModule) {
					$relRecordModel = \Vtiger_Record_Model::getInstanceById($fieldValue, $sourceModule);
					foreach ($relatedFields as $to => $from) {
						$toModel = $recordModel->getModule()->getFieldByName($to);
						$relFieldModel = $relRecordModel->getModule()->getFieldByName($from[0]);
						$relFieldValue = $relRecordModel->get($from[0]);
						if ($relFieldValue && $relFieldModel && $toModel && $toModel->isWritable()) {
							if ($toModel->isReferenceField() || $relFieldModel->isReferenceField()) {
								$sourceType = \App\Record::getType($relFieldValue);
								if (\in_array($sourceType, $toModel->getReferenceList())) {
									$recordModel->set($toModel->getName(), $relFieldValue);
								}
							} else {
								$recordModel->set($toModel->getName(), $relFieldValue);
							}
						}
					}
				}
			}
		}
		return $recordModel;
	}
}
