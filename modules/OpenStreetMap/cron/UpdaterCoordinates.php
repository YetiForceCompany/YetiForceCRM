<?php
/**
 * Cron task to update coordinates
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
$db = App\Db::getInstance();
$lastUpdatedCrmId = (new App\Db\Query())->select(['crmid'])
	->from('u_#__openstreetmap_address_updater')
	->scalar();
if ($lastUpdatedCrmId !== false) {
	$dataReader = (new App\Db\Query())->select(['crmid', 'setype', 'deleted'])
			->from('vtiger_crmentity')
			->where(['>', 'crmid', $lastUpdatedCrmId])
			->limit(AppConfig::module('OpenStreetMap', 'CRON_MAX_UPDATED_ADDRESSES'))
			->createCommand()->query();
	$moduleModel = Vtiger_Module_Model::getInstance('OpenStreetMap');
	$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
	while ($row = $dataReader->read()) {
		if ($moduleModel->isAllowModules($row['setype']) && $row['deleted'] == 0) {
			$recordModel = Vtiger_Record_Model::getInstanceById($row['crmid']);
			$coordinates = $coordinatesModel->getCoordinatesByRecord($recordModel);
			foreach ($coordinates as $typeAddress => $coordinate) {
				$isCoordinateExists = (new App\Db\Query())->from('u_#__openstreetmap')->where(['type' => $typeAddress, 'crmid' => $recordModel->getId()])->exists();
				if ($isCoordinateExists) {
					if (empty($coordinate['lat']) && empty($coordinate['lon'])) {
						$db->createCommand()->delete('u_#__openstreetmap', ['type' => $typeAddress, 'crmid' => $recordModel->getId()])->execute();
					} else {
						$db->createCommand()->update('u_#__openstreetmap', $coordinate, ['type' => $typeAddress, 'crmid' => $recordModel->getId()])->execute();
					}
				} else {
					if (!empty($coordinate['lat']) && !empty($coordinate['lon'])) {
						$coordinate['type'] = $typeAddress;
						$coordinate['crmid'] = $recordModel->getId();
						$db->createCommand()->insert('u_#__openstreetmap', $coordinate)->execute();
					}
				}
			}
		}
		$lastUpdatedCrmId = $row['crmid'];
	}
	$lastRecordId = $db->getUniqueID('vtiger_crmentity', 'crmid', false);
	if ($lastRecordId === $lastUpdatedCrmId) {
		$db->createCommand()->update('u_#__openstreetmap_address_updater', ['crmid' => $lastUpdatedCrmId])->execute();
		$cronTask->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
		$cronTask->set('lockStatus', true);
	} else {
		$db->createCommand()->update('u_#__openstreetmap_address_updater', ['crmid' => $lastUpdatedCrmId])->execute();
	}
}
