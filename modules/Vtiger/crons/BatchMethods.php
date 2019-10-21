<?php
/**
 * Batch methods.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_BatchMethods_Cron class.
 */
class Vtiger_BatchMethods_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$timeLimit = \App\Config::performance('CRON_BATCH_METHODS_LIMIT') * 60 + time();
		$dataReader = (new \App\Db\Query())->from('s_#__batchmethod')->orderBy(['id' => SORT_ASC])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$methodInstance = new \App\BatchMethod($row, false);
			$methodInstance->execute();
			if ($methodInstance->isCompleted()) {
				$methodInstance->delete();
			}
			unset($methodInstance);
			if (time() >= $timeLimit) {
				break;
			}
		}
		$dataReader->close();
	}
}
