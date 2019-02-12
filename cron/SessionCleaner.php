<?php
/**
 * Cron to destroy old session.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
if (!headers_sent()) {
	$dbCommand = \App\Db::getInstance()->createCommand();
	foreach (App\Session::clean() as $userId => $userName) {
		$dbCommand->insert('vtiger_loginhistory', [
			'user_name' => $userName,
			'logout_time' => date('Y-m-d H:i:s'),
			'status' => 'Automatic signed off'
		])->execute();
		OSSMail_Logout_Model::logutUserById($userId);
	}
} else {
	\App\Log::warning('Session cleaning has been omitted because the server headers have already been sent');
}
