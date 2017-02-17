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
	public $allowedMethod = ['GET', 'DELETE', 'PUT', 'POST'];

	/**
	 * Record model
	 * @var \Vtiger_Record_Model 
	 */
	protected $recordModel = false;

	/**
	 * Check permission to method
	 * @return boolean
	 * @throws \Api\Core\Exception
	 */
	public function checkPermission()
	{
		parent::checkPermission();
		$moduleName = $this->controller->request->get('module');
		$method = $this->controller->method;
		if ('POST' === $method) {
			$this->recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
			if (!$this->recordModel->isCreateable()) {
				throw new \Api\Core\Exception('No permissions to create record', 401);
			}
		} else {
			$record = $this->controller->request->get('record');
			if (!$record || !\App\Record::isExists($record, $moduleName)) {
				throw new \Api\Core\Exception("Record doesn't exist", 401);
			}
			$this->recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
			switch ($method) {
				case 'DELETE':
					if (!$this->recordModel->isDeletable()) {
						throw new \Api\Core\Exception('No permissions to remove record', 401);
					}
					break;
				case 'GET':
					if (!$this->recordModel->isViewable()) {
						throw new \Api\Core\Exception('No permissions to view record', 401);
					}
					break;
				case 'PUT':
					if (!$this->recordModel->isEditable()) {
						throw new \Api\Core\Exception('No permissions to edit record', 401);
					}
					break;
				default:
					break;
			}
		}
	}

	/**
	 * Get record detail
	 * @return array
	 */
	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$record = $this->controller->request->get('record');
		$recordModel = $this->recordModel;
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
		$this->recordModel->delete();
		return true;
	}

	/**
	 * Edit record
	 * @return array
	 */
	public function put()
	{
		$recordData = $this->controller->request->get('recordData');
		$moduleName = $this->controller->request->get('module');
		$result = false;
		$newRequest = new \Vtiger_Request($recordData);
		$modelClassName = \Vtiger_Loader::getComponentClassName('Action', 'Save', $moduleName);
		$saveClass = new $modelClassName();
		$recordModel = $saveClass->saveRecord($newRequest);
		return ['id' => $recordModel->getId()];
	}

	/**
	 * Create record
	 * @return array
	 */
	public function post()
	{
		$recordData = $this->controller->request->get('recordData');
		$moduleName = $this->controller->request->get('module');
		$newRequest = new \Vtiger_Request($recordData);
		$modelClassName = \Vtiger_Loader::getComponentClassName('Action', 'Save', $moduleName);
		$saveClass = new $modelClassName();
		$recordModel = $saveClass->saveRecord($newRequest);
		return ['id' => $recordModel->getId()];
	}
}
