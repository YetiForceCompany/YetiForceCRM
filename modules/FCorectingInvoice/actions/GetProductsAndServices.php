<?php

/**
 * FCorectingInvoice GetProductsAndServices Action.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class FCorectingInvoice_GetProductsAndServices_Action extends Vtiger_BasicAjax_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted('FInvoice', 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
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
		unset($response, $data, $recordModel);
	}
}
