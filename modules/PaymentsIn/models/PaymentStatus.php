<?php
/**
 * The file contains: Class to change of payment status on related records.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Change of payment status on related records.
 */
abstract class PaymentsIn_PaymentStatus_Model
{
	/**
	 * Module name.
	 *
	 * @var string
	 */
	protected static $moduleName;

	/**
	 * Field payment status name.
	 *
	 * @var string
	 */
	protected static $fieldPaymentStatusName;

	/**
	 * Related record ID name.
	 *
	 * @var string
	 */
	protected static $relatedRecordIdName;

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
			(new \App\BatchMethod(['method' => static::class . '::updatePaymentStatus', 'params' => [$recordModel->get(static::$relatedRecordIdName)]]))->save();
		}
	}

	/**
	 * Update payment status.
	 *
	 * @param int $recordId
	 *
	 * @return void
	 */
	public static function updatePaymentStatus(int $recordId)
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, static::$moduleName);
		$recordModel->set(
			static::$fieldPaymentStatusName,
			static::calculatePaymentStatus((float) $recordModel->get('sum_gross'), PaymentsIn_Module_Model::getSumOfPaymentsByRecordId($recordId, static::$moduleName))
		);
		$recordModel->save();
	}

	/**
	 * Calculate payment status.
	 *
	 * @param float $sumOfGross
	 * @param float $sumOfPayments
	 *
	 * @return string
	 */
	abstract protected static function calculatePaymentStatus(float $sumOfGross, float $sumOfPayments): string;

	/**
	 * Checking if you can update the payment status.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	protected static function canUpdatePaymentStatus(Vtiger_Record_Model $recordModel): bool
	{
		$fieldModel = \Vtiger_Module_Model::getInstance(static::$moduleName)->getFieldByName(static::$fieldPaymentStatusName);
		$returnValue = $fieldModel && $fieldModel->isActiveField() && !$recordModel->isEmpty(static::$relatedRecordIdName);
		return $returnValue && ($recordModel->isNew() || false !== $recordModel->getPreviousValue(static::$fieldPaymentStatusName));
	}
}
