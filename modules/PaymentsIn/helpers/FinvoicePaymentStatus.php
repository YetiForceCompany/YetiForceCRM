<?php
/**
 * The file contains: Class to change the payment status of a sales invoice.
 *
 * @package Helpers
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Class to change the payment status of a sales invoice.
 */
class PaymentsIn_FinvoicePaymentStatus_Helper
{
	/**
	 * Update if possible.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	public static function updateIfPossible(Vtiger_Record_Model $recordModel)
	{
		if (static::canUpdatePaymentStatus($recordModel)) {
			(new \App\BatchMethod(['method' => 'PaymentsIn_FinvoicePaymentStatus_Helper::updatePaymentStatus', 'params' => [$recordModel->get('finvoiceid')]]))->save();
		}
	}

	/**
	 * Update payment status.
	 *
	 * @param int $ordersId
	 *
	 * @return void
	 */
	public static function updatePaymentStatus(int $ordersId)
	{
		$orderRecordModel = \Vtiger_Record_Model::getInstanceById($ordersId, 'FInvoice');
		$orderRecordModel->set(
			'finvoice_paymentstatus',
			static::calculatePaymentStatus((float) $orderRecordModel->get('sum_gross'), PaymentsIn_Module_Model::getSumOfPaymentsByRecordId($ordersId, 'FInvoice'))
		);
		$orderRecordModel->save();
	}

	/**
	 * Checking if you can update the payment status.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	private static function canUpdatePaymentStatus(Vtiger_Record_Model $recordModel): bool
	{
		$fieldModel = \Vtiger_Module_Model::getInstance('FInvoice')->getFieldByName('finvoice_paymentstatus');
		$returnValue = $fieldModel && $fieldModel->isActiveField() && !$recordModel->isEmpty('finvoiceid');
		return $returnValue && ($recordModel->isNew() || false !== $recordModel->getPreviousValue('paymentsin_status'));
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
		if ($sumOfPayments > $sumOfGross || \App\Validator::floatIsEqual($sumOfGross, $sumOfPayments, 8)) {
			$paymentStatus = 'PLL_FULLY_PAID';
		} elseif (\App\Validator::floatIsEqual(0.0, $sumOfPayments, 8)) {
			$paymentStatus = 'PLL_AWAITING_PAYMENT';
		} else {
			$paymentStatus = 'PLL_PARTIALLY_PAID';
		}
		return $paymentStatus;
	}
}
