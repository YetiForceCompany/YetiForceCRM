<?php

/**
 * Supplies Inventory Action Class
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Inventory_Action extends Vtiger_Action_Controller
{

	function __construct()
	{
		$this->exposeMethod('getUnitPrice');
	}

	function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();

		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	function getUnitPrice(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$recordModule = $request->get('recordModule');
		$moduleName = $request->getModule();
		$unitPriceValues = false;

		if (in_array($recordModule, ['Products', 'Services'])) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $recordModule);
			$unitPriceValues = $recordModel->getListPriceValues($record);
		}
		$response = new Vtiger_Response();
		$response->setResult($unitPriceValues);
		$response->emit();
	}
}
