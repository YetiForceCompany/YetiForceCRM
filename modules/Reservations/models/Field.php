<?php

/**
 * Reservations field model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Reservations_Field_Model extends Vtiger_Field_Model
{

	/**
	 * Function to get Edit view display value
	 * @param string Data base value
	 * @return string value
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		$fieldName = $this->getName();

		//Set the start date and end date
		if (empty($value)) {
			if ($fieldName === 'date_start') {
				return DateTimeField::convertToUserFormat(date('Y-m-d'));
			} elseif ($fieldName === 'due_date') {
				$minutes = 15;
				return DateTimeField::convertToUserFormat(date('Y-m-d', strtotime("+$minutes minutes")));
			}
		}
		return parent::getEditViewDisplayValue($value, $record);
	}

	/**
	 * Function returns special validator for fields
	 * @return <Array>
	 */
	public function getValidator()
	{
		$validator = [];
		$fieldName = $this->getName();

		switch ($fieldName) {
			case 'due_date': $funcName = array('name' => 'dateAndTimeGreaterThanDependentField',
					'params' => ['date_start', 'time_start', 'due_date', 'time_end']);
				array_push($validator, $funcName);
				break;
			case 'date_start': $funcName = array('name' => 'dateAndTimeGreaterThanDependentField',
					'params' => ['date_start', 'time_start', 'due_date', 'time_end']);
				array_push($validator, $funcName);
				break;
			case 'time_start': $funcName = array('name' => 'dateAndTimeGreaterThanDependentField',
					'params' => ['date_start', 'time_start', 'due_date', 'time_end']);
				array_push($validator, $funcName);
				break;
			case 'time_end': $funcName = array('name' => 'dateAndTimeGreaterThanDependentField',
					'params' => ['date_start', 'time_start', 'due_date', 'time_end']);
				array_push($validator, $funcName);
				break;
			default : $validator = parent::getValidator();
				break;
		}
		return $validator;
	}
}
