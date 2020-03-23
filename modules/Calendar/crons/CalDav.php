<?php
/**
 * CalDAV Cron Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Calendar_CalDav_Cron class.
 */
class Calendar_CalDav_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		\App\Log::trace('Start cron CalDAV');
		API_DAV_Model::runCronCalDav();
		\App\Log::trace('End cron CalDAV');
	}
}
