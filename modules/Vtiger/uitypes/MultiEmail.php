<?php

/**
 * UIType multi email Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_MultiEmail_UIType extends Vtiger_Email_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setValueFromRequest(\App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getArray($requestFieldName, 'Text');
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @example validate('[{"e":"a.adach@yetiforce.com","o":0},{"e":"test@yetiforce.com","o":0}]');
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value)) {
			return;
		} elseif (is_string($value)) {
			$value = \App\Json::decode($value);
		}
		if (!is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$rawValue = \App\Json::encode($value);
		if (!isset($this->validate[$rawValue])) {
			foreach ($value as $item) {
				if (!is_array($item) || !isset($item['e'])) {
					throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
				}
				if (!filter_var($item['e'], FILTER_VALIDATE_EMAIL) || $item['e'] !== filter_var($item['e'], FILTER_SANITIZE_EMAIL)) {
					throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
				}
			}
			$this->validate[$rawValue] = true;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return $value ? \App\Json::encode($value) : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$value = \App\Json::decode($value);
		if (empty($value)) {
			return '';
		}
		$emails = [];
		foreach ($value as $item) {
			$emails[] = parent::getDisplayValue($item['e'], $record, $recordModel, $rawText, $length);
		}
		return implode(',', $emails);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return '';
		}
		$value = \App\Json::decode($value);
		if (empty($value)) {
			return '';
		}
		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return $this->getDisplayValue($value, $record, $recordModel, $rawText);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiEmail.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['c', 'k', 'y', 'ny'];
	}
}
