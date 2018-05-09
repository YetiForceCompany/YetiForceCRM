<?php
/**
 * DataSetRegister field model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class DataSetRegister_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Function returns special validator for fields.
	 *
	 * @return array
	 */
	public function getValidator()
	{
		$validator = [];
		$fieldName = $this->getName();
		switch ($fieldName) {
			case 'removed_from_register':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['added_to_register']];
				array_push($validator, $funcName);
				break;
			default:
				$validator = parent::getValidator();
				break;
		}
		return $validator;
	}
}
