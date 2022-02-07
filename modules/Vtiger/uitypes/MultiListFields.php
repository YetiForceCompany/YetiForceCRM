<?php
/**
 * UIType multi list fields field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 *  UIType multi list fields field class.
 */
class Vtiger_MultiListFields_UIType extends Vtiger_Multipicklist_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return null;
		}
		if (!\is_array($value)) {
			$value = [$value];
		}
		$value = implode(',', $value);
		return \App\Purifier::decodeHtml($value);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		$hashValue = \is_array($value) ? ',' . implode(',', $value) . ',' : $value;
		if (isset($this->validate[$hashValue]) || empty($value)) {
			return;
		}
		if (\is_string($value)) {
			$value = explode(',', $value);
		}
		if (!\is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		foreach ($value as $item) {
			if (!\is_string($item)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $item, 406);
			}
			if ($item != strip_tags($item) || $item != \App\Purifier::purify($item) || preg_match('/[^a-zA-Z0-9\_\|]/', $item)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $item, 406);
			}
		}
		$this->validate[$hashValue] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return null;
		}
		$translatedValues = [];
		$fieldValues = explode(',', $value);
		foreach ($fieldValues as $fieldValue) {
			$fieldData = explode('|', $fieldValue);
			$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($fieldData[0]);
			$moduleName = $fieldModel->getModuleName();
			$translatedValues[] = App\Language::translate($moduleName, $moduleName) . ' - ' . App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
		}
		return \App\Purifier::encodeHtml(implode(', ', $translatedValues));
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $value ? explode(',', \App\Purifier::encodeHtml(trim($value, ','))) : [];
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiListFields.tpl';
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/MultiListFields.tpl';
	}

	/**
	 * Returns template for operator.
	 *
	 * @param string $operator
	 *
	 * @return string
	 */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/MultiListFields.tpl';
	}

	/**
	 * Get picklist values.
	 *
	 * @return array
	 */
	public function getPicklistValues()
	{
		$params = $this->getFieldModel()->getFieldParams();
		$condition = ['and',
			['<>', 'vtiger_field.presence', 1],
		];
		if (isset($params['uitype'])) {
			$condition[] = ['uitype' => $params['uitype']];
		}
		if (isset($params['excludedModules'])) {
			$condition[] = ['not in', 'vtiger_tab.name', $params['excludedModules']];
		}
		if (isset($params['allowedModules'])) {
			$condition[] = ['vtiger_tab.name' => $params['allowedModules']];
		}
		$return = [];
		$query = (new App\Db\Query())->from('vtiger_field')
			->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
			->where($condition)
			->orderBy(['vtiger_tab.tabid' => \SORT_ASC, 'vtiger_field.sequence' => \SORT_ASC]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (isset($params['keys'])) {
				$key = [];
				foreach ($params['keys'] as $column) {
					$key[] = $row[$column];
				}
				$key = implode('|', $key);
			} else {
				$key = $row['fieldid'];
			}
			$return[$key] = App\Language::translate($row['name'], $row['name'], false, false) . ' - ' . App\Language::translate($row['fieldlabel'], $row['name'], false, false);
		}
		$dataReader->close();
		return $return;
	}
}
