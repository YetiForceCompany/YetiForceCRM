<?php
/**
 * Cron task to update coordinates in records.
 *
 * @package Cron
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * OpenStreetMap_UpdaterRecordsCoordinates_Cron class.
 */
class OpenStreetMap_UpdaterRecordsCoordinates_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$db = App\Db::getInstance();
		$dataReader = (new App\Db\Query())->from(OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME)
			->limit(App\Config::module('OpenStreetMap', 'cronMaxUpdatedAddresses'))
			->createCommand()->query();
		$coordinatesConnector = \App\Map\Coordinates::getInstance();
		while ($row = $dataReader->read()) {
			$typeAddress = $row['type'];
			$recordId = $row['crmid'];
			$coordinates = $coordinatesConnector->getCoordinates(\App\Json::decode($row['address']));
			if (false === $coordinates) {
				break;
			}
			if (empty($coordinates)) {
				$db->createCommand()
					->delete('u_#__openstreetmap_record_updater', ['crmid' => $recordId, 'type' => $typeAddress])
					->execute();
				continue;
			}
			$coordinates = reset($coordinates);
			if ((new App\Db\Query())->from(OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME)->where(['crmid' => $recordId, 'type' => $typeAddress])->exists()) {
				if (empty($coordinates['lat']) && empty($coordinates['lon'])) {
					$db->createCommand()->delete(OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME, ['crmid' => $recordId, 'type' => $typeAddress])->execute();
				} else {
					$db->createCommand()->update(OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME, [
						'lat' => round($coordinates['lat'], 7),
						'lon' => round($coordinates['lon'], 7),
					], ['crmid' => $recordId, 'type' => $typeAddress])->execute();
				}
				$db->createCommand()->delete('u_#__openstreetmap_record_updater', ['crmid' => $recordId, 'type' => $typeAddress])->execute();
			} else {
				if (!empty($coordinates['lat']) && !empty($coordinates['lon'])) {
					$db->createCommand()->insert(OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME, [
						'type' => $typeAddress,
						'crmid' => $recordId,
						'lat' => round($coordinates['lat'], 7),
						'lon' => round($coordinates['lon'], 7),
					])->execute();
					$db->createCommand()->delete('u_#__openstreetmap_record_updater', ['crmid' => $recordId, 'type' => $typeAddress])->execute();
				}
			}
			if ($this->checkTimeout()) {
				break;
			}
		}
		$dataReader->close();
	}
}
