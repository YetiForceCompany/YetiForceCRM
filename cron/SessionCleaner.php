<?php
/**
 * Cron to destroy old session.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
$dbCommand = \App\Db::getInstance()->createCommand();
foreach (App\Session::clean() as $userName) {
	$dbCommand->insert('vtiger_loginhistory', [
		'user_name' => $userName,
		'logout_time' => date('Y-m-d H:i:s'),
		'status' => 'Automatic signed off'
	])->execute();
}
