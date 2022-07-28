<?php
/**
 * Exception error handler class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Exception error handler class.
 */
class ErrorHandler
{
	/**
	 * Errors level.
	 *
	 * @var string[]
	 */
	private static $levelNames = [
		E_ERROR => 'E_ERROR',
		E_WARNING => 'E_WARNING',
		E_PARSE => 'E_PARSE',
		E_NOTICE => 'E_NOTICE',
		E_STRICT => 'E_STRICT',
		E_CORE_ERROR => 'E_CORE_ERROR',
		E_CORE_WARNING => 'E_CORE_WARNING',
		E_COMPILE_ERROR => 'E_COMPILE_ERROR',
		E_COMPILE_WARNING => 'E_COMPILE_WARNING',
		E_USER_ERROR => 'E_USER_ERROR',
		E_USER_WARNING => 'E_USER_WARNING',
		E_USER_NOTICE => 'E_USER_NOTICE',
	];

	/**
	 * Error init.
	 *
	 * @return void
	 */
	public static function init(): void
	{
		if (\class_exists('rcmail')) {
			return;
		}
		register_shutdown_function([__CLASS__, 'fatalHandler']);
		set_error_handler([__CLASS__, 'errorHandler'], \App\Config::debug('EXCEPTION_ERROR_LEVEL'));
	}

	/**
	 * PHP fatal handler function.
	 *
	 * @return void
	 */
	public static function fatalHandler(): void
	{
		$error = error_get_last();
		if (isset($error['type']) && \in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
			static::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}

	/**
	 * PHP error handler function.
	 *
	 * @see https://secure.php.net/manual/en/function.set-error-handler.php
	 *
	 * @param int    $no
	 * @param string $str
	 * @param string $file
	 * @param int    $line
	 *
	 * @return void
	 */
	public static function errorHandler(int $no, string $str, string $file, int $line): void
	{
		$errorString = static::error2string($no);
		$msg = reset($errorString) . ": $str in $file, line $line";
		if (\App\Config::debug('EXCEPTION_ERROR_TO_FILE')) {
			$logFile = ROOT_DIRECTORY . '/cache/logs/errors.log';
			$content = print_r($msg, true) . PHP_EOL . \App\Debuger::getBacktrace(2) . PHP_EOL;
			file_put_contents($logFile, $content, FILE_APPEND);
		}
		if (\App\Config::debug('EXCEPTION_ERROR_TO_SHOW')) {
			\vtlib\Functions::throwNewException($msg, false);
		}
	}

	/**
	 * Convert error number to string.
	 *
	 * @param int $value
	 *
	 * @return string[]
	 */
	public static function error2string(int $value): array
	{
		$levels = [];
		if (E_ALL == ($value & E_ALL)) {
			$levels[] = 'E_ALL';
			$value &= ~E_ALL;
		}
		foreach (self::$levelNames as $level => $name) {
			if (($value & $level) == $level) {
				$levels[] = $name;
			}
		}
		return $levels;
	}
}
