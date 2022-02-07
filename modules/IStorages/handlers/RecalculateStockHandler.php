<?php

/**
 * RecalculateStock Handler Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		if ('PLL_ACCEPTED' === $recordModel->get($status)) {
			if (isset($correctionModules[$moduleName])) {
				$this->updateStock($relatedModuleRecordModel, 'remove');
			}
			$this->updateStock($recordModel, 'add');
		} else {
			$delta = $recordModel->getPreviousValue($status);
			if ($delta && 'PLL_ACCEPTED' === $delta) {
				if (isset($correctionModules[$moduleName])) {
					$this->updateStock($relatedModuleRecordModel, 'add');
				}
				$this->updateStock($recordModel, 'remove');
			}
		}
	}

	/**
	 * Update stock.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 * @param string              $action
	 *
	 * @return void
	 */
	public function updateStock(Vtiger_Record_Model $recordModel, string $action): void
	{
		$inventoryData = $recordModel->getInventoryData();
		if (!empty($inventoryData) && $recordModel->get('storageid')) {
			IStorages_Module_Model::setQtyInStock($recordModel->getModuleName(), $inventoryData, $recordModel->get('storageid'), $action);
		}
	}

	/**
	 * IStoragesAfterUpdateStock handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function iStoragesAfterUpdateStock(App\EventHandler $eventHandler)
	{
		$eventHandler->getParams();
		$storageId = $eventHandler->getParams()['storageId'];
		foreach ((array_keys($eventHandler->getParams()['products']) ?? []) as $productId) {
			(new \App\BatchMethod(['method' => 'App\Integrations\Magento\Controller::updateStock', 'params' => [
				'storageId' => $storageId,
				'product' => $productId,
			]]))->save();
		}
	}
}
