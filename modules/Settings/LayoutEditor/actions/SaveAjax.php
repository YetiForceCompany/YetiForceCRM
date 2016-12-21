<?php

/**
 * Save Inventory Action Class
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_LayoutEditor_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('setInventory');
		$this->exposeMethod('saveInventoryField');
		$this->exposeMethod('saveSequence');
		$this->exposeMethod('delete');
	}

	public function setInventory(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$moduleName = $param['module'];
		$status = false;
		$inventoryInstance = Vtiger_Inventory_Model::getInstance($moduleName);
		$status = $inventoryInstance->setMode($param['status']);
		if ($status) {
			$status = true;
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $status]
		);
		$response->emit();
	}

	/**
	 * Function is used to create and edit fields in advanced block
	 * @param Vtiger_Request $request
	 */
	public function saveInventoryField(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$moduleName = $param['module'];
		$name = $param['name'];
		$id = $param['id'];
		$edit = false;
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		if (!empty($id)) {
			$return = $inventoryField->saveField($name, $param);
			$edit = true;
		} else {
			$id = $inventoryField->addField($name, $param);
		}
		$arrayInstane = $inventoryField->getFields(false, [$id], 'Settings');
		$data = [];
		if (current($arrayInstane)) {
			$data = current($arrayInstane)->getData();
			$data['translate'] = vtranslate($data['label'], $moduleName);
		}
		$response = new Vtiger_Response();
		$response->setResult(['data' => $data, 'edit' => $edit]);
		$response->emit();
	}

	public function saveSequence(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$moduleName = $param['module'];
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		$status = $inventoryField->saveSequence($param['ids']);
		if ($status) {
			$status = true;
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => $status]);
		$response->emit();
	}

	public function delete(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$moduleName = $param['module'];
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		$status = $inventoryField->delete($param);
		if ($status) {
			$status = true;
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => $status]);
		$response->emit();
	}
}
