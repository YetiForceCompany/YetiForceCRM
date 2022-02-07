<?php
/**
 * UIType Date and time.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * UIType Datetime Field Class.
 */
class Calendar_Datetime_UIType extends Vtiger_Datetime_UIType
{
	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		return strpos($value, ' ') ? App\Fields\DateTime::formatToDisplay($value) : App\Fields\Date::formatToDisplay($value);
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		$fieldName = $this->getFieldModel()->getName();
		if ('date_start' === $fieldName || 'due_date' === $fieldName) {
			$value = $value ? \App\Purifier::encodeHtml(DateTimeField::convertToUserFormat($value)) : '';
		} else {
			$value = parent::getEditViewDisplayValue($value, $recordModel);
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		$fieldName = $this->getFieldModel()->getName();
		if ('date_start' === $fieldName || 'due_date' === $fieldName) {
			$dbValue = $value ? App\Fields\Date::formatToDb($value) : '';
		} else {
			$dbValue = parent::getDBValue($value, $recordModel);
		}
		return $dbValue;
	}
}
