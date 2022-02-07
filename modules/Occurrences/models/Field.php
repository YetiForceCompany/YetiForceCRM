<?php
/**
 * Occurrences field object.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Occurrences_Field_Model Class.
 */
class Occurrences_Field_Model extends Vtiger_Field_Model
{
	/** {@inheritdoc} */
	public function getValidator()
	{
		$validator = [];
		if ('date_end' === $this->getName()) {
			$funcName = ['name' => 'greaterThanDependentField',
				'params' => ['date_start'], ];
			array_push($validator, $funcName);
		} else {
			$validator = parent::getValidator();
		}
		return $validator;
	}
}
