<?php
/**
 * Batch methods.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_BatchMethods_Cron class.
 */
class Vtiger_BatchMethods_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$query = (new \App\Db\Query())->from('s_#__batchmethod')->limit(10);
		while ($rows = $query->all()) {
			foreach ($rows as $row) {
				$this->updateLastActionTime();
				try {
					$methodInstance = new \App\BatchMethod($row, false);
					$methodInstance->execute();
					if ($methodInstance->isCompleted()) {
						$methodInstance->delete();
					}
					unset($methodInstance);
				} catch (\Throwable $th) {
					\App\Log::error("Batch method error: Method: {$row['method']} | ID: {$row['id']} \n{$th->__toString()}", 'BatchMethods');
					throw $th;
				}
			}
			if ($this->checkTimeout()) {
				return;
			}
		}
	}
}
