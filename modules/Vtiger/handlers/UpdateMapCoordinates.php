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
		$coordinates = $recordModel->get('coordinates');
		if (!\App\Json::isEmpty($coordinates)) {
			$coordinates = \App\Json::decode($coordinates);
		}
		$coordinatesConnector = \App\Map\Coordinates::getInstance();
		$values = [];
		if (!empty($coordinatesData = $coordinatesConnector->getCoordinates(\App\Map\Coordinates::getAddressParams($recordModel, 'a')))) {
			$coordinatesData = reset($coordinatesData);
			foreach ([MapCoordinates::DECIMAL, MapCoordinates::DEGREES, MapCoordinates::CODE_PLUS] as $type) {
				$values[$type][] = \App\Fields\MapCoordinates::convert(MapCoordinates::DECIMAL, $type, ['lat' => $coordinatesData['lat'], 'lon' => $coordinatesData['lon']]);
			}
			if (isset($values['decimal'][0]['lat'], $values['decimal'][0]['lon'])) {
				$return['changeValues'][] = ['fieldName' => 'coordinates[decimal][lat]', 'value' => $values['decimal'][0]['lat']];
				$return['changeValues'][] = ['fieldName' => 'coordinates[decimal][lon]', 'value' => $values['decimal'][0]['lon']];
				$recordModel->set('coordinates[decimal][lat]', $values['decimal'][0]['lat']);
				$recordModel->set('coordinates[decimal][lon]', $values['decimal'][0]['lon']);
			}
			if (isset($values['degrees'][0]['lat'], $values['degrees'][0]['lon'])) {
				$return['changeValues'][] = ['fieldName' => 'coordinates[degrees][lat]', 'value' => $values['degrees'][0]['lat']];
				$return['changeValues'][] = ['fieldName' => 'coordinates[degrees][lon]', 'value' => $values['degrees'][0]['lon']];
				$recordModel->set('coordinates[degrees][lat]', $values['degrees'][0]['lat']);
				$recordModel->set('coordinates[degrees][lon]', $values['degrees'][0]['lon']);
			}
			if (isset($values['codeplus'][0])) {
				$return['changeValues'][] = ['fieldName' => 'coordinates[codeplus]', 'value' => $values['codeplus'][0]];
				$recordModel->set('coordinates[codeplus]', $values['codeplus'][0]['lat']);
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
