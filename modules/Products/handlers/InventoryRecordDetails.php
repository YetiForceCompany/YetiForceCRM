<?php
/**
 * Inventory record details handler.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Inventory record details handler class.
 */
class Products_InventoryRecordDetails_Handler
{
	/**
	 * InventoryRecordDetails handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function inventoryRecordDetails(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$params = $eventHandler->getParams();
		$currencyId = empty($params['currencyId']) ? \App\Fields\Currency::getDefault()['id'] : $params['currencyId'];
		$info = $params['info'];
		$info['qtyPerUnit'] = $recordModel->getDisplayValue('qty_per_unit');
		if (($fieldModel = $recordModel->getField('unit_price')) && $fieldModel->isActiveField()) {
			$info['unitPriceValues'] = $fieldModel->getUITypeModel()->getEditViewFormatData($recordModel->get($fieldModel->getName()))['currencies'] ?? [];
			$info['price'] = $fieldModel->getUITypeModel()->getValueForCurrency($recordModel->get($fieldModel->getName()), $currencyId);
		}
		if (($fieldModel = $recordModel->getField('purchase')) && $fieldModel->isActiveField()) {
			$info['purchase'] = $fieldModel->getUITypeModel()->getValueForCurrency($recordModel->get($fieldModel->getName()), $currencyId);
		}
		$eventHandler->addParams('info', $info);
	}
}
