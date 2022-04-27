<?php

/**
 * OSSTimeControl field model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_Field_Model extends Vtiger_Field_Model
{
	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		$fieldName = $this->getName();
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

	/** {@inheritdoc} */
	public function getValidator()
	{
		$validator = [];
		$fieldName = $this->getName();

		switch ($fieldName) {
			case 'due_date':
			case 'time_end':
			case 'date_start':
			case 'time_start': $funcName = ['name' => 'dateAndTimeGreaterThanDependentField',
				'params' => ['date_start', 'time_start', 'due_date', 'time_end'], ];
				$validator[] = $funcName;
				break;
			default: $validator = parent::getValidator();
				break;
		}
		return $validator;
	}
}
