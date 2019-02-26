<?php
/**
 * UIType CurrencyInventory Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Vtiger_CurrencyInventory_UIType extends Vtiger_Double_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = parent::getDisplayValue($value);
		$currencyId = null;
		if ($recordModel) {
			$currencyId = $this->getCurrencyId($recordModel);
		}
		if ($record && !$currencyId && $recordModel = Vtiger_Record_Model::getInstanceById($record)) {
			$currencyId= $this->getCurrencyId($recordModel);
		}
		if ($currencyId) {
			$currencySymbol = \App\Fields\Currency::getById($currencyId)['currency_symbol'];
		} else {
			$currencySymbol =App\Fields\Currency::getDefault()['currency_symbol'];
		}
		return 	CurrencyField::appendCurrencySymbol($value, $currencySymbol);
	}

	/**
	 * Function gets currency id of inventory record.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int|null
	 */
	public function getCurrencyId(Vtiger_Record_Model $recordModel): ?int
	{
		if ($recordModel->getModule()->isInventory()) {
			$invData = $recordModel->getInventoryData();
			return current($invData)['currency'] ?? null;
		}
		return null;
	}
}
