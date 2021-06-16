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
		$ibanUiType = 329;
		$allIbanFields = $recordModel->getModule()->getFieldsByUiType($ibanUiType);
		if ($allIbanFields) {
			$recordData = $recordModel->getData();
			foreach ($allIbanFields as $moduleIbanField) {
				if (!$moduleIbanField->hasDefaultValue() && !$recordModel->get($moduleIbanField->getName())) {
					$ibanField = new \App\Fields\Iban();
					$fieldParams = $moduleIbanField->getFieldParams();
					$ibanValue = $ibanField->getIBANValue($fieldParams, $recordData);
					$recordModel->set($moduleIbanField->getName(), $ibanValue);
				}
			}
		}
	}
}
