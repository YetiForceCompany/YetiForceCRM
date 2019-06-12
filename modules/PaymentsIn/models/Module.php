<?php
/**
 * The file contains: PaymentsIn module model class.
 *
 * @package model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Class PaymentsIn_Module_Model.
 */
class PaymentsIn_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Update payment status.
	 *
	 * @param int $ordersId
	 *
	 * @return void
	 */
	public static function updatePaymentStatus(int $ordersId)
	{
		$orderRecordModel = \Vtiger_Record_Model::getInstanceById($ordersId, 'SSingleOrders');
		$orderRecordModel->set(
			'ssingleorders_payment_status',
			static::calculatePaymentStatus((float) $orderRecordModel->get('sum_gross'), static::getSumOfPaymentsByRecordId($ordersId))
		);
		$orderRecordModel->save();
	}

	/**
	 * Calculate payment status.
	 *
	 * @param float $sumOfGross
	 * @param float $sumOfPayments
	 *
	 * @return string
	 */
	private static function calculatePaymentStatus(float $sumOfGross, float $sumOfPayments): string
	{
		if (\App\Validator::floatIsEqual($sumOfGross, $sumOfPayments, 8)) {
			$paymentStatus = 'PLL_PAID';
		} elseif (\App\Validator::floatIsEqual(0.0, $sumOfPayments, 8)) {
			$paymentStatus = 'PLL_NOT_PAID';
		} elseif ($sumOfGross > $sumOfPayments) {
			$paymentStatus = 'PLL_UNDERPAID';
		} else {
			$paymentStatus = 'PLL_OVERPAID';
		}
		return $paymentStatus;
	}

	/**
	 * Get the sum of all payments by record ID.
	 *
	 * @param int $recordId
	 *
	 * @return float
	 */
	private static function getSumOfPaymentsByRecordId(int $recordId): float
	{
		$relationModel = Vtiger_Relation_Model::getInstance(
			Vtiger_Module_Model::getInstance('SSingleOrders'),
			Vtiger_Module_Model::getInstance('PaymentsIn')
		);
		$relationModel->set('parentRecord', Vtiger_Record_Model::getInstanceById($recordId, 'SSingleOrders'));
		return (float) $relationModel->getQuery()
			->createQuery()
			->sum('vtiger_paymentsin.paymentsvalue');
	}
}
