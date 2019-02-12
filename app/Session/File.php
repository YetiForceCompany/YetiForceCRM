<?php

namespace App\Session;

/**
 * Base Session Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
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
		$exclusion = ['.htaccess', 'index.html', 'sess_' . session_id()];
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'session', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile() && !in_array($item->getBasename(), $exclusion)) {
				$sessionData = static::unserialize(file_get_contents($item->getPathname()));
				if (!empty($sessionData['last_activity']) && $time - $sessionData['last_activity'] < $lifeTime) {
					continue;
				}
				unlink($item->getPathname());
				if (!empty($sessionData['authenticated_user_id'])) {
					$userId = empty($sessionData['baseUserId']) ? $sessionData['authenticated_user_id'] : $sessionData['baseUserId'];
					$userName = \App\User::getUserModel($userId)->getDetail('user_name');
					if (!empty($userName)) {
						yield $userId => $userName;
					}
				}
			}
		}
	}

	/**
	 * Deserialize session data from string, entry function.
	 *
	 * @param string $session
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NotAllowedMethod
	 *
	 * @return array
	 *
	 * @example http://php.net/manual/en/function.session-decode.php#108037
	 */
	public static function unserialize(string $session)
	{
		$method = ini_get('session.serialize_handler');
		switch ($method) {
			case 'php':
				return self::unserializePhp($session);
				break;
			case 'php_binary':
				return self::unserializePhpBinary($session);
				break;
			default:
				throw new \App\Exceptions\NotAllowedMethod('Unsupported session.serialize_handler: ' . $method . '. Supported: php, php_binary');
		}
	}

	/**
	 * Deserialize session data from string php handler method.
	 *
	 * @param string $session
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return array
	 */
	private static function unserializePhp(string $session)
	{
		$return = [];
		$offset = 0;
		while ($offset < strlen($session)) {
			if (!strstr(substr($session, $offset), '|')) {
				throw new \App\Exceptions\IllegalValue('invalid data, remaining: ' . substr($session, $offset));
			}
			$pos = strpos($session, '|', $offset);
			$num = $pos - $offset;
			$varName = substr($session, $offset, $num);
			$offset += $num + 1;
			$data = unserialize(substr($session, $offset), ['allowed_classes' => false]);
			$return[$varName] = $data;
			$offset += \strlen(serialize($data));
		}
		return $return;
	}

	/**
	 * Deserialize session data from string php_binary handler method.
	 *
	 * @param string $session
	 *
	 * @return array
	 */
	private static function unserializePhpBinary(string $session)
	{
		$return = [];
		$offset = 0;
		while ($offset < \strlen($session)) {
			$num = \ord($session[$offset]);
			++$offset;
			$varName = substr($session, $offset, $num);
			$offset += $num;
			$data = unserialize(substr($session, $offset), ['allowed_classes' => false]);
			$return[$varName] = $data;
			$offset += \strlen(serialize($data));
		}
		return $return;
	}
}
