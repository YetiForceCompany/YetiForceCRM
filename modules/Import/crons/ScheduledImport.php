<?php
/**
 * Cron for scheduled import.
 *
 * @package   App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Import_ScheduledImport_Cron class.
 */
class Import_ScheduledImport_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		Import_Data_Action::runScheduledImport();
	}
}
