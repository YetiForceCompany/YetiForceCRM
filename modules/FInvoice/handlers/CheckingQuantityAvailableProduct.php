<?php
/**
 * Checking the quantity available product handler file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */


/**
 * Checking the quantity available product handler class.
 */
class FInvoice_CheckingQuantityAvailableProduct_Handler
{
	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$response = ['result' => true];
		$dataQty = $productsName = [];
		foreach ($recordModel->getInventoryData() as $value) {
			$productRecordModel = Vtiger_Record_Model::getInstanceById($value['name'], 'Products');
			if ($productRecordModel->isEditable() && $productRecordModel->getModuleName() === 'Products' && $productRecordModel->getField('qtyinstock')->isActiveField()) {
				$dataQty[$productRecordModel->getId()]['qtyInventory'] = ($dataQty[$productRecordModel->getId()]['qtyInventory'] ?? 0) + $value['qty'];
				$dataQty[$productRecordModel->getId()]['qtyRecord'] = $productRecordModel->get('qtyinstock');
				$dataQty[$productRecordModel->getId()]['productName'] = "<br />{$productRecordModel->getName()}";
			}
		}
		if (!empty($dataQty)) {
			foreach ($dataQty as $value) {
				if ((float) $value['qtyInventory'] > (float) $value['qtyRecord']) {
					$productsName[] = $value['productName'];
				}
			}
			if (!empty($productsName)) {
				$response = ['result' => false, 'message' => App\Language::translateArgs('LBL_AVAILABLE_QUANTITY_PRODUCT', $recordModel->getModuleName(), implode(' ', $productsName))];
			}
		}
		return $response;
	}
}
