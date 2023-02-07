<?php

/**
 * UIType Group field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * UIType Group field class.
 */
class Vtiger_Group_UIType extends Vtiger_Picklist_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!is_numeric($value) || 'Groups' !== \App\Fields\Owner::getType($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength) {
			$rangeValues = explode(',', $maximumLength);
			if (($rangeValues[1] ?? $rangeValues[0]) < $value || (isset($rangeValues[1]) ? $rangeValues[0] : 0) > $value) {
				throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
		}
		$this->validate[$value] = true;
	}

	/**
	 * Get picklist values.
	 *
	 * @return array
	 */
	public function getPicklistValues(): array
	{
		$fieldParams = $this->getFieldModel()->getFieldParams();
		$accessibleGroups = [];
		if (isset($fieldParams['showAllGroups']) && $fieldParams['showAllGroups']) {
			$accessibleGroups = (new \App\Db\Query())->select(['groupid', 'groupname'])->from('vtiger_groups')->createCommand()->queryAllByGroup(0);
		} else {
			$accessibleGroups = \App\Fields\Owner::getInstance($this->getFieldModel()->getModuleName())->getAccessibleGroupForModule();
		}
		return $accessibleGroups;
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $value ? \App\Fields\Owner::getGroupName($value) : '';
	}

	/** {@inheritdoc} */
	public function getEditViewValue($value, $recordModel = false)
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (null === $value || '' === $value) {
			return '';
		}
		return \App\Fields\Owner::getGroupName($value);
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y'];
	}

	/** {@inheritdoc} */
	public function getRecordOperators(): array
	{
		return parent::getRecordOperators();
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['integer'];
	}
}
