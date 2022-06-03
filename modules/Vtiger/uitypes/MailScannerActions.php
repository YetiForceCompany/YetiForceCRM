<?php
/**
 * UIType mail scanner actions field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 *  UIType mail scanner actions field class.
 */
class Vtiger_MailScannerActions_UIType extends Vtiger_MultiListFields_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		$hashValue = \is_array($value) ? '|' . implode(',', $value) . '|' : $value;
		if (isset($this->validate[$hashValue]) || empty($value)) {
			return;
		}
		if (\is_string($value)) {
			$value = explode(',', $value);
		}
		if (!\is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$actions = App\Mail\ScannerAction::getActions();
		foreach ($value as $item) {
			if (!\is_string($item) || !\in_array($item, $actions)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $item, 406);
			}
		}
		$this->validate[$hashValue] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return null;
		}
		$fieldValues = explode(',', \App\Purifier::encodeHtml(trim($value, ',')));
		foreach ($fieldValues as &$fieldValue) {
			$fieldValue = App\Language::translate('LBL_' . strtoupper($fieldValue), 'MailIntegration');
		}
		return implode(', ', $fieldValues);
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $value ? explode(',', \App\Purifier::encodeHtml(trim($value, ','))) : [];
	}

	/** {@inheritdoc} */
	public function getQueryOperators(): array
	{
		return ['c', 'k', 'y', 'ny'];
	}

	/** {@inheritdoc} */
	public function getPicklistValues(): array
	{
		$value = [];
		$mailActions = \App\Mail\ScannerAction::getActions();
		foreach ($mailActions as $fieldValue) {
			$value[$fieldValue] = App\Language::translate('LBL_' . strtoupper($fieldValue), 'MailIntegration', false, false);
		}
		return $value;
	}
}
