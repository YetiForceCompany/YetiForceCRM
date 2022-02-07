<?php
/**
 * Clear cache cron file.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Clear cache cron class.
 */
class Vtiger_Cache_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		\App\Cache::clearTemporaryFiles();
		\App\Db::getInstance('admin')->createCommand()
			->delete('s_#__tokens', ['<', 'expiration_date', date('Y-m-d H:i:s')])
			->execute();
	}
}
