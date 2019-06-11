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
			$ordersId = (int) $recordModel->get('ssingleordersid');
			$orderRecordModel = \Vtiger_Record_Model::getInstanceById($ordersId, 'SSingleOrders');
			$sumOfGross = (float) $orderRecordModel->get('sum_gross');
			$sumOfPayments = $this->getSumOfPayments($ordersId);
			if ($sumOfGross > $sumOfPayments) {
				$paymentStatus = 'PLL_UNDERPAID';
			} elseif ($sumOfGross < $sumOfPayments) {
				$paymentStatus = 'PLL_OVERPAID';
			} elseif (\App\Validator::floatIsEqual($sumOfGross, 0.0, 2)) {
				$paymentStatus = 'PLL_NOT_PAID';
			} else {
				$paymentStatus = 'PLL_PAID';
			}
			$orderRecordModel->set('ssingleorders_payment_status', $paymentStatus);
			$orderRecordModel->save();
		}
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
		if ($recordModel->isEmpty('ssingleordersid')) {
			return false;
		}
		$ordersId = (int) $recordModel->get('ssingleordersid');
		if ((int) $recordModel->get('currency_id') !== \App\Record::getCurrencyIdFromInventory($ordersId, 'SSingleOrders')) {
			\App\Log::warning("The payment is in a different currency than the order. SSingleOrdersId: {$ordersId}");
			return false;
		}
		return $recordModel->isNew() || false !== $recordModel->getPreviousValue('paymentsin_status');
	}

	/**
	 * Get the sum of all payments.
	 *
	 * @param int $recordId
	 *
	 * @return float
	 */
	private function getSumOfPayments(int $recordId): float
	{
		return (float) (new \App\Db\Query())
			->from('vtiger_paymentsin')
			->innerJoin('vtiger_crmentity', 'vtiger_paymentsin.paymentsinid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0])
			->andWhere(['ssingleordersid' => $recordId])
			->sum('paymentsvalue');
	}
}
