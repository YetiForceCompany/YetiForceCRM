<?php

/**
 * Files clean handler class.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * SSalesProcesses_Finances_Handler class.
 */
class SSalesProcesses_Finances_Handler
{
	/**
	 * EntityBeforeSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if (($probability = $recordModel->getField('probability')) && $probability->isActiveField()) {
			if (
				($estimated = $recordModel->getField('estimated')) && $estimated->isActiveField()
				&& ($expectedSale = $recordModel->getField('expected_sale')) && $expectedSale->isActiveField()
			) {
				$value = (float) $recordModel->get($estimated->getName()) * (float) $recordModel->get($probability->getName()) / 100;
				if (!\App\Validator::floatIsEqual($value, (float) $recordModel->get($expectedSale->getName()))) {
					$recordModel->set($expectedSale->getName(), $value);
					$recordModel->setDataForSave([$expectedSale->getTableName() => [$expectedSale->getColumnName() => $value]]);
				}
			}
			if (($estimatedMargin = $recordModel->getField('estimated_margin'))
				&& $estimatedMargin->isActiveField()
				&& ($expectedMargin = $recordModel->getField('expected_margin')) && $expectedMargin->isActiveField()
			) {
				$value = (float) $recordModel->get($estimatedMargin->getName()) * (float) $recordModel->get($probability->getName()) / 100;
				if (!\App\Validator::floatIsEqual($value, (float) $recordModel->get($expectedMargin->getName()))) {
					$recordModel->set($expectedMargin->getName(), $value);
					$recordModel->setDataForSave([$expectedMargin->getTableName() => [$expectedMargin->getColumnName() => $value]]);
				}
			}
		}
	}
}
