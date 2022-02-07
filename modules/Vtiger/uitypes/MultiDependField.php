<?php

/**
 * UIType Multi Depend Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_MultiDependField_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getArray($requestFieldName, 'Text');
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value)) {
			return;
		}
		if (\is_string($value)) {
			$value = \App\Json::decode($value);
		}
		if (!\is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$rawValue = \App\Json::encode($value);
		if (!isset($this->validate[$rawValue])) {
			$fieldsModel = $this->getFieldsModel();
			foreach ($value as $item) {
				if (!\is_array($item) || array_diff_key($item, $fieldsModel)) {
					throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
				}
				foreach ($item as $fieldName => $val) {
					$fieldsModel[$fieldName]->getUITypeModel()->validate($val, $isUserFormat);
				}
			}
			$this->validate[$rawValue] = true;
		}
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if ($value) {
			$fieldsModel = $this->getFieldsModel();
			foreach ($value as &$item) {
				foreach ($item as $fieldName => &$val) {
					$val = $fieldsModel[$fieldName]->getUITypeModel()->getDBValue($val, $recordModel);
				}
			}
			$value = \App\Json::encode($value);
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value) || !($value = \App\Json::decode($value))) {
			return '';
		}
		$data = [];
		$fieldsModel = $this->getFieldsModel();
		foreach ($value as $item) {
			$partData = [];
			foreach ($item as $fieldName => $val) {
				$partData[] = $fieldsModel[$fieldName]->getUITypeModel()->getDisplayValue($val, $record, $recordModel, $rawText, $length);
			}
			if ($partData = array_filter($partData)) {
				$data[] = implode(' - ', $partData);
			}
		}
		return implode(', ', $data);
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (empty($value) || !($value = \App\Json::decode($value))) {
			return [''];
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiDependField.tpl';
	}

	/**
	 * Gets fields model.
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public function getFieldsModel()
	{
		if (!isset($this->fieldsModels)) {
			$this->fieldsModels = [];
			$fieldModel = $this->getFieldModel();
			foreach ($fieldModel->getFieldParams() as $fieldName => $fieldData) {
				$this->fieldsModels[$fieldName] = Settings_Vtiger_Field_Model::init($fieldModel->getModuleName(), $fieldData);
				$this->fieldsModels[$fieldName]->setModule($fieldModel->getModule());
			}
		}
		return $this->fieldsModels;
	}

	/** {@inheritdoc} */
	public function isActiveSearchView()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function isListviewSortable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}
}
