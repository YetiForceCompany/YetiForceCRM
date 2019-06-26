<?php
/**
 * The file contains: PaymentsIn module model class.
 *
 * @package Model
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
			$sumOfPayments = (float) $relationModel->getQuery()
				->createQuery()
				->sum('vtiger_paymentsin.paymentsvalue');
			\App\Cache::staticSave($cacheNamespace, $recordId, $sumOfPayments);
		}
		return $sumOfPayments;
	}
}
