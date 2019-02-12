<?php

namespace App;

use yii\log\Logger;

/**
 * Logger class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Log extends Logger
{
	public static $logToConsole;
	public static $logToFile;
	public static $logToProfile;

	/**
	 * Column mapping by table.
	 *
	 * @var array
	 */
	public static $tableColumnMapping = [
		'access_for_admin' => ['date', 'username', 'ip', 'module', 'url', 'agent', 'request', 'referer'],
		'access_for_api' => ['date', 'username', 'ip', 'url', 'agent', 'request'],
		'access_for_user' => ['date', 'username', 'ip', 'module', 'url', 'agent', 'request', 'referer'],
		'access_to_record' => ['date', 'username', 'ip', 'module', 'record', 'url', 'agent', 'request', 'referer'],
		'csrf' => ['date', 'username', 'ip', 'referer', 'url', 'agent'],
	];

	/**
	 * Logs a message with the given type and category.
	 * If [[traceLevel]] is greater than 0, additional call stack information about
	 * the application code will be logged as well.
	 *
	 * @param string|array $message  the message to be logged. This can be a simple string or a more
	 *                               complex data structure that will be handled by a [[Target|log target]]
	 * @param int          $level    the level of the message. This must be one of the following:
	 *                               `Logger::LEVEL_ERROR`, `Logger::LEVEL_WARNING`, `Logger::LEVEL_INFO`, `Logger::LEVEL_TRACE`,
	 *                               `Logger::LEVEL_PROFILE_BEGIN`, `Logger::LEVEL_PROFILE_END`
	 * @param string       $category the category of the message
	 */
	public function log($message, $level, $category = '')
	{
		$traces = '';
		if ($this->traceLevel) {
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
	 *
	 * @param string $message  the message to be logged
	 * @param string $category the category of the message
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
	 *
	 * @param string $message  the message to be logged
	 * @param string $category the category of the message
	 */
	public static function info($message, $category = '')
	{
		if (static::$logToFile) {
			\Yii::getLogger()->log($message, Logger::LEVEL_INFO, $category);
		}
	}

	/**
	 * Logs a warning message.
	 * A warning message is typically logged when an error occurs while the execution
	 * can still continue.
	 *
	 * @param string $message  the message to be logged
	 * @param string $category the category of the message
	 */
	public static function warning($message, $category = '')
	{
		if (static::$logToFile) {
			\Yii::getLogger()->log($message, Logger::LEVEL_WARNING, $category);
		}
	}

	/**
	 * Logs an error message.
	 * An error message is typically logged when an unrecoverable error occurs
	 * during the execution of an application.
	 *
	 * @param string $message  the message to be logged
	 * @param string $category the category of the message
	 */
	public static function error($message, $category = '')
	{
		\Yii::getLogger()->log($message, Logger::LEVEL_ERROR, $category);
	}

	/**
	 * Marks the beginning of a code block for profiling.
	 * This has to be matched with a call to [[endProfile]] with the same category name.
	 * The begin- and end- calls must also be properly nested. For example,.
	 *
	 * ```php
	 * \Yii::beginProfile('block1');
	 * // some code to be profiled
	 *     \Yii::beginProfile('block2');
	 *     // some other code to be profiled
	 *     \Yii::endProfile('block2');
	 * \Yii::endProfile('block1');
	 * ```
	 *
	 * @param string $token    token for the code block
	 * @param string $category the category of this log message
	 *
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
	 *
	 * @param string $token    token for the code block
	 * @param string $category the category of this log message
	 *
	 * @see beginProfile()
	 */
	public static function endProfile($token, $category = '')
	{
		if (static::$logToProfile) {
			\Yii::getLogger()->log($token, Logger::LEVEL_PROFILE_END, $category);
		}
	}

	/**
	 * Get user action logs.
	 *
	 * @param string $type
	 * @param string $mode
	 * @param bool   $countMode
	 *
	 * @return array
	 */
	public static function getLogs($type, $mode, $countMode = false)
	{
		$db = \App\Db::getInstance('log');
		$query = (new \App\Db\Query())->from('o_#__' . $type);
		if ($mode === 'oneDay') {
			$query->where(['>=', 'date', date('Y-m-d H:i:s', strtotime('-1 day'))]);
		} else {
			$query->limit(100);
		}
		if ($countMode) {
			return $query->count('*', $db);
		} else {
			$query->orderBy(['id' => SORT_DESC]);
			return $query->all($db);
		}
	}

	/**
	 * Get last logs.
	 *
	 * @return string
	 */
	public static function getlastLogs()
	{
		$content = '';
		$i = 0;
		foreach (\Yii::getLogger()->messages as $message) {
			$level = \yii\log\Logger::getLevelName($message[1]);
			$category = $message[2];
			$content .= "#$i [$level] {$message[0]}";
			if ($category) {
				$content .= ' || ' . $category;
			}
			$content .= PHP_EOL;
			++$i;
		}
		return $content;
	}
}
