<?php

class Products_Taxs_View extends Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$recordModule = $request->get('recordModule');
		$currency = $request->get('currency');
		$sourceModule = $request->get('sourceModule');
		$sourceRecord = $request->get('sourceRecord');
		$isIndividual = $request->get('isIndividual');
		$totalPrice = $request->get('totalPrice');
		$accountField = $request->get('accountField');

		$accountTaxs = $this->getAccountTax($sourceModule, $sourceRecord, $accountField);

		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$config = $recordModel->getTaxsConfig();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD', $record);
		$viewer->assign('RECORD_MODULE', $recordModule);
		$viewer->assign('GLOBAL_TAXS', $recordModel->getGlobalTaxs());
		$viewer->assign('CURRENCY_SYMBOL', Vtiger_Functions::getCurrencySymbolandRate($currency)['symbol']);
		$viewer->assign('TOTAL_PRICE', $totalPrice);
		$viewer->assign('CONFIG', $config);
		$viewer->assign('TAX_FIELD', Supplies_EditView_Model::getTaxField($recordModule));
		$viewer->assign('AGGREGATION_TYPE', $config['aggregation']);
		$viewer->assign('AGGREGATION_INPUT_TYPE', $config['aggregation'] == 0 ? 'radio' : 'checkbox');
		$viewer->assign('GROUP_TAXS', $accountTaxs['taxs']);
		$viewer->assign('ACCOUNT_NAME', $accountTaxs['name']);
		$viewer->view('Taxs.tpl', $moduleName);
	}

	public function getAccountTax($moduleName, $record, $accountField)
	{
		$accountTaxs = [];
		$name = '';
		$taxField = Supplies_EditView_Model::getTaxField('Accounts');
		if ($accountField != '' && $taxField != false) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$relationFieldValue = $recordModel->get($accountField);
			if ($relationFieldValue != 0) {
				$accountRecordModel = Vtiger_Record_Model::getInstanceById($relationFieldValue, 'Accounts');
				$accountTaxs = Vtiger_Taxs_UIType::getValues($accountRecordModel->get($taxField));
				$name = $accountRecordModel->getName();
			}
		}

		return ['taxs' => $accountTaxs, 'name' => $name];
	}
}
