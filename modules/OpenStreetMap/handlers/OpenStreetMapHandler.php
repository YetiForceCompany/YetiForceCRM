<?php

/**
 * Save geographical coordinates Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMapHandler extends VTEventHandler
{

	public function handleEvent($eventName, $data)
	{
		$fieldAddress = [
			'addresslevel', 'buildingnumber', 'localnumber', 'pobox'
		];
		$moduleName = $data->getModuleName();
		$recordModel = Vtiger_Record_Model::getInstanceByEntity($data->focus, $data->getId());
		$db = PearDatabase::getInstance();
		$typeAddressToUpdate = [];
		if ($data->focus->mode == 'edit') {
			$vtEntityDelta = new VTEntityDelta();
			$delta = $vtEntityDelta->getEntityDelta($moduleName, $data->getId(), true);
			$deltaFields = array_keys($delta);
			foreach ($deltaFields as $deltaField) {
				foreach ($fieldAddress as $field) {
					if (strpos($deltaField, $field) !== false) {
						$typeAddressToUpdate [] = substr($deltaField, -1);
					}
				}
			}
		}
		foreach (['a', 'b', 'c'] as $typeAddress) {
			if (!$recordModel->isEmpty('addresslevel5' . $typeAddress) && ($data->focus->mode != 'edit' || in_array($typeAddress, $typeAddressToUpdate))) {
				$isCoordinateExists = $db->pquery('SELECT 1 FROM u_yf_openstreetmap_record_updater WHERE type = ? && crmid = ?', [$typeAddress, $data->getId()]);
				$isCoordinateExists = $db->getSingleValue($isCoordinateExists);
				if (!$isCoordinateExists) {
					$address = OpenStreetMap_Module_Model::getUrlParamsToSearching($recordModel, $typeAddress);
					$db->insert('u_yf_openstreetmap_record_updater', [
						'crmid' => $data->getId(),
						'type' => $typeAddress,
						'address' => \includes\utils\Json::encode($address)
					]);
				}
			}
		}
	}
}
