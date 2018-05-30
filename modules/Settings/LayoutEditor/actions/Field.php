<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * ********************************************************************************** */

class Settings_LayoutEditor_Field_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		$this->exposeMethod('add');
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
		$this->exposeMethod('move');
		$this->exposeMethod('unHide');
		$this->exposeMethod('getPicklist');
	}

	public function add(\App\Request $request)
	{
		$type = $request->get('fieldType');
		$moduleName = $request->getByType('sourceModule', 2);
		$blockId = $request->get('blockid');
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($moduleName);
		$response = new Vtiger_Response();
		try {
			$fieldModel = $moduleModel->addField($type, $blockId, $request->getAll());
			$fieldInfo = $fieldModel->getFieldInfo();
			$responseData = array_merge([
				'id' => $fieldModel->getId(),
				'name' => $fieldModel->get('name'),
				'blockid' => $blockId,
				'customField' => $fieldModel->isCustomField(), ], $fieldInfo);
			$response->setResult($responseData);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Save field.
	 *
	 * @param \App\Request $request
	 */
	public function save(\App\Request $request)
	{
		$fieldId = $request->get('fieldid');
		$fieldInstance = Vtiger_Field_Model::getInstance($fieldId);
		$uitypeModel = $fieldInstance->getUITypeModel();
		$fields = ['presence', 'quickcreate', 'summaryfield', 'generatedtype', 'masseditable', 'header_field', 'displaytype', 'maxlengthtext', 'maxwidthcolumn', 'mandatory'];
		foreach ($fields as $field) {
			if ($request->has($field)) {
				switch ($field) {
					case 'mandatory':
						$fieldInstance->updateTypeofDataFromMandatory($request->getByType($field, 'Standard'));
						break;
					default:
						$fieldInstance->set($field, $request->getInteger($field));
						break;
				}
			}
		}
		if ($request->has('fieldMask')) {
			$fieldInstance->set('fieldparams', $request->get('fieldMask'));
		}
		$response = new Vtiger_Response();
		try {
			$defaultValue = $request->get('fieldDefaultValue');
			if ($fieldInstance->getFieldDataType() === 'date' && \App\TextParser::isVaribleToParse($defaultValue)) {
				$fieldInstance->set('defaultvalue', $defaultValue);
			} elseif ($defaultValue) {
				$uitypeModel->validate($defaultValue, true);
				$defaultValue = $uitypeModel->getDBValue($defaultValue);
			}
			$fieldInstance->set('defaultvalue', trim($defaultValue));
			$fieldInstance->save();
			$response->setResult([
				'success' => true,
				'presence' => $request->get('presence'),
				'mandatory' => $fieldInstance->isMandatory(),
				'label' => \App\Language::translate($fieldInstance->get('label'), $request->getByType('sourceModule', 2)), ]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		} catch (Error $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function delete(\App\Request $request)
	{
		$fieldId = $request->get('fieldid');
		$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
		$response = new Vtiger_Response();

		if (!$fieldInstance->isCustomField()) {
			$response->setError('122', 'Cannot delete Non custom field');
			$response->emit();

			return;
		}

		try {
			$fieldInstance->delete();
			$response->setResult(['success' => true]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function move(\App\Request $request)
	{
		$updatedFieldsList = $request->get('updatedFields');

		//This will update the fields sequence for the updated blocks
		Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($updatedFieldsList);

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	public function unHide(\App\Request $request)
	{
		$response = new Vtiger_Response();
		try {
			$fieldIds = $request->get('fieldIdList');
			Settings_LayoutEditor_Field_Model::makeFieldActive($fieldIds, $request->get('blockId'));
			$responseData = [];
			foreach ($fieldIds as $fieldId) {
				$fieldModel = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
				$fieldInfo = $fieldModel->getFieldInfo();
				$responseData[] = array_merge(['id' => $fieldModel->getId(), 'blockid' => $fieldModel->get('block')->id, 'customField' => $fieldModel->isCustomField()], $fieldInfo);
			}
			$response->setResult($responseData);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function getPicklist(\App\Request $request)
	{
		$response = new Vtiger_Response();
		$fieldName = $request->get('rfield');
		$moduleName = $request->get('rmodule');
		$picklistValues = [];
		if (!empty($fieldName) && !empty($moduleName) && $fieldName != '-') {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
			$picklistValues = $fieldInstance->getPicklistValues();
			if ($picklistValues === null) {
				$picklistValues = [];
			}
		}
		$response->setResult($picklistValues);
		$response->emit();
	}
}
