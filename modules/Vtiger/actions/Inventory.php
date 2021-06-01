<?php

/**
 * Basic inventory action file.
 *
 * @package Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Basic inventory action class.
 */
class Vtiger_Inventory_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('checkLimits');
		$this->exposeMethod('getDetails');
		$this->exposeMethod('getTableData');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
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
	public function checkLimits(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		if (!\App\Privilege::isPermitted($moduleName, 'EditView', $record)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$currency = $request->getInteger('currency');
		$price = $request->getByType('price', 'Double');
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
			$selectedCurrency = \App\Fields\Currency::getById($currency);
			$price = (float) $price * $selectedCurrency['conversion_rate'];
			$symbol = $selectedCurrency['currency_symbol'];
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

	public function getDetails(App\Request $request)
	{
		$currencyId = $request->getInteger('currency_id');
		$fieldName = $request->getByType('fieldname');
		$moduleName = $request->getModule();
		if ($request->isEmpty('idlist')) {
			$info = self::getRecordDetail($request->getInteger('record'), $currencyId, $moduleName, $fieldName);
		} else {
			foreach ($request->getArray('idlist', 'Integer') as $id) {
				$info[] = self::getRecordDetail($id, $currencyId, $moduleName, $fieldName);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($info);
		$response->emit();
	}

	/**
	 * Get record detail for inventory table.
	 *
	 * @param int      $recordId
	 * @param int|null $currencyId
	 * @param string   $moduleName
	 * @param string   $fieldName
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return array
	 */
	public static function getRecordDetail(int $recordId, ?int $currencyId, string $moduleName, string $fieldName): array
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		if (!$recordModel->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$recordModuleName = $recordModel->getModuleName();
		$info = [
			'id' => $recordId,
			'name' => App\Purifier::decodeHtml($recordModel->getName()),
			'description' => $recordModel->get('description'),
		];
		if (\in_array($recordModuleName, ['Products', 'Services'])) {
			$currencyId = empty($currencyId) ? \App\Fields\Currency::getDefault()['id'] : $currencyId;
			$info['qtyPerUnit'] = $recordModel->getDisplayValue('qty_per_unit');
			if (($fieldModel = $recordModel->getField('unit_price')) && $fieldModel->isActiveField()) {
				$info['unitPriceValues'] = $fieldModel->getUITypeModel()->getEditViewFormatData($recordModel->get($fieldModel->getName()))['currencies'] ?? [];
				$info['price'] = $fieldModel->getUITypeModel()->getValueForCurrency($recordModel->get($fieldModel->getName()), $currencyId);
			}
			if (($fieldModel = $recordModel->getField('purchase')) && $fieldModel->isActiveField()) {
				$info['purchase'] = $fieldModel->getUITypeModel()->getValueForCurrency($recordModel->get($fieldModel->getName()), $currencyId);
			}
		}
		$autoFields = [];
		$inventory = Vtiger_Inventory_Model::getInstance($moduleName);
		if ($autoCompleteField = ($inventory->getAutoCompleteFields()[$recordModuleName] ?? [])) {
			foreach ($autoCompleteField as $field) {
				$fieldModel = $recordModel->getField($field['field']);
				if ($fieldModel && ($fieldValue = $recordModel->get($field['field']))) {
					$autoFields[$field['tofield']] = $fieldModel->getEditViewDisplayValue($fieldValue, $recordModel);
					$autoFields[$field['tofield'] . 'Text'] = $fieldModel->getDisplayValue($fieldValue, $recordId, $recordModel, true);
				}
			}
		}
		$info['autoFields'] = $autoFields;
		if (!$recordModel->isEmpty('taxes')) {
			if (false === strpos($recordModel->get('taxes'), ',')) {
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
		return [$recordId => array_merge($info, $inventory->getCustomAutoComplete($fieldName, $recordModel))];
	}

	/**
	 * Get products and services from source invoice to display in correcting invoice before block.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function getTableData(App\Request $request)
	{
		if ($request->isEmpty('src_record', true) || $request->isEmpty('src_module', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$srcModule = $request->getByType('src_module', \App\Purifier::ALNUM);
		$srcRecord = $request->getInteger('src_record');
		if (!\App\Privilege::isPermitted($srcModule, 'DetailView', $srcRecord)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($srcRecord, $srcModule);
		$data = $recordModel->getInventoryData();
		foreach ($data as &$item) {
			$item['info'] = $this->getRecordDetail($item['name'], $item['currency'] ?? 0, $request->getModule(), 'name')[$item['name']];
			$item['moduleName'] = \App\Record::getType($item['info']['id']);
			$item['basetableid'] = Vtiger_Module_Model::getInstance($item['moduleName'])->get('basetableid');
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
