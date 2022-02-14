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
	/** @var array Invetory data for validation */
	public $invetoryDataQty = [];
	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$response = ['result' => true];
		foreach ($recordModel->getInventoryData() as $value) {
			$recordModelInvetory = Vtiger_Record_Model::getInstanceById($value['name']);
			if ($recordModelInvetory->getModuleName() === 'Products' && $recordModelInvetory->getModule()->getFieldByColumn('qtyinstock')->isActiveField()) {
				$this->invetoryDataQty[$value['name']]['qtyInvetory'][] = $value['qty'];
				$this->invetoryDataQty[$value['name']] += ['qtyRecord' => $recordModelInvetory->get('qtyinstock')];
			}
		}
		foreach ($this->invetoryDataQty as $value) {
			if ((float) array_sum($value['qtyInvetory']) > (float) $value['qtyRecord']) {
				$response = ['result' => false, 'message' => App\Language::translate('LBL_CHECK_QUANTITY_AVAILABLE', $recordModel->getModuleName())];
			}
		}
		return $response;
	}
}
