<?php
/**
 * The file contains: Class to change the payment status of a proforma invoice.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
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

	/** {@inheritdoc} */
	protected static function canUpdatePaymentStatus(Vtiger_Record_Model $recordModel): bool
	{
		$returnValue = parent::canUpdatePaymentStatus($recordModel);
		if (($returnValue || false !== $recordModel->getPreviousValue(static::$relatedRecordIdName)) && (int) $recordModel->get('currency_id') !== \App\Record::getCurrencyIdFromInventory($recordModel->get(static::$relatedRecordIdName), static::$moduleName)
		) {
			\App\Log::warning('The payment is in a different currency than the related record: ' . $recordModel->get(static::$relatedRecordIdName));
			$returnValue = false;
		}
		return $returnValue;
	}
}
