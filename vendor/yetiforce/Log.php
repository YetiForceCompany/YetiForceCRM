<?php
namespace App;

use \yii\log\Logger;

/**
 * Logger class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Log extends Logger
{

	public static $logToConsole;
	public static $logToFile;
	public static $logToProfile;

	/**
	 * Logs a message with the given type and category.
	 * If [[traceLevel]] is greater than 0, additional call stack information about
	 * the application code will be logged as well.
	 * @param string|array $message the message to be logged. This can be a simple string or a more
	 * complex data structure that will be handled by a [[Target|log target]].
	 * @param integer $level the level of the message. This must be one of the following:
	 * `Logger::LEVEL_ERROR`, `Logger::LEVEL_WARNING`, `Logger::LEVEL_INFO`, `Logger::LEVEL_TRACE`,
	 * `Logger::LEVEL_PROFILE_BEGIN`, `Logger::LEVEL_PROFILE_END`.
	 * @param string $category the category of the message.
	 */
	public function log($message, $level, $category = '')
	{
		$traces = '';
		if ($this->traceLevel > 0) {
			$traces = Debuger::getBacktrace(2, $this->traceLevel, ' - ');
		}
		if (static::$logToConsole) {
			Debuger::addLogs($message, self::getLevelName($level), $traces);
		}
		$this->messages[] = [$message, $level, $category, microtime(true), $traces];
		if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval) {
			$this->flush();
		}
	}

	/**
	 * Logs a trace message.
	 * Trace messages are logged mainly for development purpose to see
	 * the execution work flow of some code.
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function trace($message, $category = '')
	{
		if (static::$logToFile) {
			\Yii::getLogger()->log($message, Logger::LEVEL_TRACE, $category);
		}
	}

	/**
	 * Logs an informative message.
	 * An informative message is typically logged by an application to keep record of
	 * something important (e.g. an administrator logs in).
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function info($message, $category = '')
	{
		\Yii::getLogger()->log($message, Logger::LEVEL_INFO, $category);
	}

	/**
	 * Logs a warning message.
	 * A warning message is typically logged when an error occurs while the execution
	 * can still continue.
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function warning($message, $category = '')
	{
		\Yii::getLogger()->log($message, Logger::LEVEL_WARNING, $category);
	}

	/**
	 * Logs an error message.
	 * An error message is typically logged when an unrecoverable error occurs
	 * during the execution of an application.
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function error($message, $category = '')
	{
		\Yii::getLogger()->log($message, Logger::LEVEL_ERROR, $category);
	}

	/**
	 * Marks the beginning of a code block for profiling.
	 * This has to be matched with a call to [[endProfile]] with the same category name.
	 * The begin- and end- calls must also be properly nested. For example,
	 *
	 * ```php
	 * \Yii::beginProfile('block1');
	 * // some code to be profiled
	 *     \Yii::beginProfile('block2');
	 *     // some other code to be profiled
	 *     \Yii::endProfile('block2');
	 * \Yii::endProfile('block1');
	 * ```
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see endProfile()
	 */
	public static function beginProfile($token, $category = '')
	{
		if (static::$logToProfile) {
			\Yii::getLogger()->log($token, Logger::LEVEL_PROFILE_BEGIN, $category);
		}
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to [[beginProfile]] with the same category name.
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see beginProfile()
	 */
	public static function endProfile($token, $category = '')
	{
		if (static::$logToProfile) {
			\Yii::getLogger()->log($token, Logger::LEVEL_PROFILE_END, $category);
		}
	}
}
