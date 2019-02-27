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
		if ($recordModel && $recordModel->getModule()->isInventory()) {
			$currencyId = $this->getCurrencyId($recordModel->getInventoryData());
		}
		if ($record && !$currencyId) {
			$moduleModel = $this->getFieldModel()->getModule();
			if ($moduleModel->isInventory()) {
				$currencyId = $this->getCurrencyId(\Vtiger_Inventory_Model::getInventoryDataById($record, $moduleModel->getName()));
			}
		}
		if ($currencyId) {
			$currencySymbol = \App\Fields\Currency::getById($currencyId)['currency_symbol'];
		} else {
			$currencySymbol = \App\Fields\Currency::getDefault()['currency_symbol'];
		}
		return 	CurrencyField::appendCurrencySymbol($value, $currencySymbol);
	}

	/**
	 * Function gets currency id of inventory record.
	 *
	 * @param array $invData
	 *
	 * @return int|null
	 */
	public function getCurrencyId(array $invData): ?int
	{
		return  current($invData)['currency'] ?? null;
	}
}
