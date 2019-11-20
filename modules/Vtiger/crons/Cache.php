<?php
/**
 * Clear cache cron.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_Cache_Cron class.
 */
class Vtiger_Cache_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$time = strtotime('-30 day');
		$dirs = ['pdf', 'import', 'mail', 'session'];
		$exclusion = ['.htaccess', 'index.html'];
		foreach ($dirs as $dir) {
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isFile() && !\in_array($item->getBasename(), $exclusion) && $item->getMTime() < $time && $item->getATime() < $time) {
					unlink($item->getPathname());
				}
			}
		}
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(\App\Fields\File::getTmpPath(), \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile() && !\in_array($item->getBasename(), $exclusion) && $item->getMTime() < $time && $item->getATime() < $time) {
				unlink($item->getPathname());
			}
		}
	}
}
