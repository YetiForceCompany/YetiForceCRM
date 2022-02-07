<?php

/**
 * Auto fill iban file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Auto fill IBAN handler class.
 */
class Vtiger_AutoFillIban_Handler
{
	/**
	 * EntityBeforeSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler): void
	{
		$recordModel = $eventHandler->getRecordModel();
		foreach ($recordModel->getModule()->getFieldsByType('iban', true) as $field) {
			if (!$field->hasDefaultValue() && $recordModel->isEmpty($field->getName())) {
				$ibanField = new \App\Fields\Iban();
				$fieldParams = $field->getFieldParams();
				$ibanValue = $ibanField->getIbanValue($fieldParams, $recordModel);
				$recordModel->set($field->getName(), $ibanValue)->setDataForSave([$field->getTableName() => [$field->getColumnName() => $ibanValue]]);
			}
		}
	}
}
