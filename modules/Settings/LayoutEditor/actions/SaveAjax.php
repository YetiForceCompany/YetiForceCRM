<?php

/**
 * Save Inventory Action Class
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_LayoutEditor_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('setInventory');
		$this->exposeMethod('saveInventoryField');
	}

	public function setInventory(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$moduleName = $param['module'];
		$status = false;
		$inventoryInstance = Vtiger_Inventory_Model::getInstance($moduleName);
		$status = $inventoryInstance->setInventoryTable($param['status']);
		if ($status) {
			$status = true;
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $status]
		);
		$response->emit();
	}

	public function saveInventoryField(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$moduleName = $param['module'];
		$name = $param['name'];
		$id = $param['id'];
		$edit = false;
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		if ($id) {
			$return = $inventoryField->saveField($param);
			$edit = true;
		} else {
			$return = $inventoryField->addField($name, $param);
			$id = $return['id'];
		}
		$arrayInstane = $inventoryField->getFields(false, [$id]);
		if (current($arrayInstane)) {
			$data = current($arrayInstane)->getData();
		}
		$response = new Vtiger_Response();
		$response->setResult(['data' => $data, 'edit' => $edit]);
		$response->emit();
	}
}
