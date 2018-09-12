<?php

/**
 * ActivityRegister field model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class ActivityRegister_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Function returns special validator for fields.
	 *
	 * @return array
	 */
	public function getValidator()
	{
		$validator = [];
		if ($this->getName() === 'end_date') {
			$funcName = ['name' => 'greaterThanDependentField',
				'params' => ['start_date']];
			array_push($validator, $funcName);
		} else {
			$validator = parent::getValidator();
		}
		return $validator;
	}
}
