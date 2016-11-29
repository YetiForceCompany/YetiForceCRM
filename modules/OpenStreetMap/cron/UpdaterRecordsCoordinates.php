<?php
/* Cron task to update coordinates in records
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
$db = App\Db::getInstance();
$dataReader = (new App\Db\Query())->from('u_#__openstreetmap_record_updater')
		->limit(AppConfig::module('OpenStreetMap', 'CRON_MAX_UPDATED_ADDRESSES'))
		->createCommand()->query();
while ($row = $dataReader->read()) {
	$typeAddress = $row['type'];
	$recordId = $row['crmid'];
	$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
	$coordinates = $coordinatesModel->getCoordinates(\App\Json::decode($row['address']));
	if ($coordinates === false)
		break;
	if (empty($coordinates)) {
		$db->createCommand()
			->delete('u_#__openstreetmap_record_updater', ['crmid' => $recordId, 'type' => $typeAddress])
			->execute();
		continue;
	}
	$coordinates = reset($coordinates);
	$isCoordinateExists = (new App\Db\Query())->from('u_#__openstreetmap')
		->where(['type' => $typeAddress, 'crmid' => $recordId])
		->exists();
	if ($isCoordinateExists) {
		if (empty($coordinates['lat']) && empty($coordinates['lon'])) {
			$db->createCommand()->delete('u_#__openstreetmap', ['type' => $typeAddress, 'crmid' => $recordId])->execute();
		} else {
			$db->createCommand()->update('u_#__openstreetmap', ['lat' => $coordinates['lat'], 'lon' => $coordinates['lon']], ['type' => $typeAddress, 'crmid' => $recordId])->execute();
		}
		$db->createCommand()->delete('u_#__openstreetmap_record_updater', ['type' => $typeAddress, 'crmid' => $recordId])->execute();
	} else {
		if (!empty($coordinates['lat']) && !empty($coordinates['lon'])) {
			$db->createCommand()->insert('u_#__openstreetmap', [
				'type' => $typeAddress,
				'crmid' => $recordId,
				'lat' => $coordinates['lat'],
				'lon' => $coordinates['lon']
			])->execute();
			$db->createCommand()->delete('u_#__openstreetmap_record_updater', ['type' => $typeAddress, 'crmid' => $recordId])->execute();
		}
	}
}
