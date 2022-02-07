<?php

/**
 * Multi Reference Updater Handler Class.
 *
 * @package		Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$sourceModule = $params['sourceModule'];
		$destinationModule = $params['destinationModule'];
		if (\Vtiger_MultiReferenceValue_UIType::getFieldsByModules($sourceModule, $destinationModule)) {
			Vtiger_MultiReferenceValue_UIType::setRecordToCron($sourceModule, $destinationModule, $params['sourceRecordId']);
		}
		if (\Vtiger_MultiReferenceValue_UIType::getFieldsByModules($destinationModule, $sourceModule)) {
			Vtiger_MultiReferenceValue_UIType::setRecordToCron($destinationModule, $sourceModule, $params['destinationRecordId']);
		}
	}

	/**
	 * EntityAfterUnLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterUnLink(App\EventHandler $eventHandler)
	{
		$this->entityAfterLink($eventHandler);
	}

	/**
	 * EntityAfterTransferLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterTransferLink(App\EventHandler $eventHandler)
	{
		$this->entityAfterLink($eventHandler);
	}

	/**
	 * EntityAfterTransferUnLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterTransferUnLink(App\EventHandler $eventHandler)
	{
		$this->entityAfterLink($eventHandler);
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
			$referenceFields = $recordModel->getModule()->getFieldsByReference();
			foreach ($referenceFields as $fieldName => $fieldModel) {
				if (!$recordModel->isNew() && false !== $recordModel->getPreviousValue($fieldName) && $fieldModel->isActiveField()) {
					$recordId = $recordModel->getPreviousValue($fieldName);
					$module = \App\Record::getType($recordId);
					if ($module && \in_array(\App\Module::getModuleId($module), $moduleIds)) {
						Vtiger_MultiReferenceValue_UIType::setRecordToCron($module, $moduleName, $recordId);
					}
				}
				$module = \App\Record::getType($recordModel->get($fieldName));
				if ($module && \in_array(\App\Module::getModuleId($module), $moduleIds)) {
					Vtiger_MultiReferenceValue_UIType::setRecordToCron($module, $moduleName, $recordModel->get($fieldName));
				}
			}
		}
	}
}
