<?php

/**
 * The file contains: CalculateSumOfExecutionTime class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * CalculateSumOfExecutionTime class.
 */
class HelpDesk_CalculateSumOfExecutionTime_Model
{
	/**
	 * Calculate.
	 *
	 * @param int   $recordId
	 * @param float $val
	 *
	 * @return void
	 */
	public static function calculate(int $recordId, float $val = 0)
	{
		if (static::isActive()) {
			$row = (new \App\Db\Query())->from('vtiger_troubletickets')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_troubletickets.ticketid')
				->where(['vtiger_crmentity.deleted' => 0, 'ticketid' => $recordId])
				->one();
			$sumTime = round((
				$val + $row['sum_time']
			), 2);

			static::update($recordId, $sumTime);
			$parentId = $row['parentid'];
			if (!empty($parentId)) {
				static::calculate($parentId, static::getSumTimeOfChildren($parentId));
			}
		}
	}

	/**
	 * Is active.
	 *
	 * @return bool
	 */
	private static function isActive(): bool
	{
		$fieldModel = \Vtiger_Field_Model::getInstance('total_execution_time', \Vtiger_Module_Model::getInstance('HelpDesk'));
		return $fieldModel && $fieldModel->isActiveField();
	}

	/**
	 * Update.
	 *
	 * @param int   $recordId
	 * @param float $sumTime
	 *
	 * @return void
	 */
	private static function update(int $recordId, float $sumTime)
	{
		\App\Db::getInstance()
			->createCommand()
			->update(
			'vtiger_troubletickets',
			['total_execution_time' => $sumTime],
			['ticketid' => $recordId]
		)->execute();
	}

	/**
	 * Get sum time of children.
	 *
	 * @param int $recordId
	 *
	 * @return float
	 */
	private static function getSumTimeOfChildren(int $recordId): float
	{
		return (float) (new \App\Db\Query())->from('vtiger_troubletickets')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_troubletickets.ticketid')
			->where(['vtiger_crmentity.deleted' => 0, 'parentid' => $recordId])
			->sum('total_execution_time');
	}
}
