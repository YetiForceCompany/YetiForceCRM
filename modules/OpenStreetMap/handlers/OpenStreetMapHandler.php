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
		$typeAddressToUpdate = [];
		if ($data->focus->mode === 'edit') {
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
			if (!$recordModel->isEmpty('addresslevel5' . $typeAddress) && ($data->focus->mode !== 'edit' || in_array($typeAddress, $typeAddressToUpdate))) {
				$isCoordinateExists = (new App\Db\Query())
						->from('u_yf_openstreetmap_record_updater')
						->where(['type' => $typeAddress, 'crmid' => $data->getId()])
						->exists();
				$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
				$address = $coordinatesModel->getUrlParamsToSearching($recordModel, $typeAddress);
				if (!$isCoordinateExists) {
					App\Db::getInstance()->createCommand()->insert('u_yf_openstreetmap_record_updater', [
						'crmid' => $data->getId(),
						'type' => $typeAddress,
						'address' => \includes\utils\Json::encode($address)
					])->execute();
				} else {
					App\Db::getInstance()->createCommand()
						->update('u_yf_openstreetmap_record_updater', ['address' => \includes\utils\Json::encode($address)], ['crmid' => $data->getId(), 'type' => $typeAddress])
						->execute();
				}
			}
		}
	}
}
