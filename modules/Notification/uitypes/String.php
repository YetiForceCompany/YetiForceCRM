<?php

/**
 * Uitype model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_String_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function isAjaxEditable()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($newValue = $recordModel->getParseField($this->get('field')->getName())) {
			$value = $newValue;
		}
		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}
}
