<?php
/**
 * Cron task to update coordinates
 * @package YetiForce.CRON
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
$db = PearDatabase::getInstance();
$result = $db->query('SELECT crmid FROM u_yf_openstreetmap_address_updater');
if ($lastUpdatedCrmId = $db->getRow($result)) {
	$lastUpdatedCrmId = $lastUpdatedCrmId['crmid'];
	$query = 'SELECT crmid, setype FROM vtiger_crmentity WHERE crmid > ? LIMIT ?';
	$result = $db->pquery($query, [$lastUpdatedCrmId, AppConfig::module('OpenStreetMap', 'CRON_MAX_UPDATED_ADDRESSES')]);
	$moduleModel = Vtiger_Module_Model::getInstance('OpenStreetMap');
	while ($row = $db->getRow($result)) {
		if ($moduleModel->isAllowModules($row['setype'])) {
			$recordModel = Vtiger_Record_Model::getInstanceById($row['crmid']);
			$coordinates = OpenStreetMap_Module_Model::getCoordinatesByRecord($recordModel);
			foreach ($coordinates as $typeAddress => $coordinate) {
				$params['lat' . $typeAddress] = $coordinate['lat'];
				$params['lon' . $typeAddress] = $coordinate['lon'];
			}
			$isCoordinateExists = $db->pquery('SELECT 1 FROM u_yf_openstreetmap WHERE crmid = ?', [$recordModel->getId()]);
			$isCoordinateExists = $db->getSingleValue($isCoordinateExists);
			if ($isCoordinateExists) {
				if (!empty($params)) {
					$db->update('u_yf_openstreetmap', $params, 'crmid = ?', [$recordModel->getId()]);
				}
			} else {
				$params['crmid'] = $recordModel->getId();
				$db->insert('u_yf_openstreetmap', $params);
			}
		}
		$lastUpdatedCrmId = $row['crmid'];
	}
	
	$lastRecordId = $db->query('SELECT id FROM vtiger_crmentity_seq');
	$lastRecordId = $db->getSingleValue($lastRecordId);
	if ($lastRecordId - 1 == $lastUpdatedCrmId) {
		$db->update('u_yf_openstreetmap_address_updater', ['crmid' => 0]);
		$cronTask->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
		$cronTask->set('lockStatus', true);
	} else {
		$db->update('u_yf_openstreetmap_address_updater', ['crmid' => $lastUpdatedCrmId]);
	}
}
