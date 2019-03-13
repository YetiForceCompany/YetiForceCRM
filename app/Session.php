<?php

namespace App;

/**
 * Session class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Session
{
	/**
	 * @var type
	 */
	public static $pool;

	/**
	 * Initialize session class.
	 */
	public static function init()
	{
		if (\session_status() === PHP_SESSION_ACTIVE) {
			return;
		}

		$driver = \AppConfig::performance('SESSION_DRIVER');
		if ($driver) {
			$className = '\App\Session\\' . $driver;
			static::$pool = new $className();
			\session_set_save_handler(static::$pool, true);
		}
		\session_start();
	}

	/**
	 * Returns a session Item representing the specified key.
	 *
	 * @param string $key
	 *
	 * @return string|array
	 */
	public static function get($key)
	{
		if (empty(static::$pool)) {
			return $_SESSION[$key] ?? null;
		}
		return static::$pool->get($key);
	}

	/**
	 * Confirms if the session contains specified session item.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function has($key)
	{
		if (empty(static::$pool)) {
			return isset($_SESSION[$key]);
		}
		return static::$pool->has($key);
	}

	/**
	 * Session Save.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public static function set($key, $value = null)
	{
		if (empty(static::$pool)) {
			return $_SESSION[$key] = $value;
		}
		return static::$pool->set($key, $value);
	}

	/**
	 * Removes the item from the session.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function delete($key)
	{
		if (empty(static::$pool)) {
			unset($_SESSION[$key]);
		}
		static::$pool->delete($key);
	}

	/**
	 * Update the current session id with a newly generated one.
	 *
	 * @link http://php.net/manual/en/function.session-regenerate-id.php
	 *
	 * @param bool $deleteOldSession
	 */
	public static function regenerateId($deleteOldSession = false)
	{
		if (empty(static::$pool)) {
			\session_regenerate_id($deleteOldSession);
		} else {
			static::$pool->regenerateId($deleteOldSession);
		}
	}

	/**
	 * Destroys all data registered to a session.
	 *
	 * @link http://php.net/manual/en/function.session-destroy.php
	 */
	public static function destroy()
	{
		$_SESSION = [];
		if (PHP_SAPI !== 'cli' && !headers_sent()) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params['path'], $params['domain'],
				$params['secure'], $params['httponly']
			);
		}
		\session_destroy();
	}

	/**
	 * Function to clean session. Removed old session.
	 *
	 * @return string[]
	 */
	public static function clean()
	{
		if (!empty(static::$pool)) {
			return static::$pool->clean();
		}
		return [];
	}
}
