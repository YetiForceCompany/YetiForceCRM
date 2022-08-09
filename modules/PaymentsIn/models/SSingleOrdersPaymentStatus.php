<?php
/**
 * The file contains: Class to change the payment status of a SSingleOrders.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Class to change the payment status of a SSingleOrders.
 */
class PaymentsIn_SSingleOrdersPaymentStatus_Model extends PaymentsIn_PaymentStatus_Model
{
	/** {@inheritdoc} */
	protected static $moduleName = 'SSingleOrders';

	/** {@inheritdoc} */
	protected static $fieldPaymentStatusName = 'payment_status';

	/** {@inheritdoc} */
	protected static $relatedRecordIdName = 'ssingleordersid';

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
