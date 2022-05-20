<?php

/**
 * Uitype model.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Notification_String_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (!$recordModel && $record) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $this->get('field')->getModuleName());
		}
		if ($recordModel) {
			$value = $recordModel->getParseField($this->get('field')->getName());
		}
		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}
}
