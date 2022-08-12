<?php
/**
 * Cron for scheduled import.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * OSSMailScanner_Verification_Cron class.
 */
class OSSMailScanner_Verification_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$config = OSSMailScanner_Record_Model::getConfig('cron');
		$duration = $config['time'] ?? 0;
		$email = $config['email'] ?? '';
		$dbCommand = App\Db::getInstance()->createCommand();
		$dataReader = (new App\Db\Query())->from('vtiger_ossmails_logs')->where(['status' => 1])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$startTime = strtotime($row['start_time']);
			if ($duration && $email
			&& strtotime('now') > $startTime + ($duration * 60)
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

		$hours = OSSMailScanner_Record_Model::getConfig('cron')['blockCheckHours'] ?? '';
		$hours = $hours ? explode(',', $hours) : [];
		$query = (new \App\Db\Query())->from('roundcube_users')->where(['crm_status' => \OSSMail_Record_Model::MAIL_BOX_STATUS_BLOCKED_TEMP]);
		$dataReader = $query->createCommand()->query();
		while ($account = $dataReader->read()) {
			if (empty($account['actions'])) {
				continue;
			}
			$check = true;
			if (!empty($account['failed_login'])) {
				$check = date('Y-m-d G', strtotime($account['failed_login'])) !== date('Y-m-d G');
				if ($hours && $check) {
					$check = \in_array(date('G'), $hours);
				}
			}
			if ($check) {
				\OSSMail_Record_Model::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], '', false, [], $account);
			}
		}
		$dataReader->close();
	}
}
