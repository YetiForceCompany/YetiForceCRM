<?php
/**
 * The file contains: Class to change of payment status on related records.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * Field payment sum name.
	 *
	 * @var string
	 */
	protected static $fieldPaymentSumName;

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
			if ($currentRelatedId = $recordModel->get(static::$relatedRecordIdName)) {
				(new \App\BatchMethod(['method' => static::class . '::updatePaymentStatus', 'params' => [$currentRelatedId]]))->save();
			}
			$previousRelatedId = $recordModel->getPreviousValue(static::$relatedRecordIdName);
			if (false !== $previousRelatedId && $previousRelatedId > 0) {
				(new \App\BatchMethod(['method' => static::class . '::updatePaymentStatus', 'params' => [$previousRelatedId]]))->save();
			}
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
		$changes = false;
		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, static::$moduleName);
		if (!empty(static::$fieldPaymentStatusName)) {
			$statusFieldModel = $recordModel->getField(static::$fieldPaymentStatusName);
			if ($statusFieldModel && $statusFieldModel->isActiveField()) {
				$recordModel->set(
					static::$fieldPaymentStatusName,
					static::calculatePaymentStatus((float) $recordModel->get('sum_gross'), static::getSumOfPaymentsByRecordId($recordId, static::$moduleName))
				);
				$changes = true;
			}
		}
		if (!empty(static::$fieldPaymentSumName)) {
			$sumFieldModel = $recordModel->getField(static::$fieldPaymentSumName);
			if ($sumFieldModel && $sumFieldModel->isActiveField()) {
				$recordModel->set(
					static::$fieldPaymentSumName,
					static::getSumOfPaymentsByRecordId($recordId, static::$moduleName)
				);
				$changes = true;
			}
		}
		if ($changes) {
			$recordModel->save();
		}
	}

	/**
	 * Get the sum of all payments by record ID.
	 *
	 * @param int    $recordId
	 * @param string $moduleName
	 *
	 * @return float
	 */
	public static function getSumOfPaymentsByRecordId(int $recordId, string $moduleName): float
	{
		$cacheNamespace = "getSumOfPaymentsByRecordId.{$moduleName}";
		if (\App\Cache::staticHas($cacheNamespace, $recordId)) {
			$sumOfPayments = (float) \App\Cache::staticGet($cacheNamespace, $recordId);
		} else {
			$relationModel = Vtiger_Relation_Model::getInstance(
				Vtiger_Module_Model::getInstance($moduleName),
				Vtiger_Module_Model::getInstance('PaymentsIn')
			);
			$relationModel->set('parentRecord', Vtiger_Record_Model::getInstanceById($recordId, $moduleName));
			$queryGenerator = $relationModel->getQuery();
			$queryGenerator->addNativeCondition(['vtiger_paymentsin.paymentsin_status' => 'PLL_PAID']);
			$sumOfPayments = (float) $queryGenerator->createQuery()
				->sum('vtiger_paymentsin.paymentsvalue');
			\App\Cache::staticSave($cacheNamespace, $recordId, $sumOfPayments);
		}
		return $sumOfPayments;
	}

	/**
	 * Calculate payment status.
	 *
	 * @param float $sumOfGross
	 * @param float $sumOfPayments
	 *
	 * @return string
	 */
	protected static function calculatePaymentStatus(float $sumOfGross, float $sumOfPayments): string
	{
		if (\App\Validator::floatIsEqual($sumOfGross, $sumOfPayments, 3)) {
			$paymentStatus = 'PLL_PAID';
		} elseif (\App\Validator::floatIsEqual(0.0, $sumOfPayments, 3)) {
			$paymentStatus = 'PLL_NOT_PAID';
		} elseif ($sumOfGross > $sumOfPayments) {
			$paymentStatus = 'PLL_UNDERPAID';
		} else {
			$paymentStatus = 'PLL_OVERPAID';
		}
		return $paymentStatus;
	}

	/**
	 * Checking if you can update the payment status.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	protected static function canUpdatePaymentStatus(Vtiger_Record_Model $recordModel): bool
	{
		$returnValue = !$recordModel->isEmpty(static::$relatedRecordIdName) && ($recordModel->isNew() || false !== $recordModel->getPreviousValue('paymentsin_status'));
		if ($returnValue) {
			$fieldModel = \Vtiger_Module_Model::getInstance(static::$moduleName)->getFieldByName(static::$fieldPaymentStatusName);
			$returnValue = $fieldModel && $fieldModel->isActiveField();
		}
		return $returnValue;
	}

	/**
	 * Check if payment fields for update changed.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	protected static function checkIfPaymentFieldsChanged(Vtiger_Record_Model $recordModel): bool
	{
		$result = false;
		$fieldsToCheck = ['paymentsin_status', 'finvoiceid', 'ssingleordersid', 'finvoiceproformaid'];
		foreach ($fieldsToCheck as $fieldName) {
			if (false !== $recordModel->getPreviousValue($fieldName)) {
				$result = true;
				break;
			}
		}
		return $result;
	}
}
