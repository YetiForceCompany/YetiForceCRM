<?php
/**
 * UIType CurrencyInventory Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */

/**
 * Class CurrencyInventory.
 */
class Vtiger_CurrencyInventory_UIType extends Vtiger_Double_UIType
{
	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = parent::getDisplayValue($value);
		$currencyId = null;
		$moduleModel = $this->getFieldModel()->getModule();
		if ($moduleModel->isInventory() && \Vtiger_Inventory_Model::getInstance($moduleModel->getName())->isField('currency')) {
			if ($recordModel) {
				$currencyId = $this->getCurrencyId($recordModel->getInventoryData());
			}
			if ($record && !$currencyId) {
				$currencyId = $this->getCurrencyId(\Vtiger_Inventory_Model::getInventoryDataById($record, $moduleModel->getName()));
			}
		}
		if ($currencyId) {
			$currencySymbol = \App\Fields\Currency::getById($currencyId)['currency_symbol'];
		} else {
			$currencySymbol = \App\Fields\Currency::getDefault()['currency_symbol'];
		}
		return CurrencyField::appendCurrencySymbol($value, $currencySymbol);
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
		return current($invData)['currency'] ?? null;
	}
}
