<?php

class Products_Taxs_View extends Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$currency = $request->get('currency');
		$sourceModule = $request->get('sourceModule');
		$sourceRecord = $request->get('sourceRecord');
		$isIndividual = $request->get('isIndividual');
		$totalPrice = $request->get('totalPrice');

		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$config = $recordModel->getTaxsConfig();
		//$groupDiscount = $this->getGroupTaxs($sourceModule, $sourceRecord);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('GLOBAL_TAXS', $recordModel->getGlobalTaxs());
		$viewer->assign('CURRENCY_SYMBOL', Vtiger_Functions::getCurrencySymbolandRate($currency)['symbol']);
		$viewer->assign('TOTAL_PRICE', $totalPrice);
		$viewer->assign('CONFIG', $config);
		$viewer->assign('AGGREGATION_TYPE', $config['aggregation']);
		$viewer->assign('AGGREGATION_INPUT_TYPE', $config['aggregation'] == 0 ? 'radio' : 'checkbox');
		//$viewer->assign('GROUP_TAX', $groupDiscount['discount']);
		$viewer->view('Taxs.tpl', $moduleName);
	}

	public function getGroupTaxs($moduleName, $record)
	{
		$discount = 0;
		$taxField = 'discount';

		if ($record != '') {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$relationFieldValue = $recordModel->get($accountField);

		}

		return ['discount' => $discount, 'accountid' => $relationFieldValue];
	}
}
