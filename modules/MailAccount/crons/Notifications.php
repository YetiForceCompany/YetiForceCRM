<?php
/**
 * Mail account notifications file.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Mail account notifications class.
 */
class MailAccount_Notifications_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$duration = \App\Mail::getConfig('scanner', 'time_for_notification');
		$email = \App\Mail::getConfig('scanner', 'email_for_notification');
		if (!$duration || !$email) {
			return false;
		}
		$dbCommand = App\Db::getInstance()->createCommand();
		$dataReader = (new App\Db\Query())->from('vtiger_ossmails_logs')->where(['status' => \App\Mail\ScannerLog::STATUS_RUNNING])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$startTime = strtotime($row['start_time']);
			if (strtotime('now') > $startTime + ($duration * 60)
			&& !(new \App\Db\Query())->from('vtiger_ossmailscanner_log_cron')->where(['laststart' => $startTime])->exists()) {
				$dbCommand->insert('vtiger_ossmailscanner_log_cron', ['laststart' => $startTime, 'status' => 0, 'created_time' => date('Y-m-d H:i:s')])->execute();
				$url = \App\Config::main('site_URL');
				$mailStatus = \App\Mailer::addMail([
					'to' => $email,
					'subject' => App\Language::translate('Email_FromName', 'OSSMailScanner'),
					'content' => App\Language::translate('Email_Body', 'OSSMailScanner') . "\r\n<br><a href='{$url}'>{$url}</a>",
				]);
				$dbCommand->update('vtiger_ossmailscanner_log_cron', ['status' => (int) $mailStatus], ['laststart' => $startTime])->execute();
			}
		}
		$dataReader->close();
	}
}
