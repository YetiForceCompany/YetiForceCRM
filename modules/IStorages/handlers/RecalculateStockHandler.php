<?php

/**
 * RecalculateStock Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_RecalculateStockHandler_Handler
{

	/**
	 * EntityAfterSave handler function
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

	public function getInventoryDataAndSend($data, $action)
	{
		$moduleName = $data->getModuleName();
		if ($data->focus->inventoryData) {
			$inventoryData = $data->focus->inventoryData;
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('id', $data->getId());
			$inventoryData = $recordModel->getInventoryData();
		}
		if (!empty($inventoryData)) {
			IStorages_Module_Model::RecalculateStock($moduleName, $inventoryData, $data->get('storageid'), $action);
		}
	}
}
