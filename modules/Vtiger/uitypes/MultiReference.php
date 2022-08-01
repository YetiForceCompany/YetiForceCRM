<?php

/**
 * UIType MultiReference Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_MultiReference_UIType class.
 */
class Vtiger_MultiReference_UIType extends Vtiger_Base_UIType
{
	/**
	 * Separator.
	 */
	const COMMA = ',';

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		$value = \is_array($value) ? implode(self::COMMA, $value) : $value;
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		$ids = $this->getArrayValues($value);
		if (\count($ids) > $this->getSelectionLimit()) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		foreach ($ids as $recordId) {
			if (!is_numeric($recordId)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $recordId, 406);
			}
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength && App\TextUtils::getTextLength($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value)) {
			$value = '';
		}
		if (!\is_array($value)) {
			$value = [$value];
		}
		return implode(',', $value);
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$values = [];
		if (!\is_array($value)) {
			$value = $value ? explode('##', $value) : [];
		}
		foreach ($value as $val) {
			$values[] = parent::getDbConditionBuilderValue($val, $operator);
		}
		return implode('##', $values);
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$referenceModuleName = current($this->getReferenceList());
		if (empty($value) || !$referenceModuleName || !($referenceModule = \Vtiger_Module_Model::getInstance($referenceModuleName)) || !$referenceModule->isActive()) {
			return '';
		}
		$displayValue = [];
		foreach ($this->getArrayValues($value) as $recordId) {
			$recordId = (int) $recordId;
			if ($rawText) {
				$displayValue[] = App\Record::getLabel($recordId);
			} else {
				$displayValue[] = \App\Record::getHtmlLink($recordId, $referenceModuleName, null, !empty($this->fullUrl));
			}
		}
		$maxLength = (int) ($this->getFieldModel()->getFieldParams()['displayLength'] ?? (\is_int($length) ? $length : \App\Config::main('href_max_length')));
		return \App\Layout::truncateHtml(implode(', <br>', $displayValue), 'miniHtml', $maxLength);
	}

	/** {@inheritdoc} */
	public function getApiDisplayValue($value, Vtiger_Record_Model $recordModel, array $params = [])
	{
		$referenceModuleName = current($this->getReferenceList());
		if (empty($value) || !$referenceModuleName || !($referenceModule = \Vtiger_Module_Model::getInstance($referenceModuleName)) || !$referenceModule->isActive()) {
			return '';
		}
		$result = [];
		foreach ($this->getArrayValues($value) as $recordId) {
			if (\App\Record::isExists($recordId)) {
				$result[$recordId] = [
					'value' => \App\Record::getLabel($recordId, true),
					'record' => $recordId,
					'referenceModule' => $referenceModuleName,
					'state' => \App\Record::getState($recordId),
					'isPermitted' => \App\Privilege::isPermitted($referenceModuleName, 'DetailView', $recordId),
				];
			}
		}
		return $result;
	}

	/**
	 * Gets reference module name.
	 *
	 * @return array
	 */
	public function getReferenceList(): array
	{
		return (array) $this->getFieldModel()->getFieldParams()['module'] ?? [];
	}

	/**
	 * Gets selection limit.
	 *
	 * @return int
	 */
	public function getSelectionLimit(): int
	{
		return (int) ($this->getFieldModel()->getFieldParams()['limit'] ?? 50);
	}

	/**
	 * Gets an array values.
	 *
	 * @param string|array|null $value
	 *
	 * @return int[]
	 */
	public function getArrayValues($value): array
	{
		if ($value) {
			return \is_array($value) ? $value : explode(self::COMMA, $value);
		}
		return [];
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		$displayValue = [];
		foreach ($this->getArrayValues($value) as $recordId) {
			if (is_numeric($recordId) && \App\Record::isExists($recordId)) {
				$displayValue[$recordId] = \App\Record::getLabel($recordId);
			}
		}
		return $displayValue;
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiReference.tpl';
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/MultiReference.tpl';
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/MultiReference.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['c', 'k', 'y', 'ny'];
	}
}
