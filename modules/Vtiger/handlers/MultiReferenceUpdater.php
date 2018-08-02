<?php

/**
 * Multi Reference Updater Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_MultiReferenceUpdater_Handler
{
	/**
	 * EntityAfterLink handler function.
	 *
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
	 * EntityAfterUnLink handler function.
	 *
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
			$uitypeModel->reloadValue($params['sourceModule'], $params['sourceRecordId']);
		}
	}

	/**
	 * EntityAfterTransferLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterTransferLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		$fields = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($params['sourceModule'], $params['destinationModule']);
		foreach ($fields as &$field) {
			$fieldModel = new Vtiger_Field_Model();
			$fieldModel->initialize($field);
			$uitypeModel = $fieldModel->getUITypeModel();
			$uitypeModel->reloadValue($params['sourceModule'], $params['sourceRecordId']);
		}
	}

	/**
	 * EntityAfterTransferUnLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterTransferUnLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		$fields = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($params['sourceModule'], $params['destinationModule']);
		foreach ($fields as &$field) {
			$fieldModel = new Vtiger_Field_Model();
			$fieldModel->initialize($field);
			$uitypeModel = $fieldModel->getUITypeModel();
			$uitypeModel->reloadValue($params['sourceModule'], $params['sourceRecordId']);
		}
	}

	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$moduleName = $eventHandler->getModuleName();
		$moduleIds = Vtiger_MultiReferenceValue_UIType::getMultiReferenceModules($moduleName);
		if ($moduleIds) {
			$previousValue = $recordModel->getPreviousValue();
			$referenceFields = $recordModel->getModule()->getFieldsByReference();
			foreach ($referenceFields as $fieldName => $fieldModel) {
				if (isset($previousValue[$fieldName]) && !$recordModel->isNew()) {
					$module = \App\Record::getType($previousValue[$fieldName]);
					if ($module && in_array(\App\Module::getModuleId($module), $moduleIds)) {
						Vtiger_MultiReferenceValue_UIType::setRecordToCron($module, $moduleName, $previousValue[$fieldName]);
					}
				}
				$module = \App\Record::getType($recordModel->get($fieldName));
				if ($module && in_array(\App\Module::getModuleId($module), $moduleIds)) {
					Vtiger_MultiReferenceValue_UIType::setRecordToCron($module, $moduleName, $recordModel->get($fieldName));
				}
			}
		}
	}
}
