<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

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
		$validator = array();
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
