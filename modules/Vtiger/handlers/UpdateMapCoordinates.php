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
		$recordId = $recordModel->getId();
		foreach ($recordModel->getModule()->getFieldsByType(['mapCoordinates'], true) as $fieldName => $fieldModel) {
			$coordinateData = $this->getCoordinateToDb($recordModel->get($fieldName));
			if (!empty($coordinateData['lat']) && !empty($coordinateData['lon'])) {
				if (!(new \App\Db\Query())->from(\OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME)
					->where(['crmid' => $recordId, 'type' => $fieldName])->exists()) {
					$action = 'insert';
				} elseif ($recordModel->getPreviousValue($fieldName)) {
					$action = 'update';
				}
			} elseif ($recordModel->getPreviousValue($fieldName) && empty($coordinateData['lat']) && empty($coordinateData['lon'])) {
				if ((new \App\Db\Query())->from(\OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME)
					->where(['crmid' => $recordId, 'type' => $fieldName])->exists()) {
					$action = 'delete';
				}
			}
			if (isset($action)) {
				\App\Fields\MapCoordinates::updateMapCoordinates($recordId, $fieldName, $coordinateData, $action);
			}
		}
	}

	/**
	 * Retrieves the decimal coordinates according to the database type.
	 *
	 * @param string $value
	 *
	 * @return array
	 */
	public function getCoordinateToDb(string $value): array
	{
		$coordinateData = \App\Json::decode($value);
		switch ($coordinateData['type']) {
			case \App\Fields\MapCoordinates::DEGREES:
				$coordinate = \App\Fields\MapCoordinates::convert(\App\Fields\MapCoordinates::DEGREES, \App\Fields\MapCoordinates::DECIMAL, $coordinateData['value']);
				break;
			case \App\Fields\MapCoordinates::CODE_PLUS:
				$coordinate = \App\Fields\MapCoordinates::convert(\App\Fields\MapCoordinates::CODE_PLUS, \App\Fields\MapCoordinates::DECIMAL, $coordinateData['value']);
				break;
			default:
			$coordinate = $coordinateData['value'];
				break;
		}
		return $coordinate;
	}
}
