<?php

namespace App\Session;

/**
 * Base Session Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class File extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public static function clean()
	{
		$time = microtime(true);
		$lifeTime = \AppConfig::security('MAX_LIFETIME_SESSION');
		$oldSessionId = session_id();
		$oldSessionData = $_SESSION;
		$exclusion = ['.htaccess', 'index.html', 'sess_' . $oldSessionId];
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'session', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile() && !in_array($item->getBasename(), $exclusion)) {
				session_decode(file_get_contents($item->getPathname()));
				if (empty($_SESSION['last_activity']) || $time - $_SESSION['last_activity'] < $lifeTime) {
					continue;
				}
				unlink($item->getPathname());
				if (!empty($_SESSION['authenticated_user_id'])) {
					$userName = \App\User::getUserModel(empty($_SESSION['baseUserId']) ? $_SESSION['authenticated_user_id'] : $_SESSION['baseUserId'])->getDetail('user_name');
					if (!empty($userName)) {
						yield $userName;
					}
				}
			}
		}
		session_id($oldSessionId);
		$_SESSION = $oldSessionData;
	}
}
