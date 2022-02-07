<?php
/**
 * Calculations handler.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Products_Calculations_Handler
{
	/**
	 * EntityBeforeSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$resultField = \App\Config::module($eventHandler->getModuleName(), 'CALCULATION_FIELD', 'commissionrate');
		if ($resultField && ($purchaseField = $recordModel->getField('purchase')) && $purchaseField->isActiveField() &&
			($marginField = $recordModel->getField('commissionrate')) && $marginField->isActiveField() &&
			($unitPriceField = $recordModel->getField('unit_price')) && $unitPriceField->isActiveField()) {
			$uiTypeModel = $unitPriceField->getName() === $resultField ? $purchaseField->getUITypeModel() : $unitPriceField->getUITypeModel();
			$value = $recordModel->get($uiTypeModel->getFieldModel()->getName());
			$currencyId = $uiTypeModel->getBaseCurrency($value) ?? \App\Fields\Currency::getDefault()['id'];
			$value = (float) $uiTypeModel->getValueForCurrency($value, $currencyId);

			switch ($resultField) {
				case $unitPriceField->getName():
					$value = $this->getValueField($currencyId, $value, null, (float) $recordModel->get($marginField->getName()));
					break;
				case $marginField->getName():
					$purchaseValue = $purchaseField->getUITypeModel()->getValueForCurrency($recordModel->get($purchaseField->getName()), $currencyId);
					$value = $this->getValueField($currencyId, (float) $purchaseValue, $value);
					break;
				case $purchaseField->getName():
					$value = $this->getValueField($currencyId, null, $value, (float) $recordModel->get($marginField->getName()));
					break;
				default:
					break;
			}
			$recordModel->set($resultField, $value);
		}
	}

	/**
	 * Calculation.
	 *
	 * @param int        $currencyId
	 * @param float|null $purchase
	 * @param float|null $unitPrice
	 * @param float|null $margin
	 *
	 * @return float|string
	 */
	private function getValueField(int $currencyId, ?float $purchase = null, ?float $unitPrice = null, ?float $margin = null)
	{
		if (null === $margin) {
			$value = empty($purchase) ? 0 : round(100 * ($unitPrice - $purchase) / $purchase, 3);
		} else {
			if (null === $unitPrice) {
				$value = empty($purchase) ? 0 : ($margin / 100) * $purchase + $purchase;
			} else {
				$value = empty($unitPrice) ? 0 : ($unitPrice * 100) / ($margin + 1);
			}
			$value = \App\Json::encode(['currencies' => [$currencyId => ['price' => $value]], 'currencyId' => $currencyId]);
		}
		return $value;
	}
}
