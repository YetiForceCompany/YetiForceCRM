<?php
/**
 * Support of one-time processes to execute scripts whose execution time is very long.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$disable = true;
$iterator = new \DirectoryIterator(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR . 'Batch');
foreach ($iterator as $item) {
	if ($item->isFile() && $item->getExtension() === 'php') {
		$class = 'Cron\Batch\\' . $item->getBasename('.php');
		$handler = new $class();
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
	\App\Db::getInstance()->createCommand()->update('vtiger_cron_task', ['status' => 0], ['name' => 'LBL_BATCH_PROCESSES'])
		->execute();
}
