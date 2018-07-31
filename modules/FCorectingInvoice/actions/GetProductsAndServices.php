<?php

class FCorectingInvoice_GetProductsAndServices_Action extends Vtiger_BasicAjax_Action
{
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted('FInvoice', 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$recordModel = FInvoice_Record_Model::getInstanceById($request->getInteger('record'));
		if (!$recordModel->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$data = $recordModel->getInventoryData();
		foreach ($data as &$item) {
			$item['info'] = (new Vtiger_Inventory_Action())->getRecordDetail($item['name'], $item['currency'], 'FInvoice', 'name')[$item['name']];
		}
		unset($item);
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
		unset($response,$data,$recordModel);
	}
}
