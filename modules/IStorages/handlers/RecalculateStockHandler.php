<?php

/**
 * RecalculateStock Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class RecalculateStockHandler extends VTEventHandler
{

	public function handleEvent($eventName, $data)
	{
		$moduleName = $data->getModuleName();
		$correctionModules = ['IGRNC' => 'igrnid', 'IGDNC' => 'igdnid'];
		if (in_array($moduleName, ['IGRN', 'IIDN', 'IGDN', 'IGIN', 'IPreOrder', 'ISTDN', 'ISTRN', 'IGRNC', 'IGDNC'])) {
			$status = strtolower($moduleName) . '_status';
			// Checks if the module is a correction module
			if (isset($correctionModules[$moduleName])) {
				$relatedModuleField = $correctionModules[$moduleName];
				$relatedModuleRecordId = $data->get($relatedModuleField);
				$relatedModuleRecordModel = Vtiger_Record_Model::getInstanceById($relatedModuleRecordId);
			}
			if ($data->get($status) == 'PLL_ACCEPTED') {
				if (isset($correctionModules[$moduleName])) {
					$this->getInventoryDataAndSend($relatedModuleRecordModel, 'remove');
				}
				$this->getInventoryDataAndSend($data, 'add');
			} else {
				$vtEntityDelta = new VTEntityDelta();
				$delta = $vtEntityDelta->getEntityDelta($moduleName, $data->getId(), true);
				if (is_array($delta) && !empty($delta[$status]) && in_array('PLL_ACCEPTED', $delta[$status])) {
					if (isset($correctionModules[$moduleName])) {
						$this->getInventoryDataAndSend($relatedModuleRecordModel, 'add');
					}
					$this->getInventoryDataAndSend($data, 'remove');
				}
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
