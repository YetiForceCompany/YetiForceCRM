<?php
/**
 * UIType Icon Field File.
 *
 * @package   Settings.UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * UIType Icon Field Class.
 */
class Settings_Vtiger_Icon_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Icon.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return [];
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = \App\Json::isJson($value) ? \App\Json::decode($value) : $value;
		if (!$value) {
			return '';
		}
		if (!\is_array($value)) {
			$value = [$value];
		}
		$displayData = '';
		$type = $value['type'] ?? 'icon';
		$name = $value['name'] ?? $value[0];
		if ('icon' === $type) {
			$displayData = "<span class=\"{$name}\"></span>";
		} elseif ('image' === $type) {
			$displayData = '<img class="icon-img--picklist" src="' . \App\Layout\Media::getImageUrl($name) . '">';
		}

		return $displayData;
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		$value = \App\Json::isJson($value) ? \App\Json::decode($value) : $value;
		if (!$value) {
			return '';
		}
		if (!\is_array($value)) {
			$value = [$value];
		}
		$type = $value['type'] ?? 'icon';
		$name = $value['name'] ?? $value[0];
		if ('image' === $type) {
			$name = \App\Layout\Media::getImage($name)['name'] ?? '';
		}

		return $name;
	}
}
