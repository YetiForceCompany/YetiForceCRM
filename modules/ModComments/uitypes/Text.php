<?php
/**
 * UIType ModComments Text Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Class for ModComments Text uitype.
 */
class ModComments_Text_UIType extends Vtiger_Text_UIType
{
	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ('commentcontent' !== $this->getFieldModel()->getName()) {
			return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
		}
		$value = \App\Purifier::purifyHtml($value);
		if (!$rawText) {
			$value = \App\Utils\Completions::decode($value);
		}
		return $length ? \App\Layout::truncateHtml($value) : $value;
	}
}
