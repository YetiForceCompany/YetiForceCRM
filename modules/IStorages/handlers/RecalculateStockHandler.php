<?php

/**
 * RecalculateStock Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class RecalculateStockHandler extends VTEventHandler
{

	function handleEvent($eventName, $data)
	{
		$moduleName = $data->getModuleName();
		if (in_array($moduleName, ['IGRN', 'IIDN', 'IGDN', 'IGIN', 'IPreOrder', 'ISTDN', 'ISTRN'])) {
			$status = strtolower($moduleName) . '_status';
			if ($data->get($status) == 'PLL_ACCEPTED') {
				$this->getInventoryDataAndSend($data, 'add');
			} else {
				$vtEntityDelta = new VTEntityDelta();
				$delta = $vtEntityDelta->getEntityDelta($moduleName, $data->getId(), true);
				if (is_array($delta) && !empty($delta[$status]) && in_array('PLL_ACCEPTED', $delta[$status])) {
					$this->getInventoryDataAndSend($data, 'remove');
				}
			}
		}
	}

	function getInventoryDataAndSend($data, $action)
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
