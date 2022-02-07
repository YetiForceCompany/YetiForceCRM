<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_LayoutEditor_Field_Action extends Settings_Vtiger_Index_Action
{
	/** @var string[] List of fields in edit view modal */
	const EDIT_FIELDS_FORM = [
		'label', 'presence', 'quickcreate', 'summaryfield', 'generatedtype', 'masseditable', 'header_field',
		'displaytype', 'maxlengthtext', 'maxwidthcolumn', 'tabindex', 'mandatory', 'icon_name',
	];

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('add');
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
		$this->exposeMethod('move');
		$this->exposeMethod('unHide');
		$this->exposeMethod('getPicklist');
		$this->exposeMethod('checkPicklistExist');
		$this->exposeMethod('validate');
		$this->exposeMethod('createSystemField');
		Settings_Vtiger_Tracker_Model::addBasic('save');
	}

	/**
	 * Create field.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function add(App\Request $request): void
	{
		$type = $request->getByType('fieldType', 'Alnum');
		$moduleName = $request->getByType('sourceModule', 'Alnum');
		$blockId = $request->getInteger('blockid');
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
	 * Validate field.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function validate(App\Request $request): void
	{
		$data = [];
		foreach (['fieldType', 'fieldLabel', 'fieldName', 'pickListValues'] as $name) {
			if ($request->has($name)) {
				if ('pickListValues' === $name) {
					$value = $request->getArray($name, \App\Purifier::TEXT);
				} else {
					$value = $request->getByType($name, \App\Purifier::TEXT);
				}
				$data[$name] = $value;
			}
		}
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($request->getByType('sourceModule', 'Alnum'));
		$response = new Vtiger_Response();
		$response->setResult($moduleModel->validate($data, false));
		$response->emit();
	}

	/**
	 * Save field.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return void
	 */
	public function save(App\Request $request): void
	{
		$fieldId = $request->getInteger('fieldid');
		if (empty($fieldId)) {
			throw new \App\Exceptions\AppException('Empty field ID: ' . $fieldId);
		}
		$fieldInstance = Vtiger_Field_Model::getInstance($fieldId);
		$uitypeModel = $fieldInstance->getUITypeModel();
		foreach (self::EDIT_FIELDS_FORM as $field) {
			if ($request->has($field)) {
				switch ($field) {
					case 'mandatory':
						$fieldInstance->updateTypeofDataFromMandatory($request->getByType($field, 'Standard'));
						break;
					case 'label':
						$fieldInstance->set($field, $request->getByType($field, 'Text'));
						break;
					case 'header_field':
						if ($request->getBoolean($field)) {
							if (!\in_array($request->getByType('header_type', 'Standard'), $uitypeModel->getHeaderTypes())) {
								throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . 'header_type', 406);
							}
							$data['type'] = $request->getByType('header_type', 'Standard');
							if ('highlights' === $data['type']) {
								$data['class'] = $request->getByType('header_class', 'Standard');
							} elseif ('value' === $data['type'] && $fieldInstance->isReferenceField() && ($relFields = $request->getArray('header_rel_fields', \App\Purifier::ALNUM))) {
								$relModuleModel = \Vtiger_Module_Model::getInstance(current($fieldInstance->getReferenceList()));
								foreach ($relFields as $fieldName) {
									if ($relModuleModel->getFieldByName($fieldName)->isViewableInDetailView()) {
										$data['rel_fields'][] = $fieldName;
									}
								}
							}
							$value = \App\Json::encode($data);
						} else {
							$value = '';
						}
						$fieldInstance->set($field, $value);
						break;
					case 'quickcreate':
						$quickCreateValue = $request->getInteger($field);
						if ($fieldInstance->get('quickcreate') !== $quickCreateValue && $quickCreateValue > 0) {
							$fieldInstance->set('quicksequence', $fieldInstance->__getNextQuickCreateSequence());
						}
						$fieldInstance->set($field, $quickCreateValue);
						break;
					case 'icon_name':
						$fieldVal = null;
						if (!$request->isEmpty('icon_name') && ($iconName = $request->getByType('icon_name', \App\Purifier::ALNUM_EXTENDED))) {
							$iconArray = \App\Json::decode($fieldInstance->get('icon'));
							$iconArray['name'] = $iconName;
							$fieldVal = \App\Json::encode($iconArray);
						}
						$fieldInstance->set('icon', $fieldVal);
						break;
					default:
						$fieldInstance->set($field, $request->getInteger($field));
						break;
				}
			}
		}
		if ($request->has('fieldMask')) {
			$params = $fieldInstance->getFieldParams();
			$params['mask'] = $request->getByType('fieldMask', 'Text');
			if (empty($params['mask'])) {
				unset($params['mask']);
			}
			$fieldInstance->set('fieldparams', $params ? \App\Json::encode($params) : '');
		}
		if ($request->has('anonymizationTarget')) {
			$fieldInstance->set('anonymizationTarget', $request->getArray('anonymizationTarget', \App\Purifier::INTEGER));
		}
		$response = new Vtiger_Response();
		try {
			if ($request->has('defaultvalue')) {
				if ($request->getBoolean('defaultvalue')) {
					$uitypeModel->setDefaultValueFromRequest($request);
				} else {
					$fieldInstance->set('defaultvalue', '');
				}
			}
			$fieldInstance->save();
			$response->setResult([
				'success' => true,
				'presence' => $request->getBoolean('presence') ? '1' : '0',
				'mandatory' => $fieldInstance->isMandatory(),
				'label' => \App\Language::translate($fieldInstance->get('label'), $request->getByType('sourceModule', 2)), ]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		} catch (Error $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Delete field.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function delete(App\Request $request): void
	{
		$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($request->getInteger('fieldid'));
		$response = new Vtiger_Response();
		if (!$fieldInstance->isCustomField()) {
			$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_NON_CUSTOM_FIELD_CANNOT_DELETE', 'Settings::LayoutEditor')]);
		} else {
			try {
				$fieldInstance->delete();
				$response->setResult(['success' => true]);
			} catch (Exception $e) {
				$response->setError($e->getCode(), $e->getMessage());
			}
		}
		$response->emit();
	}

	/**
	 * Move field.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function move(App\Request $request): void
	{
		Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($request->getMultiDimensionArray('updatedFields',
		[
			'block' => 'Integer',
			'fieldid' => 'Integer',
			'sequence' => 'Integer',
		]));
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	/**
	 * Make field active.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function unHide(App\Request $request): void
	{
		$response = new Vtiger_Response();
		try {
			$fieldIds = $request->getArray('fieldIdList', 'Integer');
			if (!empty($fieldIds)) {
				Settings_LayoutEditor_Field_Model::makeFieldActive($fieldIds, $request->getInteger('blockId'));
				$responseData = [];
				foreach ($fieldIds as $fieldId) {
					$fieldModel = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
					$fieldInfo = $fieldModel->getFieldInfo();
					$responseData[] = array_merge(['id' => $fieldModel->getId(), 'blockid' => $fieldModel->get('block')->id, 'customField' => $fieldModel->isCustomField()], $fieldInfo);
				}
				$response->setResult($responseData);
			}
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Check if picklist exist.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function checkPicklistExist(App\Request $request): void
	{
		$response = new Vtiger_Response();
		$response->setResult(\App\Fields\Picklist::isPicklistExist($request->getByType('fieldName', 'Alnum')));
		$response->emit();
	}

	/**
	 * Get picklist values.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function getPicklist(App\Request $request): void
	{
		$response = new Vtiger_Response();
		$fieldName = $request->getByType('rfield', 'Alnum');
		$moduleName = $request->getByType('rmodule', 'Alnum');
		$picklistValues = [];
		if (!empty($fieldName) && !empty($moduleName) && '-' != $fieldName) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
			$picklistValues = $fieldInstance->getPicklistValues();
			if (null === $picklistValues) {
				$picklistValues = [];
			}
		}
		$response->setResult($picklistValues);
		$response->emit();
	}

	/**
	 * Create system field.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function createSystemField(App\Request $request): void
	{
		$moduleName = $request->getByType('sourceModule', 'Alnum');
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($moduleName);
		$response = new Vtiger_Response();
		try {
			$moduleModel->addSystemField($request->getByType('field', 'Alnum'), $request->getInteger('blockId'), [
				'generatedtype' => 2,
			]);
			$response->setResult(true);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
