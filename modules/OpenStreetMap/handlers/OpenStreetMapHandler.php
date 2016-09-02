<?php

/**
 * Save geographical coordinates Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMapHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		$recordModel = Vtiger_Record_Model::getInstanceByEntity($data->focus, $data->getId());
		$coordinates = OpenStreetMap_Module_Model::getCoordinatesByRecord($recordModel);
		$db = PearDatabase::getInstance();
		$params = [];
		foreach ($coordinates as $typeAddress => $coordinate) {
			$isCoordinateExists = $db->pquery('SELECT 1 FROM u_yf_openstreetmap WHERE type = ? AND crmid = ?', [$typeAddress, $data->getId()]);
			$isCoordinateExists = $db->getSingleValue($isCoordinateExists);
			if ($isCoordinateExists){
				if (empty($coordinate['lat']) && empty($coordinate['lon'])) {
					$db->delete('u_yf_openstreetmap', 'type = ? AND crmid = ?', [$typeAddress, $data->getId()]);
				} else {
					$db->update('u_yf_openstreetmap', $coordinate, 'type = ? AND crmid = ?', [$typeAddress, $data->getId()]);
				}
			} else {
				if (!empty($coordinate['lat']) && !empty($coordinate['lon'])) {
					$coordinate['type'] = $typeAddress;
					$coordinate['crmid'] = $data->getId();
					$db->insert('u_yf_openstreetmap', $coordinate);
				}
			}
		}
	}
}
