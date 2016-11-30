<?php

/**
 * Multi Reference Updater Handler Class
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_MultiReferenceUpdater_Handler
{

	/**
	 * EntityAfterLink handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		$fields = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($params['sourceModule'], $params['destinationModule']);
		foreach ($fields as &$field) {
			$fieldModel = new Vtiger_Field_Model();
			$fieldModel->initialize($field);
			$uitypeModel = $fieldModel->getUITypeModel();
			$uitypeModel->addValue($params['CRMEntity'], $params['sourceRecordId'], $params['destinationRecordId']);
		}
	}

	/**
	 * EntityAfterUnLink handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterUnLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		$fields = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($params['sourceModule'], $params['destinationModule']);
		foreach ($fields as &$field) {
			$fieldModel = new Vtiger_Field_Model();
			$fieldModel->initialize($field);
			$uitypeModel = $fieldModel->getUITypeModel();
			$uitypeModel->removeValue(CRMEntity::getInstance($params['sourceModule']), $params['sourceRecordId'], $params['destinationRecordId']);
		}
	}
}
