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
					} else {
						MapCoordinates::updateMapCoordinates($recordId, $fieldName, 'insert', $coordinate);
					}
				} elseif (!$isNew && empty($coordinate['lat']) && empty($coordinate['lon'])) {
					MapCoordinates::updateMapCoordinates($recordId, $fieldName, 'delete');
				}
			}
		}
	}

	/**
	 * EditViewChangeValue function.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return array
	 */
	public function editViewChangeValue(App\EventHandler $eventHandler): array
	{
		$return = [];
		$recordModel = $eventHandler->getRecordModel();
		$coordinatesFieldValue = $recordModel->get('coordinates');
		if (!\App\Json::isEmpty($coordinatesFieldValue)) {
			$coordinatesFieldValue = \App\Json::decode($coordinatesFieldValue);
		}
		if ((empty($coordinatesFieldValue['value']) || (empty($coordinatesFieldValue['value']['lat']) && empty($coordinatesFieldValue['value']['lon']))) && !empty($coordinatesData = \App\Map\Coordinates::getInstance()->getCoordinates(\App\Map\Coordinates::getAddressParams($recordModel, 'a')))) {
			$coordinatesData = reset($coordinatesData);
			foreach ([MapCoordinates::DECIMAL, MapCoordinates::DEGREES, MapCoordinates::CODE_PLUS] as $type) {
				if (!empty($convertCoordinates = \App\Fields\MapCoordinates::convert(MapCoordinates::DECIMAL, $type, ['lat' => $coordinatesData['lat'], 'lon' => $coordinatesData['lon']]))) {
					if (MapCoordinates::CODE_PLUS === $type) {
						$return['changeValues'][] = ['fieldName' => 'coordinates[codeplus]', 'value' => $convertCoordinates];
						$recordModel->set("coordinates[{$type}]", $convertCoordinates);
					} else {
						$return['changeValues'][] = ['fieldName' => "coordinates[{$type}][lat]", 'value' => $convertCoordinates['lat']];
						$return['changeValues'][] = ['fieldName' => "coordinates[{$type}][lon]", 'value' => $convertCoordinates['lon']];
						$recordModel->set("coordinates[{$type}][lat]", $convertCoordinates['lat']);
						$recordModel->set("coordinates[{$type}][lon]", $convertCoordinates['lon']);
					}
				}
			}
		}
		return $return;
	}

	/**
	 * Get variables for the current event.
	 *
	 * @param string $name
	 * @param array  $params
	 * @param string $moduleName
	 *
	 * @return array|null
	 */
	public function vars(string $name, array $params, string $moduleName): ?array
	{
		if (\App\EventHandler::EDIT_VIEW_CHANGE_VALUE === $name) {
			return ['addresslevel1a', 'addresslevel2a', 'addresslevel3a',  'addresslevel5a', 'addresslevel8a', 'buildingnumbera', 'addresslevel8a'];
		}
		return null;
	}
}
