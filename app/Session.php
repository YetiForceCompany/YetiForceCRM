<?php

namespace App;

/**
 * Session class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Session
{
	/**
	 *  @var string Session path
	 */
	const SESSION_PATH = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'session';

	/**
	 * Session handler.
	 *
	 * @var \App\Session\Base
	 */
	public static $pool;

	/**
	 * Initialize session class.
	 */
	public static function init()
	{
		if (PHP_SESSION_ACTIVE === \session_status()) {
			return;
		}
		if (self::load()) {
			\session_set_save_handler(self::$pool, true);
		}
		\session_start();
	}

	/**
	 * Load session driver.
	 *
	 * @return bool
	 */
	public static function load(): bool
	{
		if (empty(self::$pool) && !empty(\Config\Performance::$SESSION_DRIVER)) {
			$className = '\App\Session\\' . \Config\Performance::$SESSION_DRIVER;
			self::$pool = new $className();
			return true;
		}
		return false;
	}

	/**
	 * Returns a session Item representing the specified key.
	 *
	 * @param string $key
	 *
	 * @return array|string
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
	 * @see https://php.net/manual/en/function.session-regenerate-id.php
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
	 * @see https://php.net/manual/en/function.session-destroy.php
	 */
	public static function destroy()
	{
		$_SESSION = [];
		if (\PHP_SAPI !== 'cli' && !headers_sent()) {
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

	/**
	 * Function to clean all session.
	 *
	 * @return int
	 */
	public static function cleanAll(): int
	{
		if (!empty(static::$pool)) {
			return static::$pool->cleanAll();
		}
		return 0;
	}

	/**
	 * Function to get session data by id.
	 *
	 * @param string $sessionId
	 *
	 * @return array
	 */
	public static function getById(string $sessionId): array
	{
		if (!empty(static::$pool)) {
			return static::$pool->getById($sessionId);
		}
		return [];
	}
}
