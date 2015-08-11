<?php

class Products_Discounts_View extends Vtiger_Index_View
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
		$accountField = $request->get('accountField');

		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$config = $recordModel->getDiscountsConfig();
		$groupDiscount = $this->getGroupDiscount($sourceModule, $sourceRecord, $accountField);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('GLOBAL_DISCOUNTS', $recordModel->getGlobalDiscounts());
		$viewer->assign('TOTAL_PRICE', $totalPrice);
		$viewer->assign('CONFIG', $config);
		$viewer->assign('AGGREGATION_TYPE', $config['aggregation']);
		$viewer->assign('AGGREGATION_INPUT_TYPE', $config['aggregation'] == 0 ? 'radio' : 'checkbox');
		$viewer->assign('GROUP_DISCOUNT', $groupDiscount['discount']);
		$viewer->assign('ACCOUNT_ID', $groupDiscount['accountid']);
		$viewer->view('Discounts.tpl', $moduleName);
	}

	public function getGroupDiscount($moduleName, $record, $accountField)
	{
		$discount = 0;
		$discountField = 'discount';

		if ($accountField != '') {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$relationFieldValue = $recordModel->get($accountField);
			if ($relationFieldValue != 0) {
				$mainRecordModel = Vtiger_Record_Model::getInstanceById($relationFieldValue, $mainModule);
				$discount = $mainRecordModel->get($discountField);
			}
		}

		return ['discount' => $discount, 'accountid' => $relationFieldValue];
	}
}
