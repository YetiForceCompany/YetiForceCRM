<?php

/**
 * OSSTimeControl time uitype class
 * @package YetiForce.Uitype
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSTimeControl_Time_UIType extends Vtiger_Time_UIType
{

	public function getEditViewDisplayValue($value, $record = false)
	{
		if (!empty($value)) {
			return parent::getEditViewDisplayValue($value, $record);
		}

		$specialTimeFields = array('time_start', 'time_end');
		$fieldName = $this->get('field')->getFieldName();
		if (!in_array($fieldName, $specialTimeFields)) {
			return parent::getEditViewDisplayValue($value, $record);
		} else {
			return $this->getDisplayTimeDifferenceValue($fieldName, $value);
		}
	}

	/**
	 * Function to get the calendar event call duration value in hour format
	 * @param type $fieldName
	 * @param type $value
	 * @return <Vtiger_Time_UIType> - getTimeValue 
	 */
	public function getDisplayTimeDifferenceValue($fieldName, $value)
	{
		$date = new DateTime($value);

		if ($fieldName === 'time_end' && empty($value)) {
			$date->modify("+15 minutes");
		}

		$dateTimeField = new DateTimeField($date->format('Y-m-d H:i:s'));
		$value = $dateTimeField->getDisplayTime();
		return $value;
	}
}
