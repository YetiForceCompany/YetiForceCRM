<?php

/**
 * Reservations field model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Reservations_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Function to get Edit view display value.
	 *
	 * @param string Data base value
	 * @param mixed $value
	 * @param mixed $recordModel
	 *
	 * @return string value
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		$fieldName = $this->getName();

		//Set the start date and end date
		if (empty($value)) {
			if ('date_start' === $fieldName) {
				return DateTimeField::convertToUserFormat(date('Y-m-d'));
			}
			if ('due_date' === $fieldName) {
				$minutes = 15;

				return DateTimeField::convertToUserFormat(date('Y-m-d', strtotime("+$minutes minutes")));
			}
		}
		return parent::getEditViewDisplayValue($value, $recordModel);
	}

	/**
	 * Function returns special validator for fields.
	 *
	 * @return <Array>
	 */
	public function getValidator()
	{
		$validator = [];
		$fieldName = $this->getName();

		switch ($fieldName) {
			case 'due_date': $funcName = ['name' => 'dateAndTimeGreaterThanDependentField',
				'params' => ['date_start', 'time_start', 'due_date', 'time_end'], ];
				$validator[] = $funcName;
				break;
			case 'date_start': $funcName = ['name' => 'dateAndTimeGreaterThanDependentField',
				'params' => ['date_start', 'time_start', 'due_date', 'time_end'], ];
				$validator[] = $funcName;
				break;
			case 'time_start': $funcName = ['name' => 'dateAndTimeGreaterThanDependentField',
				'params' => ['date_start', 'time_start', 'due_date', 'time_end'], ];
				$validator[] = $funcName;
				break;
			case 'time_end': $funcName = ['name' => 'dateAndTimeGreaterThanDependentField',
				'params' => ['date_start', 'time_start', 'due_date', 'time_end'], ];
				$validator[] = $funcName;
				break;
			default: $validator = parent::getValidator();
				break;
		}
		return $validator;
	}
}
