<?php
/**
 * Exception error handler class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * This is the list of currently registered HTTP status codes.
	 *
	 * @var array
	 */
	public static $httpStatusCodes = [
		200 => 'OK',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version not supported',
		511 => 'Network Authentication Required', // RFC 6585
		1001 => 'SQL Error',
	];

	/**
	 * Error init.
	 */
	public static function init()
	{
		if (\class_exists('rcmail')) {
			return;
		}
		register_shutdown_function([__CLASS__, 'fatalHandler']);
		set_error_handler([__CLASS__, 'errorHandler'], \App\Config::debug('EXCEPTION_ERROR_LEVEL'));
	}

	/**
	 * PHP fatal handler function.
	 */
	public static function fatalHandler()
	{
		$error = error_get_last();
		if (isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
			static::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}

	/**
	 * PHP error handler function.
	 *
	 * @param int    $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int    $errline
	 *
	 * @see https://secure.php.net/manual/en/function.set-error-handler.php
	 */
	public static function errorHandler($errno, $errstr, $errfile, $errline)
	{
		$errorString = static::error2string($errno);
		$msg = reset($errorString) . ": $errstr in $errfile, line $errline";
		if (\App\Config::debug('EXCEPTION_ERROR_TO_FILE')) {
			$file = ROOT_DIRECTORY . '/cache/logs/errors.log';
			$content = print_r($msg, true) . PHP_EOL . \App\Debuger::getBacktrace(2) . PHP_EOL;
			file_put_contents($file, $content, FILE_APPEND);
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
	public static function error2string($value)
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

	/**
	 * Parse exception data function.
	 *
	 * @param \Throwable $e
	 *
	 * @return array
	 */
	public static function parseException(\Throwable $e): array
	{
		$trace = \Config\Debug::$displayExceptionBacktrace ? str_replace(\ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $e->getTraceAsString()) : '';
		$message = 'Internal Server Error';
		$code = 0 === $e->getCode() ? 500 : $e->getCode();
		if ($e instanceof \yii\db\Exception) {
			$code = 1001;
		}
		if (\Config\Debug::$displayExceptionMessage) {
			$message = $e->getMessage();
			if (false === strpos($message, '||')) {
				$message = Language::translateSingleMod($message, 'Other.Exceptions');
			} else {
				$params = explode('||', $message);
				$message = call_user_func_array('vsprintf', [Language::translateSingleMod(array_shift($params), 'Other.Exceptions'), $params]);
			}
		} elseif (isset(static::$httpStatusCodes[$code])) {
			$message = static::$httpStatusCodes[$code];
		}
		return [
			'code' => $code,
			'message' => $message,
			'trace' => $trace
		];
	}
}
