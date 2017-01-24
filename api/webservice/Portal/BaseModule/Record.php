<?php
namespace Api\Portal\BaseModule;

/**
 * Get record detail class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Record extends \Api\Core\BaseAction
{

	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET', 'DELETE'];

	/**
	 * Get record detail
	 * @return array
	 */
	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$record = $this->controller->request->get('record');
		$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$rawData = $recordModel->getData();
		$moduleModel = $recordModel->getModule();

		$displayData = [];
		$moduleBlockFields = \Vtiger_Field_Model::getAllForModule($moduleModel);
		foreach ($moduleBlockFields as $moduleFields) {
			foreach ($moduleFields as $moduleField) {
				$block = $moduleField->get('block');
				if (empty($block)) {
					continue;
				}
				$blockLabel = \App\Language::translate($block->label, $moduleName);
				$fieldLabel = \App\Language::translate($moduleField->get('label'), $moduleName);
				$displayData[$blockLabel][$fieldLabel] = $recordModel->getDisplayValue($moduleField->getName(), $record, true);
			}
		}

		$inventory = false;
		if ($recordModel->getModule()->isInventory()) {
			$rawInventory = $recordModel->getInventoryData();
			$inventory = [];
			$inventoryField = \Vtiger_InventoryField_Model::getInstance($moduleName);
			$inventoryFields = $inventoryField->getFields();
			foreach ($rawInventory as $row) {
				$inventoryRow = [];
				foreach ($inventoryFields as $name => $field) {
					$inventoryRow[$name] = $field->getDisplayValue($row[$name]);
				}
				$inventory[] = $inventoryRow;
			}
		}
		$resposne = [
			'data' => $displayData,
			'inventory' => $inventory
		];
		if ((int) $this->controller->headers['X-RAW-DATA'] === 1) {
			$resposne['rawData'] = $rawData;
			$resposne['rawInventory'] = $rawInventory;
		}
		return $resposne;
	}

	/**
	 * Delete record
	 * @return bool
	 */
	public function delete()
	{
		$moduleName = $this->controller->request->get('module');
		$record = $this->controller->request->get('record');
		$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$status = false;
		if ($recordModel->isDeletable()) {
			$recordModel->delete();
			$status = true;
		}
		return $status;
	}
}
