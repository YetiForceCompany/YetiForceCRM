<?php

/**
 * OSSTimeControl time uitype class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_Time_UIType extends Vtiger_Time_UIType
{
	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (!empty($value)) {
			return parent::getEditViewDisplayValue($value, $recordModel);
		}

		$specialTimeFields = ['time_start', 'time_end'];
		$fieldName = $this->get('field')->getFieldName();
		if (!\in_array($fieldName, $specialTimeFields)) {
			return parent::getEditViewDisplayValue($value, $recordModel);
		}
		return $this->getDisplayTimeDifferenceValue($fieldName, $value);
	}

	/**
	 * Function to get the calendar event call duration value in hour format.
	 *
	 * @param type $fieldName
	 * @param type $value
	 *
	 * @return <Vtiger_Time_UIType> - getTimeValue
	 */
	public function getDisplayTimeDifferenceValue($fieldName, $value)
	{
		if (null === $value) {
			$value = "now";
		}
		$date = new DateTime($value);
		if ('time_end' === $fieldName && empty($value)) {
			$date->modify('+15 minutes');
		}
		return (new DateTimeField($date->format('Y-m-d H:i:s')))->getDisplayTime();
	}
}
