<?php
/**
 * Cron to destroy old session.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_SessionCleaner_Cron class.
 */
class Vtiger_SessionCleaner_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$dbCommand = \App\Db::getInstance('webservice')->createCommand();
		foreach (\Api\Core\Containers::$listTables as $row) {
			if (!isset($row['session'])) {
				continue;
			}
			$dbCommand->delete($row['session'], ['<', 'created', date('Y-m-d H:i:s', strtotime('now') - \Config\Security::$apiLifetimeSessionCreate * 60)])->execute();
			$dbCommand->delete($row['session'], ['<', 'changed', date('Y-m-d H:i:s', strtotime('now') - \Config\Security::$apiLifetimeSessionUpdate * 60)])->execute();
		}
		if (!headers_sent()) {
			$dbCommand = \App\Db::getInstance()->createCommand();
			foreach (App\Session\File::clean() as $userId => $userName) {
				$dbCommand->insert('vtiger_loginhistory', [
					'user_name' => $userName,
					'userid' => $userId,
					'logout_time' => date('Y-m-d H:i:s'),
					'status' => 'Automatic signed off'
				])->execute();
				OSSMail_Logout_Model::logutUserById($userId);
			}
		} else {
			\App\Log::warning('Session cleaning has been omitted because the server headers have already been sent');
		}
	}
}
