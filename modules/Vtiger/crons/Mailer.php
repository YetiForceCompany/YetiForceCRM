<?php
/**
 * Mailer cron.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_Mailer_Cron class.
 */
class Vtiger_Mailer_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$limit = (int) App\Config::performance('CRON_MAX_NUMBERS_SENDING_MAILS', 1000);
		$query = (new \App\Db\Query())->from('s_#__mail_queue')->where(['status' => 1])->orderBy(['priority' => SORT_DESC, 'id' => SORT_ASC])->limit(20);
		$db = \App\Db::getInstance('admin');
		foreach ($query->batch(20, $db) as $rows) {
			$this->updateLastActionTime();
			foreach ($rows as $row) {
				\App\Mailer::sendByRowQueue($row);
				--$limit;
				if (0 >= $limit) {
					return;
				}
			}
			if ($this->checkTimeout()) {
				return;
			}
		}
	}
}
