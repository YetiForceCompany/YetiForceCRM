<?php
/* Cron task to update coordinates in records
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
$db = PearDatabase::getInstance();
$query = 'SELECT * FROM u_yf_openstreetmap_record_updater LIMIT ?';
$result = $db->pquery($query, [AppConfig::module('OpenStreetMap', 'CRON_MAX_UPDATED_ADDRESSES')]);
while ($row = $db->getRow($result)) {
	$typeAddress = $row['type'];
	$recordId = $row['crmid'];
	$coordinates = OpenStreetMap_Module_Model::getCoordinates(\includes\utils\Json::decode($row['address']));
	if ($coordinates === false)
		break;
	if (empty($coordinates)) {
		$db->delete('u_yf_openstreetmap_record_updater', 'crmid = ? && type = ?', [$recordId, $typeAddress]);
		continue;
	}
	$coordinates = reset($coordinates);
	$isCoordinateExists = $db->pquery('SELECT 1 FROM u_yf_openstreetmap WHERE type = ? && crmid = ?', [$typeAddress, $recordId]);
	$isCoordinateExists = $db->getSingleValue($isCoordinateExists);
	if ($isCoordinateExists) {
		if (empty($coordinates['lat']) && empty($coordinates['lon'])) {
			$db->delete('u_yf_openstreetmap', 'type = ? && crmid = ?', [$typeAddress, $recordId]);
		} else {
			$db->update('u_yf_openstreetmap', ['lat' => $coordinates['lat'], 'lon' => $coordinates['lon']], 'type = ? && crmid = ?', [$typeAddress, $recordId]);
		}
		$db->delete('u_yf_openstreetmap_record_updater', 'crmid = ? && type = ?', [$recordId, $typeAddress]);
	} else {
		if (!empty($coordinates['lat']) && !empty($coordinates['lon'])) {
			$db->insert('u_yf_openstreetmap', [
				'type' => $typeAddress,
				'crmid' => $recordId,
				'lat' => $coordinates['lat'],
				'lon' => $coordinates['lon']
			]);
			$db->delete('u_yf_openstreetmap_record_updater', 'crmid = ? && type = ?', [$recordId, $typeAddress]);
		}
	}
}
