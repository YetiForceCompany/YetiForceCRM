<?php

/**
 * UIType ChangesJson Field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * ChangesJson UIType class.
 */
class Vtiger_ChangesJson_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getArray($requestFieldName, 'Text');
		if (!empty($value['changes']) && !empty($value['module'])) {
			$moduleModel = Vtiger_Module_Model::getInstance($value['module']);
			$value['changes'] = array_intersect_key($value['changes'], $moduleModel->getFields());
		}
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || (!\is_array($value) && \App\Json::isEmpty($value))) {
			return;
		}
		if (\is_string($value)) {
			$value = \App\Json::decode($value);
		}
		if (!\is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Utils::varExport($value), 406);
		}
		$rawValue = \App\Json::encode($value);
		if (!isset($this->validate[$rawValue])) {
			$moduleModel = Vtiger_Module_Model::getInstance($value['module']);
			foreach ($value['changes'] as $fieldName => $val) {
				$fieldModel = $moduleModel ? $moduleModel->getFieldByName($fieldName) : null;
				if (!$fieldModel || !$fieldModel->isWritable()) {
					throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
				}
				$fieldModel->getUITypeModel()->validate($val, $isUserFormat);
			}
			$this->validate[$rawValue] = true;
		}
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value) || (!\is_array($value) && \App\Json::isEmpty($value))) {
			return '';
		}
		$value = \is_array($value) ? $value : \App\Json::decode($value);
		$moduleModel = Vtiger_Module_Model::getInstance($value['module']);
		foreach ($value['changes'] as $fieldName => &$val) {
			$fieldModel = $moduleModel->getFieldByName($fieldName);
			$val = $fieldModel->getUITypeModel()->getDBValue($val);
		}
		return \App\Json::encode($value);
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (\App\Json::isEmpty($value)) {
			return '';
		}
		$value = \App\Json::decode($value);
		$recordId = $value['record'];
		$moduleModel = Vtiger_Module_Model::getInstance($value['module']);
		$data = [];
		$size = 'mini';
		if (empty($length)) {
			$length = \App\Config::main('listview_max_textlength');
		} elseif (\is_string($length)) {
			$size = $length;
			$length = \App\Config::main('listview_max_textlength');
		}

		foreach ($value['changes'] as $fieldName => $value) {
			$fieldModel = $moduleModel->getFieldByName($fieldName);
			$data[] = $fieldModel->getFullLabelTranslation() . ': ' . $fieldModel->getDisplayValue($value, $recordId, false, $rawText);
		}
		$value = implode("\n<br>", $data);
		if (!$rawText) {
			$value = \App\Layout::truncateHtml(\App\Utils\Completions::decode($value), $size, $length);
		}

		return $value;
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (\App\Json::isEmpty($value)) {
			return '';
		}
		$value = \App\Json::decode($value);
		$recordId = $value['record'];
		$moduleModel = Vtiger_Module_Model::getInstance($value['module']);
		$data = [];
		foreach ($value['changes'] as $fieldName => $value) {
			$fieldModel = $moduleModel->getFieldByName($fieldName);
			$data[] = $fieldModel->getFullLabelTranslation() . ': ' . $fieldModel->getDisplayValue($value, $recordId, false, true);
		}
		$value = implode(' ', $data);
		return \App\TextUtils::textTruncate(\App\Utils\Completions::decode($value), 100);
	}

	/** {@inheritdoc} */
	public function getEditViewValue($value, $recordModel = false)
	{
		if (\App\Json::isEmpty($value)) {
			return '';
		}
		$value = \App\Json::decode($value);
		$moduleModel = Vtiger_Module_Model::getInstance($value['module']);
		foreach ($value['changes'] as $fieldName => &$val) {
			$fieldModel = $moduleModel->getFieldByName($fieldName);
			$val = $fieldModel->getEditViewValue($val);
		}
		return \App\Json::encode($value);
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

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/ChangesJson.tpl';
	}
}
