<?php

/**
 * RecalculateStock Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_RecalculateStockHandler_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$moduleName = $eventHandler->getModuleName();
		$correctionModules = ['IGRNC' => 'igrnid', 'IGDNC' => 'igdnid'];
		$recordModel = $eventHandler->getRecordModel();
		$status = strtolower($moduleName) . '_status';
		// Checks if the module is a correction module
		if (isset($correctionModules[$moduleName])) {
			$relatedModuleField = $correctionModules[$moduleName];
			$relatedModuleRecordId = $recordModel->get($relatedModuleField);
			$relatedModuleRecordModel = Vtiger_Record_Model::getInstanceById($relatedModuleRecordId);
		}
		if ($recordModel->get($status) === 'PLL_ACCEPTED') {
			if (isset($correctionModules[$moduleName])) {
				$this->getInventoryDataAndSend($relatedModuleRecordModel, 'remove');
			}
			$this->getInventoryDataAndSend($recordModel, 'add');
		} else {
			$delta = $recordModel->getPreviousValue($status);
			if ($delta && 'PLL_ACCEPTED' === $delta) {
				if (isset($correctionModules[$moduleName])) {
					$this->getInventoryDataAndSend($relatedModuleRecordModel, 'add');
				}
				$this->getInventoryDataAndSend($recordModel, 'remove');
			}
		}
	}

	public function getInventoryDataAndSend(Vtiger_Record_Model $recordModel, $action)
	{
		$moduleName = $recordModel->getModuleName();
		$inventoryData = $recordModel->getInventoryData();
		if (!empty($inventoryData) && $recordModel->get('storageid')) {
			IStorages_Module_Model::recalculateStock($moduleName, $inventoryData, $recordModel->get('storageid'), $action);
		}
	}
}
