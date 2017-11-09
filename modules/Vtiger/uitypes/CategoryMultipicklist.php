<?php

/**
 * UIType Category multipicklist
 * @package YetiForce.UIType
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_CategoryMultipicklist_UIType extends Vtiger_Tree_UIType
{

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return string
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if ($value) {
			$value = ",$value,";
		} elseif (is_null($value)) {
			$value = '';
		}
		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * Verification of data
	 * @param string $value
	 * @param bool $isUserFormat
	 * @return null
	 * @throws \App\Exceptions\Security
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || $value === '' || $value === null) {
			return;
		}
		foreach (explode(',', $value) as $row) {
			if (substr($row, 0, 1) !== 'T' || !is_numeric(substr($row, 1))) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->get('field')->getFieldName() . '||' . $value, 406);
			}
		}
		$this->validate = true;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $tree
	 * @param int $record
	 * @param Vtiger_Record_Model $recordInstance
	 * @param boolean $rawText
	 * @return string
	 */
	public function getDisplayValue($tree, $record = false, $recordInstance = false, $rawText = false)
	{
		$fieldModel = $this->get('field');
		if (empty($tree)) {
			return '';
		}
		$names = [];
		$trees = array_filter(explode(',', $tree));
		$treeData = \App\Fields\Tree::getPicklistValue($fieldModel->getFieldParams(), $fieldModel->getModuleName());
		foreach ($trees as $treeId) {
			if (isset($treeData[$treeId])) {
				$names[] = $treeData[$treeId];
			}
		}
		return \App\Purifier::encodeHtml(implode(', ', $names));
	}
}
