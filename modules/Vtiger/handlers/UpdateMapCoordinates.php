<?php

/**
 *  Update map coordinates handler file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Update map coordinates handler class.
 */
class Vtiger_UpdateMapCoordinates_Handler
{
	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		foreach ($recordModel->getModule()->getFieldsByType(['mapCoordinates'], true) as $fieldName => $fieldModel) {
			\App\Fields\MapCoordinates::updateMapCoordinates($recordModel, $fieldName);
		}
	}
}
