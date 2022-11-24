<?php

/**
 * UIType Map coordinates field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * UIType Map coordinates field class.
 */
class Vtiger_MapCoordinates_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value)) {
			return;
		}
		if (\is_string($value)) {
			$value = \App\Json::decode($value);
		}
		if (!\is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$rawValue = \App\Json::encode($value);
		if ($isUserFormat) {
			$validators = \App\Fields\MapCoordinates::VALIDATORS;
		} else {
			$validators = ['type' => 'Standard', 'value' => \App\Fields\MapCoordinates::VALIDATORS[$value['type']]];
		}
		if (!isset($this->validate[$rawValue])) {
			\App\Purifier::purifyMultiDimensionArray($value, $validators);
			$this->validate[$rawValue] = true;
		}
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (\is_string($value)) {
			$data = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		} else {
			$data = $value;
		}
		if (empty($data['type'])) {
			return null;
		}
		return \App\Json::encode(['value' => $data[$data['type']], 'type' => $data['type']]);
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return $this->getEmptyValue();
		}
		if (\is_string($value)) {
			if (\App\Json::isEmpty($value)) {
				return $this->getEmptyValue();
			}
			$value = \App\Json::decode($value);
		}
		if (isset($value['value'])) {
			$coordinates = $value[$value['type']] = $value['value'];
		} else {
			$coordinates = $value[$value['type']];
		}
		unset($value['value']);
		foreach (array_keys(\App\Fields\MapCoordinates::COORDINATE_FORMATS) as $type) {
			if ($value['type'] !== $type) {
				$value[$type] = \App\Fields\MapCoordinates::convert($value['type'], $type, $coordinates);
			}
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		// $values = [];
		// if (!\is_array($value)) {
		// 	$value = $value ? explode('##', $value) : [];
		// }
		// foreach ($value as $val) {
		// 	$values[] = parent::getDbConditionBuilderValue($val, $operator);
		// }
		// return implode('##', $values);
		return $value;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$value = \App\Json::decode($value);
		$rawValue = \is_array($value['value']) ? ("{$value['value']['lat']},{$value['value']['lon']}") : $value['value'];
		if ($rawText) {
			return $rawValue;
		}
		$map = $this->getEditViewDisplayValue($value, $recordModel);
		$desc = '';
		foreach (\App\Fields\MapCoordinates::COORDINATE_FORMATS as $key => $label) {
			if (\is_array($map[$key])) {
				$coord = "{$map[$key]['lat']}, {$map[$key]['lon']}";
			} else {
				$coord = $map[$key];
			}
			$desc .= \App\Language::translate($label, 'OpenStreetMap') . ': ' . $coord . '<br />';
		}
		$desc = \App\Purifier::encodeHtml($desc);
		$title = \App\Language::translate('LBL_COORDINATES', 'OpenStreetMap');
		$mapEncoded = '';
		$openStreetMapModuleModel = OpenStreetMap_Module_Model::getInstance('OpenStreetMap');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->hasModulePermission($openStreetMapModuleModel->getId())
		&& $openStreetMapModuleModel->isAllowModules($this->getFieldModel()->getModuleName())) {
			$mapEncoded = \App\Purifier::encodeHtml(\App\Json::encode($map));
		}
		return "<a class=\"js-popover-tooltip js-show-map__btn\" title=\"{$title}\" href=\"https://maps.google.com?q={$map['decimal']['lat']}+{$map['decimal']['lon']}\" target=\"popup\" rel=\"noreferrer noopener\" data-class=\"u-min-w-300px\" data-content=\"{$desc}\" data-value=\"{$mapEncoded}\">{$rawValue}</a>";
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MapCoordinates.tpl';
	}

	/** {@inheritdoc} */
	public function isActiveSearchView()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function isListviewSortable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['string'];
	}

	// /** {@inheritdoc} */
	// public function getOperatorTemplateName(string $operator = '')
	// {
	// 	return 'ConditionBuilder/MapCoordinates.tpl';
	// }

	// /** {@inheritdoc} */
	// public function getQueryOperators()
	// {
	// 	return ['e', 'n', 'y', 'ny'];
	// }

	/** {@inheritdoc} */
	public function getFieldInfo(): array
	{
		$fieldInfo = $this->getFieldModel()->loadFieldInfo();
		// $fieldInfo['picklistvalues'] = $this->getPicklistValues();
		$fieldInfo['type'] = '';
		return $fieldInfo;
	}

	/**
	 * Get empty value.
	 *
	 * @return array
	 */
	private function getEmptyValue(): array
	{
		return [
			'decimal' => [
				'lat' => '',
				'lon' => '',
			],
			'degrees' => [
				'lat' => '',
				'lon' => '',
			],
			'codeplus' => '',
			'type' => 'decimal',
		];
	}
}
