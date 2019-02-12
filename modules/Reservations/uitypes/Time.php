<?php

/**
 * Reservations time UIType class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Reservations_Time_UIType extends Vtiger_Time_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (!empty($value)) {
			return parent::getEditViewDisplayValue($value, $recordModel);
		}
		$specialTimeFields = ['time_start', 'time_end'];
		$fieldName = $this->get('field')->getFieldName();
		if (!in_array($fieldName, $specialTimeFields)) {
			return parent::getEditViewDisplayValue($value, $recordModel);
		} else {
			return $this->getDisplayTimeDifferenceValue($fieldName, $value);
		}
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
		$date = new DateTime($value);
		if ($fieldName === 'time_end' && empty($value)) {
			$date->modify('+15 minutes');
		}
		return (new DateTimeField($date->format('Y-m-d H:i:s')))->getDisplayTime();
	}
}
