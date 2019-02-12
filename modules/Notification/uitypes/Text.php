<?php

/**
 * Uitype Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_Text_UIType extends Vtiger_Text_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = $recordModel->getParseField($this->get('field')->getName());

		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}
}
