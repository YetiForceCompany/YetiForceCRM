<?php

/**
 * UIType MultiReference Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$valueArr = explode(self::COMMA, $value);
		foreach ($valueArr as $recordId) {
			if (!is_numeric($recordId)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $recordId, 406);
			}
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength && App\TextParser::getTextLength($value) > $maximumLength) {
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
		$values = explode(self::COMMA, $value);
		$maxLength = \is_int($length) ? $length : \App\Config::main('href_max_length');
		foreach ($values as $recordId) {
			if ($name = App\Record::getLabel($recordId)) {
				$name = $rawText ? $name : \App\TextParser::textTruncate($name, $maxLength);
				if (!$rawText && \App\Privilege::isPermitted($referenceModuleName, 'DetailView', $recordId)) {
					if ('Active' !== \App\Record::getState($recordId)) {
						$name = '<s>' . $name . '</s>';
					}
					$url = "index.php?module={$referenceModuleName}&view={$referenceModule->getDetailViewName()}&record={$recordId}";
					if (!empty($this->fullUrl)) {
						$url = Config\Main::$site_URL . $url;
					}
					$name = "<a class='modCT_{$referenceModuleName} showReferenceTooltip js-popover-tooltip--record' href='{$url}' title='" . App\Language::translateSingularModuleName($referenceModuleName) . "'>{$name}</a>";
				}
				$displayValue[$recordId] = $name;
			}
		}

		return implode(', <br>', $displayValue);
	}

	/**
	 * Gets reference module name.
	 *
	 * @return array
	 */
	public function getReferenceList(): array
	{
		$referenceList = $this->getFieldModel()->getFieldParams()['module'] ?? [];
		return (array) $referenceList;
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$referenceModuleName = current($this->getReferenceList());
		if (empty($value) || !$referenceModuleName || !($referenceModule = \Vtiger_Module_Model::getInstance($referenceModuleName)) || !$referenceModule->isActive()) {
			return '';
		}
		$displayValueRaw = [];
		$values = explode(self::COMMA, $value);
		$length = $this->getFieldModel()->get('maxlengthtext');
		$maxLength = empty($length) ? \App\Config::main('href_max_length') : $length;
		$break = false;
		foreach ($values as $recordId) {
			if ($name = App\Record::getLabel($recordId)) {
				$displayValueRaw[$recordId] = $name;
				if (!$rawText) {
					$names[$recordId] = $name;
					if (($maxLengthPart = \App\TextParser::getTextLength(implode(', ', $names))) > $maxLength) {
						$partLength = \count($names) > 1 ? ($maxLengthPart - $maxLength) + 1 : $maxLength;
						$name = \App\TextParser::textTruncate($name, $partLength);
						$break = true;
					}
					if ('Active' !== \App\Record::getState($recordId)) {
						$name = '<s>' . $name . '</s>';
					}
					$displayValueRaw[$recordId] = $name;
					if (\App\Privilege::isPermitted($referenceModuleName, 'DetailView', $recordId)) {
						$displayValueRaw[$recordId] = "<a class='modCT_{$referenceModuleName} showReferenceTooltip js-popover-tooltip--record' href='index.php?module={$referenceModuleName}&view=" . $referenceModule->getDetailViewName() . "&record={$recordId}' title='" . App\Language::translateSingularModuleName($referenceModuleName) . "'>{$name}</a>";
					}
					if ($break) {
						break;
					}
				}
			}
		}
		return implode(', ', $displayValueRaw);
	}

	/** {@inheritdoc} */
	public function getApiDisplayValue($value, Vtiger_Record_Model $recordModel)
	{
		$referenceModuleName = current($this->getReferenceList());
		if (empty($value) || !$referenceModuleName || !($referenceModule = \Vtiger_Module_Model::getInstance($referenceModuleName)) || !$referenceModule->isActive()) {
			return '';
		}

		$result = [];
		foreach (explode(self::COMMA, $value) as $recordId) {
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
	 * Gets an array values.
	 *
	 * @param string|null $value
	 *
	 * @return int[]
	 */
	public function getArrayValues(?string $value): array
	{
		return $value ? explode(self::COMMA, $value) : [];
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		$displayValue = [];
		$valueArr = explode(self::COMMA, $value);
		foreach ($valueArr as $recordId) {
			if (is_numeric($recordId) && \App\Record::isExists($recordId)) {
				$displayValue[] = \App\Record::getLabel($recordId);
			}
		}
		return \App\Purifier::encodeHtml(implode(', ', $displayValue));
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
