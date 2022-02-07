<?php
/**
 * Support of one-time processes to execute scripts whose execution time is very long.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_BatchProcesses_Cron class.
 */
class Vtiger_BatchProcesses_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$disable = true;
		$iterator = new \DirectoryIterator(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR . 'Batch');
		foreach ($iterator as $item) {
			if ($item->isFile() && 'php' === $item->getExtension()) {
				$this->updateLastActionTime();
				$class = 'Cron\Batch\\' . $item->getBasename('.php');
				$handler = new $class($this);
				if ($handler->preProcess()) {
					$handler->process();
				}
				if ($handler->postProcess()) {
					unlink($item->getPathname());
				}
				$disable = false;
			}
		}
		if ($disable) {
			\App\Cron::updateStatus(\App\Cron::STATUS_DISABLED, 'LBL_BATCH_PROCESSES');
		}
	}
}
