<?php

/**
 * Multi Reference Updater Handler Class
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_MultiReferenceUpdater_Handler extends VTEventHandler
{

	public function handleEvent($eventName, $entityData)
	{
		if (in_array($eventName, ['vtiger.entity.link.after', 'vtiger.entity.unlink.after'])) {
			$fields = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($entityData['sourceModule'], $entityData['destinationModule']);
			foreach ($fields as $field) {
				$fieldModel = new Vtiger_Field_Model();
				$fieldModel->initialize($field);
				$UITypeModel = $fieldModel->getUITypeModel();

				if ($eventName == 'vtiger.entity.link.after') {
					$UITypeModel->addValue($entityData['CRMEntity'], $entityData['sourceRecordId'], $entityData['destinationRecordId']);
				} elseif ($eventName == 'vtiger.entity.unlink.after') {
					$UITypeModel->removeValue(CRMEntity::getInstance($entityData['sourceModule']), $entityData['sourceRecordId'], $entityData['destinationRecordId']);
				}
			}
		}
	}
}
