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

		$conversionRate = 1;
		$response = new Vtiger_Response();
		$listPriceValues = $taxes = [];
		$listPrice = false;

		if (empty($idList)) {
			$info = $this->getRecordDetail($recordId, $currencyId);
		} else {
			foreach ($idList as $id) {
				$info[] = $this->getRecordDetail($id, $currencyId);
			}
		}
		$response->setResult($info);
		$response->emit();
	}

	function getRecordDetail($recordId, $currencyId)
	{
		$conversionRate = 1;
		$listPriceValues = $taxes = [];
		$listPrice = false;

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$moduleName = $recordModel->getModuleName();
		if (in_array($moduleName, ['Products', 'Services'])) {
			//$taxes = $recordModel->getTaxes();
			$listPriceValues = $recordModel->getListPriceValues($recordModel->getId());
			$priceDetails = $recordModel->getPriceDetails();
			foreach ($priceDetails as $currencyDetails) {
				if ($currencyId == $currencyDetails['curid']) {
					$conversionRate = $currencyDetails['conversionrate'];
				}
			}
			$listPrice = (float) $recordModel->get('unit_price') * (float) $conversionRate;
		}

		$info = [
			$recordId => [
				'id' => $recordId,
				'name' => decode_html($recordModel->getName()),
				//'taxes' => $taxes,
				'price' => $listPrice,
				'listpricevalues' => $listPriceValues,
				'description' => decode_html($recordModel->get('description')),
		]];
		return $info;
	}
}
