<?php

/**
 * Basic Inventory View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Inventory_View extends Vtiger_IndexAjax_View
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showDiscounts');
		$this->exposeMethod('showTaxes');
	}

	public function showDiscounts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$discountType = $request->getInteger('discountType');
		$currency = $request->getInteger('currency');
		$relatedRecord = $request->isEmpty('relatedRecord', true) ? false : $request->getInteger('relatedRecord');
		$totalPrice = $request->getByType('totalPrice', 'Double');
		if (!\App\Privilege::isPermitted($moduleName, 'EditView')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$inventoryModel = Vtiger_Inventory_Model::getInstance($moduleName);
		$config = $inventoryModel->getDiscountsConfig();
		$groupDiscount = $inventoryModel->getAccountDiscount($relatedRecord);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('GLOBAL_DISCOUNTS', $inventoryModel->getGlobalDiscounts());
		$viewer->assign('CURRENCY_SYMBOL', \App\Fields\Currency::getById($currency)['currency_symbol']);
		$viewer->assign('TOTAL_PRICE', $totalPrice);
		$viewer->assign('CONFIG', $config);
		$viewer->assign('DISCOUNT_TYPE', $discountType);
		$viewer->assign('AGGREGATION_TYPE', $config['aggregation']);
		$viewer->assign('AGGREGATION_INPUT_TYPE', $config['aggregation'] == 0 ? 'radio' : 'checkbox');
		$viewer->assign('GROUP_DISCOUNT', $groupDiscount['discount']);
		$viewer->assign('ACCOUNT_NAME', $groupDiscount['name']);
		$viewer->view('InventoryDiscounts.tpl', $moduleName);
	}

	/**
	 * Function to show taxes.
	 *
	 * @param \App\Request $request
	 */
	public function showTaxes(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$recordModule = $request->getByType('recordModule', 'Alnum');
		$currency = $request->getInteger('currency');
		$sourceRecord = $request->isEmpty('sourceRecord', true) ? false : $request->getInteger('sourceRecord');
		$taxType = $request->getInteger('taxType');
		$totalPrice = $request->getByType('totalPrice', 'Double');
		if (!\App\Privilege::isPermitted($moduleName, 'EditView')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$inventoryModel = Vtiger_Inventory_Model::getInstance($moduleName);
		$accountTaxs = $inventoryModel->getAccountTax($moduleName, $sourceRecord);
		$taxField = '';
		if ($recordModule && ($recordModuleModel = \Vtiger_Module_Model::getInstance($recordModule))) {
			$taxField = ($field = current($recordModuleModel->getFieldsByUiType(303))) ? $field->getName() : '';
		}

		$config = $inventoryModel->getTaxesConfig();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD', $record);
		$viewer->assign('RECORD_MODULE', $recordModule);
		$viewer->assign('GLOBAL_TAXES', Vtiger_Inventory_Model::getGlobalTaxes());
		$viewer->assign('CURRENCY_SYMBOL', \App\Fields\Currency::getById($currency)['currency_symbol']);
		$viewer->assign('TOTAL_PRICE', $totalPrice);
		$viewer->assign('CONFIG', $config);
		$viewer->assign('TAX_TYPE', $taxType);
		$viewer->assign('TAX_FIELD', $taxField);
		$viewer->assign('AGGREGATION_TYPE', $config['aggregation']);
		$viewer->assign('AGGREGATION_INPUT_TYPE', $config['aggregation'] == 0 ? 'radio' : 'checkbox');
		$viewer->assign('GROUP_TAXS', $accountTaxs['taxs']);
		$viewer->assign('ACCOUNT_NAME', $accountTaxs['name']);
		$viewer->view('InventoryTaxes.tpl', $moduleName);
	}
}
