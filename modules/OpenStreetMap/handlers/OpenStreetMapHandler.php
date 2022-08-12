<?php

/**
 * Save geographical coordinates handler file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 *  Save geographical coordinates handler class.
 */
class OpenStreetMap_OpenStreetMapHandler_Handler
{
	/** @var string[] Type of address. */
	const FIELDS_ADDRESS_TYPE = ['addresslevel', 'buildingnumber', 'localnumber', 'pobox'];

	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return void
	 */
	public function entityAfterSave(App\EventHandler $eventHandler): void
	{
		$typeAddressToUpdate = [];
		$recordModel = $eventHandler->getRecordModel();
		if (!$recordModel->isNew()) {
			$deltaFields = array_keys($recordModel->getPreviousValue());
			foreach ($deltaFields as &$deltaField) {
				if ($recordModel->getPreviousValue($deltaField) !== $recordModel->get($deltaField)) {
					foreach (self::FIELDS_ADDRESS_TYPE as $field) {
						if (false !== strpos($deltaField, $field)) {
							$typeAddressToUpdate[] = substr($deltaField, -1);
						}
					}
				}
			}
		}
		foreach (\App\Map\Coordinates::TYPE_ADDRESS as $typeAddress) {
			if ((!$recordModel->isEmpty('addresslevel5' . $typeAddress) || !$recordModel->isEmpty('addresslevel4' . $typeAddress) || !$recordModel->isEmpty('addresslevel3' . $typeAddress) || !$recordModel->isEmpty('addresslevel2' . $typeAddress)) && ($recordModel->isNew() || \in_array($typeAddress, $typeAddressToUpdate))) {
				$isCoordinateExists = (new App\Db\Query())
					->from('u_#__openstreetmap_record_updater')
					->where(['type' => $typeAddress, 'crmid' => $recordModel->getId()])
					->exists();
				$address = \App\Map\Coordinates::getAddressParams($recordModel, $typeAddress);
				if (!$isCoordinateExists) {
					App\Db::getInstance()->createCommand()
						->insert('u_#__openstreetmap_record_updater', [
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
