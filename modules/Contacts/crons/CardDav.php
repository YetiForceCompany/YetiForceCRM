<?php
/**
 * CardDAV Cron Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_CardDav_Cron class.
 */
class Contacts_CardDav_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		\App\Log::trace('Start cron CardDAV');
		API_DAV_Model::runCronCardDav();
		\App\Log::trace('End cron CardDAV');
	}
}
