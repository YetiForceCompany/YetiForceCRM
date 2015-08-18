<?php

/**
 * Supplies CheckLimits Action Class
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_CheckLimits_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function process(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$currency = $request->get('currency');
		$price = $request->get('price');
		$limitConfig = $request->get('limitConfig');
		
		$response = new Vtiger_Response();
		$response->setResult($this->checkLimits($record, $currency, $price, $limitConfig));
		$response->emit();
	}

	function checkLimits($record, $currency, $price, $limitConfig)
	{
		$limitFieldName = 'xxx';
		$balanceFieldName = 'limit';
		$moduleInstance = Vtiger_Module_Model::getInstance('Accounts');
		$limitField = Vtiger_Field_Model::getInstance($limitFieldName, $moduleInstance);
		$balanceField = Vtiger_Field_Model::getInstance($limitFieldName, $moduleInstance);
		if (!$limitField->isActiveField() || !$balanceField->isActiveField()) {
			return ['status' => true];
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($record, 'Accounts');
		$limitID = $recordModel->get($limitFieldName);
		$balance = $recordModel->get($balanceFieldName);
		$limit = reset(Vtiger_SuppliesLimit_UIType::getValues($limitID))['value'];

		$baseCurrency = Vtiger_Util_Helper::getBaseCurrency();
		$symbol = $baseCurrency['currency_symbol'];
		if ($baseCurrency['id'] != $currency) {
			$selectedCurrency = Vtiger_Functions::getCurrencySymbolandRate($currency);
			$price = floatval($price) * $selectedCurrency['rate'];
			$symbol = $selectedCurrency['symbol'];
		}
		$totalPrice = $price + $balance;

		$status = $totalPrice > $limit ? false : true;
		if (!$status) {
			$viewer = new Vtiger_Viewer();
			$viewer->assign('PRICE', $price);
			$viewer->assign('BALANCE', $balance);
			$viewer->assign('SYMBOL', $symbol);
			$viewer->assign('LIMIT', $limit);
			$viewer->assign('TOTALS', $totalPrice);
			$viewer->assign('LIMIT_CONFIG', $limitConfig);
			$html = $viewer->view('LimitAlert.tpl', 'Supplies', true);
		}
		return ['status' => $status, 'html' => $html];
	}

}
