<?php

/**
 * Basic Inventory Action Class
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Inventory_Action extends Vtiger_Action_Controller
{

	public function __construct()
	{
		$this->exposeMethod('checkLimits');
		$this->exposeMethod('getUnitPrice');
		$this->exposeMethod('getDetails');
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();

		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Function verifies whether the Account's credit limit has been reached
	 * @param Vtiger_Request $request
	 */
	public function checkLimits(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$currency = $request->get('currency');
		$price = $request->get('price');
		$limitFieldName = 'creditlimit';
		$balanceFieldName = 'inventorybalance';
		$response = new Vtiger_Response();

		$moduleInstance = Vtiger_Module_Model::getInstance('Accounts');
		$limitField = Vtiger_Field_Model::getInstance($limitFieldName, $moduleInstance);
		$balanceField = Vtiger_Field_Model::getInstance($balanceFieldName, $moduleInstance);
		if (!$limitField->isActiveField() || !$balanceField->isActiveField()) {
			$response->setResult(['status' => true]);
			$response->emit();
			return;
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($record, 'Accounts');
		$limitID = $recordModel->get($limitFieldName);
		$balance = $recordModel->get($balanceFieldName);
		if (!empty($limitID)) {
			$limit = Vtiger_InventoryLimit_UIType::getValues($limitID)['value'];
		} else {
			$response->setResult(['status' => true]);
			$response->emit();
			return;
		}

		$baseCurrency = Vtiger_Util_Helper::getBaseCurrency();
		$symbol = $baseCurrency['currency_symbol'];
		if ($baseCurrency['id'] != $currency) {
			$selectedCurrency = vtlib\Functions::getCurrencySymbolandRate($currency);
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
			$html = $viewer->view('InventoryLimitAlert.tpl', $moduleName, true);
		}

		$response->setResult([
			'status' => $status,
			'html' => $html
		]);
		$response->emit();
	}

	public function getUnitPrice(Vtiger_Request $request)
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

	public function getDetails(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$idList = $request->get('idlist');
		$currencyId = $request->get('currency_id');
		$fieldName = $request->get('fieldname');
		$moduleName = $request->getModule();

		if (empty($idList)) {
			$info = $this->getRecordDetail($recordId, $currencyId, $moduleName, $fieldName);
		} else {
			foreach ($idList as $id) {
				$info[] = $this->getRecordDetail($id, $currencyId, $moduleName, $fieldName);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($info);
		$response->emit();
	}

	public function getRecordDetail($recordId, $currencyId, $moduleName, $fieldName)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$recordModuleName = $recordModel->getModuleName();
		$info = [
			'id' => $recordId,
			'name' => decode_html($recordModel->getName()),
			'description' => $recordModel->get('description'),
		];
		if (in_array($recordModuleName, ['Products', 'Services'])) {
			$conversionRate = 1;
			$info['unitPriceValues'] = $recordModel->getListPriceValues($recordModel->getId());
			$priceDetails = $recordModel->getPriceDetails();
			foreach ($priceDetails as $currencyDetails) {
				if ($currencyId == $currencyDetails['curid']) {
					$conversionRate = $currencyDetails['conversionrate'];
				}
			}
			$info['price'] = (float) $recordModel->get('unit_price') * (float) $conversionRate;
		}
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		$autoCompleteField = $inventoryField->getAutoCompleteFieldsByModule($recordModuleName);
		$autoFields = [];
		if ($autoCompleteField) {
			foreach ($autoCompleteField as $field) {
				$moduleModel = Vtiger_Module_Model::getInstance($field['module']);
				$fieldModel = Vtiger_Field_Model::getInstance($field['field'], $moduleModel);
				$fieldValue = $recordModel->get($field['field']);
				if (!empty($fieldValue)) {
					$autoFields[$field['tofield']] = $fieldValue;
					$autoFields[$field['tofield'] . 'Text'] = $fieldModel->getDisplayValue($fieldValue, $recordId, $recordModel, true);
				}
			}
		}
		$info['autoFields'] = $autoFields;
		if (!$recordModel->isEmpty('taxes') && strpos($recordModel->get('taxes'), ',') === false) {
			$taxModel = Settings_Inventory_Record_Model::getInstanceById($recordModel->get('taxes'), 'Taxes');
			$info['taxes'] = [
				'type' => 'group',
				'value' => $taxModel->get('value'),
			];
		}
		$autoCustomFields = $inventoryField->getCustomAutoComplete($moduleName, $fieldName, $recordModel);
		return [$recordId => array_merge($info, $autoCustomFields)];
	}
}
