<?php
namespace Api\Portal\BaseModule;

/**
 * Get record detail class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Record extends \Api\Core\BaseAction
{

	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET', 'DELETE', 'PUT'];

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

		$displayData = $fieldsLabel = [];
		$moduleBlockFields = \Vtiger_Field_Model::getAllForModule($moduleModel);
		foreach ($moduleBlockFields as $moduleFields) {
			foreach ($moduleFields as $moduleField) {
				$block = $moduleField->get('block');
				if (empty($block)) {
					continue;
				}
				$fieldLabel = \App\Language::translate($moduleField->get('label'), $moduleName);
				$displayData[$moduleField->getName()] = $recordModel->getDisplayValue($moduleField->getName(), $record, true);
				$fieldsLabel[$moduleField->getName()] = $fieldLabel;
				if ($moduleField->isReferenceField()) {
					$refereneModule = $moduleField->getUITypeModel()->getReferenceModule($recordModel->get($moduleField->getName()));
					$rawData[$moduleField->getName() . '_module'] = $refereneModule ? $refereneModule->getName() : null;
				}
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
			'name' => $recordModel->getName(),
			'id' => $recordModel->getId(),
			'fields' => $fieldsLabel,
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

	/**
	 * Save record
	 * @return array
	 */
	public function put()
	{
		$recordData = $this->controller->request->get('recordData');
		$moduleName = $this->controller->request->get('module');
		$record = $this->controller->request->get('record');
		$result = false;
		if ($record) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		} else {
			$recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
		}
		foreach ($recordData as $key => $value) {
			$recordModel->set($key, $value);
		}
		if (!$record && $recordModel->isCreateable() || $record && $recordModel->isEditable()) {
			$recordModel->save();
			$result = true;
		} else {
			$message = \App\Language::translate('Permission to perform the operation is denied');
		}
		return ['id' => $recordModel->getId(), 'result' => $result, 'message' => $message];
	}
}
