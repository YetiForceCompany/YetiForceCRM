<?php
/**
 * UIType Text Field file.
 *
 * @package UiType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Text UIType class.
 */
class SMSNotifier_Text_UIType extends Vtiger_Text_UIType
{
	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$size = 'mini';
		if (empty($length)) {
			$length = 400;
		} elseif (\is_string($length)) {
			$size = $length;
			$length = 200;
		}
		$value = \App\Purifier::purify($value);
		$value = nl2br($value);
		if (!$rawText) {
			$value = \App\Layout::truncateHtml(\App\Utils\Completions::decode($value), $size, $length);
		}

		return $value;
	}

	/** {@inheritdoc} */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		if (empty($value)) {
			return '';
		}
		$value = nl2br(\App\Purifier::purify($value));
		return \App\Utils\Completions::decodeEmoji($value);
	}
}
