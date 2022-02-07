<?php
/**
 * The file contains: Class to change the payment status of a proforma invoice.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

/**
 * Class to change the payment status of a proforma invoice.
 */
class PaymentsIn_FinvoiceProformaPaymentStatus_Model extends PaymentsIn_PaymentStatus_Model
{
	/** {@inheritdoc} */
	protected static $moduleName = 'FInvoiceProforma';

	/** {@inheritdoc} */
	protected static $fieldPaymentStatusName = 'payment_status';

	/** {@inheritdoc} */
	protected static $fieldPaymentSumName = 'payment_sum';

	/** {@inheritdoc} */
	protected static $relatedRecordIdName = 'finvoiceproformaid';

}
