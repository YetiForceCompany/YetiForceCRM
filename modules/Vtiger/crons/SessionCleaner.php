<?php
/**
 * Cron to destroy old session.
 *
 * @package   Cron
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_SessionCleaner_Cron class.
 */
class Vtiger_SessionCleaner_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$dbCommand = \App\Db::getInstance('webservice')->createCommand();
		$dbCommand->delete('w_#__portal_session', ['<', 'created', date('Y-m-d H:i:s', strtotime('now') - \Config\Security::$apiLifetimeSessionCreate * 60)])->execute();
		$dbCommand->delete('w_#__portal_session', ['<', 'changed', date('Y-m-d H:i:s', strtotime('now') - \Config\Security::$apiLifetimeSessionUpdate * 60)])->execute();
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
