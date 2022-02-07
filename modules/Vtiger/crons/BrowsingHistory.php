<?php
/**
 * Clear browsing history cron.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Lorencik <m.lorencik@yetiforce.com>
 */

/**
 * Vtiger_BrowsingHistory_Cron class.
 */
class Vtiger_BrowsingHistory_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$deleteAfter = App\Config::performance('BROWSING_HISTORY_DELETE_AFTER');
		$deleteAfter = date('Y-m-d ', strtotime("-$deleteAfter DAY")) . '00:00:00';
		\App\Db::getInstance()->createCommand()->delete('u_#__browsinghistory', ['<', 'date', $deleteAfter])->execute();
	}
}
