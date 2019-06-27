<?php
/**
 * The file contains: Class to change the payment status of a SSingleOrders.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Class to change the payment status of a SSingleOrders.
 */
class PaymentsIn_SSingleOrdersPaymentStatus_Model extends PaymentsIn_PaymentStatus_Model
{
	/**
	 * {@inheritdoc}
	 */
	protected static $moduleName = 'SSingleOrders';

	/**
	 * {@inheritdoc}
	 */
	protected static $fieldPaymentStatusName = 'payment_status';

	/**
	 * {@inheritdoc}
	 */
	protected static $relatedRecordIdName = 'ssingleordersid';

	/**
	 * {@inheritdoc}
	 */
	protected static function canUpdatePaymentStatus(Vtiger_Record_Model $recordModel): bool
	{
		$returnValue = parent::canUpdatePaymentStatus($recordModel);
		if ($returnValue && (int) $recordModel->get('currency_id') !== \App\Record::getCurrencyIdFromInventory($recordModel->get('ssingleordersid'), 'SSingleOrders')) {
			\App\Log::warning('The payment is in a different currency than the order. SSingleOrdersId: ' . $recordModel->get('ssingleordersid'));
			$returnValue = false;
		}
		return $returnValue;
	}
}
