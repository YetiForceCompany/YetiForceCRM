<?php

/**
 * Supplies MassSave Action Class
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_GetDetails_Action extends Vtiger_Action_Controller
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
		$recordId = $request->get('record');
		$idList = $request->get('idlist');
		$currencyId = $request->get('currency_id');
		$moduleName = $request->getModule();

		$conversionRate = 1;
		$response = new Vtiger_Response();
		$unitPriceValues = $taxes = [];
		$unitPrice = false;

		if (empty($idList)) {
			$info = $this->getRecordDetail($recordId, $currencyId, $moduleName);
		} else {
			foreach ($idList as $id) {
				$info[] = $this->getRecordDetail($id, $currencyId, $moduleName);
			}
		}
		$response->setResult($info);
		$response->emit();
	}

	function getRecordDetail($recordId, $currencyId, $moduleName)
	{
		$conversionRate = 1;
		$unitPriceValues = $taxes = [];
		$unitPrice = false;

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$recordModuleName = $recordModel->getModuleName();
		if (in_array($recordModuleName, ['Products', 'Services'])) {
			$unitPriceValues = $recordModel->getListPriceValues($recordModel->getId());
			$priceDetails = $recordModel->getPriceDetails();
			foreach ($priceDetails as $currencyDetails) {
				if ($currencyId == $currencyDetails['curid']) {
					$conversionRate = $currencyDetails['conversionrate'];
				}
			}
			$unitPrice = (float) $recordModel->get('unit_price') * (float) $conversionRate;
		}
		$autoCompleteField = Supplies_SupField_Model::getAutoCompleteField($recordModuleName, $moduleName);
		$autoFields = [];
		if ($autoCompleteField) {
			foreach ($autoCompleteField as $field) {
				if ($recordModel->has($field['field']) && $recordModel->get($field['field']) != '') {
					$autoFields[$field['tofield']] = $recordModel->get($field['field']);
				}
			}
		}
		$info = [
			$recordId => [
				'id' => $recordId,
				'name' => decode_html($recordModel->getName()),
				'price' => CurrencyField::convertToUserFormat($unitPrice, null, true),
				'unitPriceValues' => $unitPriceValues,
				'description' => decode_html($recordModel->get('description')),
				'autoFields' => $autoFields,
		]];
		return $info;
	}
}
