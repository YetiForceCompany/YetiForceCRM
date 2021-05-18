<?php
/**
 * Attendee UIType field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Attendee UIType field class .
 */
class Vtiger_Attendee_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return \App\Json::decode($value);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!\in_array($value, [0, 1, '1', '0', 'on'])) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$return = [];
		if ($value && !\App\Json::isEmpty($value)) {
			foreach (\App\Json::decode($value) as $attendee) {
				$return[] = $attendee['name'] ?? $attendee['value'];
			}
		}
		return \App\Purifier::encodeHtml(implode(', ', $return));
	}

	/** {@inheritdoc} */
	// public function getEditViewDisplayValue($value, $recordModel = false)
	// {
	// 	return explode(',', \App\Purifier::encodeHtml(trim($value, ',')));
	// }

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Attendee.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}
}
