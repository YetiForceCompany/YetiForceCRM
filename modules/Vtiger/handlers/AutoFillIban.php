<?php

/**
 * Auto fill iban class.
 *
 * @package Handler
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Kon <a.kon@yetiforce.com>
 */
/**
 * Vtiger_AutoFillIban_Handler class.
 */
class Vtiger_AutoFillIban_Handler
{
	/**
	 * EntityBeforeSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		foreach ($recordModel->getModule()->getFieldsByType('iban', true) as $field) {
			if (!$field->hasDefaultValue() && $recordModel->isEmpty($field->getName())) {
				$ibanField = new \App\Fields\Iban();
				$fieldParams = $field->getFieldParams();
				$ibanValue = $ibanField->getIBANValue($fieldParams, $recordModel->getData());
				$recordModel->set($field->getName(), $ibanValue)->setDataForSave([$field->getTableName() => [$field->getColumnName() => $ibanValue]]);
			}
		}
	}
}
