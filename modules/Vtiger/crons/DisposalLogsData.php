<?php
/**
 * Logs and data disposal cron file.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Logs and data disposal cron class.
 */
class Vtiger_DisposalLogsData_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$logDb = \App\DB::getInstance('log');
		$logDb->createCommand()
			->delete(\App\Integrations\Comarch::LOG_TABLE_NAME, [
				'and',
				['error' => 1],
				['<', 'time', date('Y-m-d H:i:s', strtotime('-90 day'))]
			])
			->execute();
		$logDb->createCommand()
			->delete(\App\Integrations\Comarch::LOG_TABLE_NAME, [
				'and',
				['error' => 0],
				['<', 'time', date('Y-m-d H:i:s', strtotime('-30 day'))]
			])
			->execute();
	}
}
