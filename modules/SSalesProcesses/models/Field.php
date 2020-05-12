<?php

/**
 * Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SSalesProcesses_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Function returns special validator for fields.
	 *
	 * @return array
	 */
	public function getValidator()
	{
		$validator = [];
		if ('estimated_date' === $this->getName()) {
			$validator[] = ['name' => 'greaterThanDependentField',
				'params' => ['startdate',  $this->getName()], ];
		} elseif ('estimated_margin' === $this->getName()) {
			$validator[] = ['name' => 'lessThanDependentField',
				'params' => ['estimated', $this->getName()], ];
		} else {
			$validator = parent::getValidator();
		}
		return $validator;
	}
}
