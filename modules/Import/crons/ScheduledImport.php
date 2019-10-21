<?php
/**
 * Cron for scheduled import.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Import_ScheduledImport_Cron class.
 */
class Import_ScheduledImport_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		Import_Data_Action::runScheduledImport();
	}
}
