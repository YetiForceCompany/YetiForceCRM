<?php

/**
 *  Update map coordinates handler file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

use App\Fields\MapCoordinates;

/**
 * Update map coordinates handler class.
 */
class Vtiger_UpdateMapCoordinates_Handler
{
	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return void
	 */
	public function entityAfterSave(App\EventHandler $eventHandler): void
	{
		$recordModel = $eventHandler->getRecordModel();
		$recordId = $recordModel->getId();
		$isNew = $recordModel->isNew();
		foreach ($recordModel->getModule()->getFieldsByType(['mapCoordinates'], true) as $fieldName => $fieldModel) {
			if ($isNew || false !== $recordModel->getPreviousValue($fieldName)) {
				$value = $recordModel->get($fieldName);
				if (!\App\Json::isEmpty($value)) {
					$value = \App\Json::decode($value);
					$coordinate = MapCoordinates::convert($value['type'], MapCoordinates::DECIMAL, $value['value']);
				}
				if (!empty($coordinate['lat']) && !empty($coordinate['lon'])) {
					if ((new \App\Db\Query())->from(\OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME)
						->where(['crmid' => $recordId, 'type' => $fieldName])->exists()) {
						MapCoordinates::updateMapCoordinates($recordId, $fieldName, 'update', $coordinate);
					} elseif ($recordModel->getPreviousValue($fieldName)) {
						MapCoordinates::updateMapCoordinates($recordId, $fieldName, 'insert', $coordinate);
					}
				} elseif (!$isNew && empty($coordinate['lat']) && empty($coordinate['lon'])) {
					MapCoordinates::updateMapCoordinates($recordId, $fieldName, 'delete');
				}
			}
		}
	}
}
