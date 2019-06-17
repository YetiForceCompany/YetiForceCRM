<?php
/**
 * The file contains: PaymentsIn handler class.
 *
 * @package Handler
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */
class PaymentsIn_PaymentsInHandler_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return void
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if ($this->canUpdatePaymentStatus($recordModel)) {
			(new \App\BatchMethod(['method' => 'PaymentsIn_Module_Model::updatePaymentStatus', 'params' => [$recordModel->get('ssingleordersid')]]))->save();
		}
		PaymentsIn_FinvoicePaymentStatus_Model::updateIfPossible($recordModel);
	}

	/**
	 * Checking if you can update the payment status.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	private function canUpdatePaymentStatus(Vtiger_Record_Model $recordModel): bool
	{
		$fieldModel = \Vtiger_Module_Model::getInstance('SSingleOrders')->getFieldByName('ssingleorders_payment_status');
		$returnValue = $fieldModel && $fieldModel->isActiveField() && !$recordModel->isEmpty('ssingleordersid');
		if ($returnValue && (int) $recordModel->get('currency_id') !== \App\Record::getCurrencyIdFromInventory($recordModel->get('ssingleordersid'), 'SSingleOrders')) {
			\App\Log::warning('The payment is in a different currency than the order. SSingleOrdersId: ' . $recordModel->get('ssingleordersid'));
			$returnValue = false;
		}
		return $returnValue && ($recordModel->isNew() || false !== $recordModel->getPreviousValue('paymentsin_status'));
	}
}
