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
		list($recordModel, $inventoryModel, $inventoryData) = $data;
		$moduleName = $recordModel->getModuleName();
		if (in_array($moduleName, ['IGRN', 'IIDN', 'IGDN', 'IGIN'])) {
			$status = strtolower($moduleName) . '_status';
			if ($recordModel->get($status) == 'PLL_ACCEPTED') {
				IStorages_Module_Model::RecalculateStock($moduleName, $inventoryData, $recordModel->get('storageid'), 'add');
			} else {
				$recordId = $recordModel->getId();
				$vtEntityDelta = new VTEntityDelta();
				$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);
				if (is_array($delta) && !empty($delta[$status]) && in_array('PLL_ACCEPTED', $delta[$status])) {
					IStorages_Module_Model::RecalculateStock($moduleName, $inventoryData, $recordModel->get('storageid'), 'remove');
				}
			}
		}
	}
}
