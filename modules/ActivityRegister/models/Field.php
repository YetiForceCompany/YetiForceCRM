<?php

/**
 * ActivityRegister field model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		if ('end_date' === $this->getName()) {
			$funcName = ['name' => 'greaterThanDependentField',
				'params' => ['start_date']];
			array_push($validator, $funcName);
		} else {
			$validator = parent::getValidator();
		}
		return $validator;
	}
}
