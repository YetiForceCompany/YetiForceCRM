<?php
/**
 * The file contains: Class to change the payment status of a sales invoice.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Class to change the payment status of a sales invoice.
 */
class PaymentsIn_FinvoicePaymentStatus_Model extends PaymentsIn_PaymentStatus_Model
{
	/**
	 * {@inheritdoc}
	 */
	protected static $moduleName = 'FInvoice';

	/**
	 * {@inheritdoc}
	 */
	protected static $fieldPaymentStatusName = 'finvoice_paymentstatus';

	/**
	 * {@inheritdoc}
	 */
	protected static $relatedRecordIdName = 'finvoiceid';

	/**
	 * {@inheritdoc}
	 */
	protected static function calculatePaymentStatus(float $sumOfGross, float $sumOfPayments): string
	{
		if ($sumOfPayments > $sumOfGross || \App\Validator::floatIsEqual($sumOfGross, $sumOfPayments, 2)) {
			$paymentStatus = 'PLL_FULLY_PAID';
		} elseif (\App\Validator::floatIsEqual(0.0, $sumOfPayments, 2)) {
			$paymentStatus = 'PLL_AWAITING_PAYMENT';
		} else {
			$paymentStatus = 'PLL_PARTIALLY_PAID';
		}
		return $paymentStatus;
	}
}
