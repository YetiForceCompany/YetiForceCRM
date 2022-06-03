<?php
/**
 * Cron task to update coordinates.
 *
 * @package Cron
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * OSSSoldServices_Renewal_Cron class.
 */
class OpenStreetMap_UpdaterCoordinates_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$db = App\Db::getInstance();
		$lastUpdatedCrmId = (new App\Db\Query())->select(['crmid'])->from('u_#__openstreetmap_address_updater')->scalar();
		if (false !== $lastUpdatedCrmId) {
			$dataReader = (new App\Db\Query())->select(['crmid', 'setype', 'deleted'])
				->from('vtiger_crmentity')
				->where(['setype' => \App\Config::module('OpenStreetMap', 'mapModules', [])])
				->andWhere(['>', 'crmid', $lastUpdatedCrmId])
				->limit(App\Config::module('OpenStreetMap', 'cronMaxUpdatedAddresses'))
				->createCommand()->query();
			$moduleModel = OpenStreetMap_Module_Model::getInstance('OpenStreetMap');
			$coordinatesConnector = \App\Map\Coordinates::getInstance();
			while ($row = $dataReader->read()) {
				if ($moduleModel->isAllowModules($row['setype']) && 0 == $row['deleted']) {
					$recordModel = Vtiger_Record_Model::getInstanceById($row['crmid']);
					foreach (\App\Map\Coordinates::TYPE_ADDRESS as $typeAddress) {
						$addressInfo = \App\Map\Coordinates::getAddressParams($recordModel, $typeAddress);
						$coordinate = $coordinatesConnector->getCoordinates($addressInfo);
						if (false === $coordinate) {
							break;
						}
						if (empty($coordinate)) {
							continue;
						}
						$coordinate = reset($coordinate);
						$isCoordinateExists = (new App\Db\Query())->from(OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME)->where(['crmid' => $recordModel->getId(), 'type' => $typeAddress])->exists();
						if ($isCoordinateExists) {
							if (empty($coordinate['lat']) && empty($coordinate['lon'])) {
								$db->createCommand()->delete(OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME, ['crmid' => $recordModel->getId(), 'type' => $typeAddress])->execute();
							} else {
								$db->createCommand()->update(OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME, [
									'lat' => round($coordinate['lat'], 7),
									'lon' => round($coordinate['lon'], 7),
								], ['crmid' => $recordModel->getId(), 'type' => $typeAddress])
									->execute();
							}
						} elseif (!empty($coordinate['lat']) && !empty($coordinate['lon'])) {
							$db->createCommand()->insert(OpenStreetMap_Module_Model::COORDINATES_TABLE_NAME, [
								'lat' => round($coordinate['lat'], 7),
								'lon' => round($coordinate['lon'], 7),
								'type' => $typeAddress,
								'crmid' => $recordModel->getId(),
							])->execute();
						}
					}
				}
				$lastUpdatedCrmId = $row['crmid'];
				if ($this->checkTimeout()) {
					break;
				}
			}
			$dataReader->close();
			$lastRecordId = $db->getUniqueID('vtiger_crmentity', 'crmid', false);
			if ($dataReader->count() || $lastRecordId === $lastUpdatedCrmId) {
				$db->createCommand()->update('u_#__openstreetmap_address_updater', ['crmid' => $lastUpdatedCrmId])->execute();
				$this->cronTask->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
				$this->cronTask->set('lockStatus', true);
			} else {
				$db->createCommand()->update('u_#__openstreetmap_address_updater', ['crmid' => $lastUpdatedCrmId])->execute();
			}
		}
	}
}
