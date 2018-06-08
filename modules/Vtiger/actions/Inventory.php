<?php

/**
 * Basic Inventory Action Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Inventory_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		$this->exposeMethod('checkLimits');
		$this->exposeMethod('getUnitPrice');
		$this->exposeMethod('getDetails');
	}

	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function verifies whether the Account's credit limit has been reached.
	 *
	 * @param \App\Request $request
	 */
	public function checkLimits(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		if (!\App\Privilege::isPermitted($moduleName, 'EditView', $record)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
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
			$price = (float) $price * $selectedCurrency['rate'];
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
			'html' => $html,
		]);
		$response->emit();
	}

	public function getUnitPrice(\App\Request $request)
	{
		$record = $request->getInteger('record');
		$recordModule = $request->getByType('recordModule', 2);
		if (!\App\Privilege::isPermitted($recordModule, 'EditView', $record)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$unitPriceValues = false;
		if (in_array($recordModule, ['Products', 'Services'])) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $recordModule);
			$unitPriceValues = $recordModel->getListPriceValues($record);
		}
		$response = new Vtiger_Response();
		$response->setResult($unitPriceValues);
		$response->emit();
	}

	public function getDetails(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$idList = $request->get('idlist');
		$currencyId = $request->getInteger('currency_id');
		$fieldName = $request->getByType('fieldname');
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
		if (!\App\Privilege::isPermitted($moduleName, 'EditView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$recordModuleName = $recordModel->getModuleName();
		$info = [
			'id' => $recordId,
			'name' => App\Purifier::decodeHtml($recordModel->getName()),
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
			$info['qtyPerUnit'] = $recordModel->getDisplayValue('qty_per_unit');
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
		if (!$recordModel->isEmpty('taxes')) {
			if (strpos($recordModel->get('taxes'), ',') === false) {
				$taxModel = Settings_Inventory_Record_Model::getInstanceById($recordModel->get('taxes'), 'Taxes');
			} else {
				$productTaxes = explode(',', $recordModel->get('taxes'));
				$taxModel = Settings_Inventory_Record_Model::getInstanceById(reset($productTaxes), 'Taxes');
			}
			$info['taxes'] = [
				'type' => 'group',
				'value' => $taxModel->get('value')
			];
		}
		$autoCustomFields = $inventoryField->getCustomAutoComplete($moduleName, $fieldName, $recordModel);

		return [$recordId => array_merge($info, $autoCustomFields)];
	}
}
