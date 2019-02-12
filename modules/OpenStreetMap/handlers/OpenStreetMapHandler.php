<?php

/**
 * Save geographical coordinates Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_OpenStreetMapHandler_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$fieldAddress = [
			'addresslevel', 'buildingnumber', 'localnumber', 'pobox',
		];
		$typeAddressToUpdate = [];
		$recordModel = $eventHandler->getRecordModel();
		if (!$recordModel->isNew()) {
			$deltaFields = array_keys($recordModel->getPreviousValue());
			foreach ($deltaFields as &$deltaField) {
				if ($recordModel->getPreviousValue($deltaField) !== $recordModel->get($deltaField)) {
					foreach ($fieldAddress as &$field) {
						if (strpos($deltaField, $field) !== false) {
							$typeAddressToUpdate[] = substr($deltaField, -1);
						}
					}
				}
			}
		}
		foreach (\App\Map\Coordinates::TYPE_ADDRES as $typeAddress) {
			if (!$recordModel->isEmpty('addresslevel5' . $typeAddress) && ($recordModel->isNew() || in_array($typeAddress, $typeAddressToUpdate))) {
				$isCoordinateExists = (new App\Db\Query())
					->from('u_#__openstreetmap_record_updater')
					->where(['type' => $typeAddress, 'crmid' => $recordModel->getId()])
					->exists();
				$address = \App\Map\Coordinates::getAddressParams($recordModel, $typeAddress);
				if (!$isCoordinateExists) {
					App\Db::getInstance()->createCommand()->insert('u_#__openstreetmap_record_updater', [
						'crmid' => $recordModel->getId(),
						'type' => $typeAddress,
						'address' => \App\Json::encode($address),
					])->execute();
				} else {
					App\Db::getInstance()->createCommand()
						->update('u_#__openstreetmap_record_updater', ['address' => \App\Json::encode($address)], ['crmid' => $recordModel->getId(), 'type' => $typeAddress])
						->execute();
				}
			}
		}
	}
}
