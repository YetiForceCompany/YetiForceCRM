<?php
/**
 * Logger files.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

use yii\log\Logger;

/**
 * Logger class.
 */
class Log extends Logger
{
	public static $logToConsole;
	public static $logToFile;
	public static $logToProfile;
	public $logToLevels = 0;
	/**
	 * Column mapping by table for logs owasp.
	 *
	 * @var array
	 */
	public static $owaspColumnMapping = [
		'access_for_admin' => ['date', 'username', 'ip', 'module', 'url', 'agent', 'request', 'referer'],
		'access_for_api' => ['date', 'username', 'ip', 'url', 'agent', 'request'],
		'access_for_user' => ['date', 'username', 'ip', 'module', 'url', 'agent', 'request', 'referer'],
		'access_to_record' => ['date', 'username', 'ip', 'module', 'record', 'url', 'agent', 'request', 'referer'],
		'csrf' => ['date', 'username', 'ip', 'referer', 'url', 'agent'],
	];
	/**
	 * Column mapping by table for logs viewer.
	 *
	 * @var array
	 */
	public static $logsViewerColumnMapping = [
		'magento' => [
			'label' => 'LBL_MAGENTO',
			'labelModule' => 'Settings:Magento',
			'table' => 'l_#__magento',
			'icon' => 'yfi-magento',
			'columns' => [
				'time' => ['type' => 'DateTime', 'label' => 'LBL_TIME'],
				'category' => ['type' => 'Text', 'label' => 'LBL_CATEGORY'],
				'message' => ['type' => 'Text', 'label' => 'LBL_MESSAGE'],
				'code' => ['type' => 'Text', 'label' => 'LBL_CODE'],
				'trace' => ['type' => 'Text', 'label' => 'LBL_BACKTRACE'],
			],
			'filter' => [
				'time' => 'DateTimeRange',
				'category' => 'Text',
				'message' => 'Text',
				'code' => 'Text',
				'trace' => 'Text',
			],
		],
		'wapro' => [
			'label' => 'LBL_WAPRO_ERP',
			'labelModule' => 'Settings:Wapro',
			'table' => 'l_#__wapro',
			'icon' => 'fab fa-connectdevelop',
			'columns' => [
				'time' => ['type' => 'DateTime', 'label' => 'LBL_TIME'],
				'category' => ['type' => 'Text', 'label' => 'LBL_CATEGORY'],
				'message' => ['type' => 'Text', 'label' => 'LBL_MESSAGE'],
				'error' => ['type' => 'Text', 'label' => 'LBL_CODE'],
				'trace' => ['type' => 'Text', 'label' => 'LBL_BACKTRACE'],
			],
			'filter' => [
				'time' => 'DateTimeRange',
				'category' => 'Text',
				'message' => 'Text',
				'code' => 'Text',
				'trace' => 'Text',
			],
		],
		'switchUsers' => [
			'label' => 'LBL_SWITCH_USERS',
			'labelModule' => 'Settings:Users',
			'table' => 'l_#__switch_users',
			'icon' => 'yfi-users',
			'columns' => [
				'date' => ['type' => 'DateTime', 'label' => 'LBL_TIME'],
				'status' => ['type' => 'Text', 'label' => 'LBL_STATUS'],
				'busername' => ['type' => 'Text', 'label' => 'LBL_BASE_USER'],
				'dusername' => ['type' => 'Text', 'label' => 'LBL_DEST_USER'],
				'ip' => ['type' => 'Text', 'label' => 'LBL_IP_ADDRESS'],
				'agent' => ['type' => 'Text', 'label' => 'LBL_USER_AGENT'],
			],
			'filter' => [
				'date' => 'DateTimeRange',
				'busername' => 'Text',
				'dusername' => 'Text',
				'ip' => 'Text',
				'agent' => 'Text',
			],
		],
		'batchMethod' => [
			'label' => 'LBL_BATCH_METHODS',
			'labelModule' => 'Settings:CronTasks',
			'table' => 'l_#__batchmethod',
			'icon' => 'fas fa-swatchbook',
			'columns' => [
				'date' => ['type' => 'DateTime', 'label' => 'LBL_TIME'],
				'method' => ['type' => 'Text', 'label' => 'LBL_BATCH_NAME'],
				'message' => ['type' => 'Text', 'label' => 'LBL_ERROR_MASAGE'],
				'userid' => ['type' => 'Owner', 'label' => 'LBL_OWNER'],
				'params' => ['type' => 'Text', 'label' => 'LBL_PARAMS'],
			],
			'filter' => [
				'date' => 'DateTimeRange',
				'method' => 'Text',
				'message' => 'Text',
				'params' => 'Text',
			],
		],
		'mail' => [
			'label' => 'LBL_MAILS_NOT_SENT',
			'labelModule' => 'Settings:Log',
			'table' => 'l_#__mail',
			'icon' => 'adminIcon-mail-queue',
			'columns' => [
				'date' => ['type' => 'DateTime', 'label' => 'LBL_TIME'],
				'subject' => ['type' => 'Text', 'label' => 'LBL_SUBJECT'],
				'from' => ['type' => 'Text', 'label' => 'LBL_FROM'],
				'to' => ['type' => 'Text', 'label' => 'LBL_TO'],
				'owner' => ['type' => 'Owner', 'label' => 'LBL_OWNER'],
			],
			'filter' => [
				'date' => 'DateTimeRange',
				'subject' => 'Text',
				'from' => 'Text',
				'to' => 'Text',
			],
		],
		'profile' => [
			'label' => 'LBL_PROFILING',
			'labelModule' => 'Settings:Log',
			'table' => 'l_#__profile',
			'icon' => 'fas fa-stopwatch',
			'columns' => [
				'category' => ['type' => 'Text', 'label' => 'Category'],
				'info' => ['type' => 'Text', 'label' => 'LBL_PARAMS'],
				'log_time' => ['type' => 'Text', 'label' => 'LBL_TIME'],
				'trace' => ['type' => 'Text', 'label' => 'LBL_BACKTRACE'],
				'duration' => ['type' => 'Text', 'label' => 'LBL_DURATION'],
			],
			'filter' => [
				'category' => 'Text',
				'subinfoject' => 'Text',
				'log_time' => 'Text',
				'trace' => 'Text',
				'duration' => 'Text',
			],
		],
	];
	public static $levelMap = [
		'error' => Logger::LEVEL_ERROR,
		'warning' => Logger::LEVEL_WARNING,
		'info' => Logger::LEVEL_INFO,
		'trace' => Logger::LEVEL_TRACE,
		'profile' => Logger::LEVEL_PROFILE,
	];

	/**
	 * Initializes the logger by registering [[flush()]] as a shutdown function.
	 */
	public function init()
	{
		parent::init();
		if (\Config\Debug::$LOG_LEVELS) {
			$this->setLevels(\Config\Debug::$LOG_LEVELS);
		}
	}

	/**
	 * Sets the message levels that this target is interested in.
	 *
	 * @param array|int $levels message levels that this target is interested in.
	 */
	public function setLevels($levels)
	{
		if (\is_array($levels)) {
			foreach ($levels as $level) {
				if (isset(self::$levelMap[$level])) {
					$this->logToLevels |= self::$levelMap[$level];
				} else {
					throw new Exceptions\AppException("Unrecognized level: $level");
				}
			}
		} else {
			$bitmapValues = array_reduce(self::$levelMap, fn ($carry, $item) => $carry | $item);
			if (!($bitmapValues & $levels) && 0 !== $levels) {
				throw new Exceptions\AppException("Incorrect $levels value");
			}
			$this->logToLevels = $levels;
		}
	}

	/**
	 * Logs a message with the given type and category.
	 * If [[traceLevel]] is greater than 0, additional call stack information about
	 * the application code will be logged as well.
	 *
	 * @param array|string $message  the message to be logged. This can be a simple string or a more
	 *                               complex data structure that will be handled by a [[Target|log target]]
	 * @param int          $level    the level of the message. This must be one of the following:
	 *                               `Logger::LEVEL_ERROR`, `Logger::LEVEL_WARNING`, `Logger::LEVEL_INFO`, `Logger::LEVEL_TRACE`,
	 *                               `Logger::LEVEL_PROFILE_BEGIN`, `Logger::LEVEL_PROFILE_END`
	 * @param string       $category the category of the message
	 */
	public function log($message, $level, $category = '')
	{
		if (0 !== $this->logToLevels && !($this->logToLevels & $level)) {
			return;
		}
		$traces = '';
		if ($this->traceLevel) {
			$traces = Debuger::getBacktrace(2, $this->traceLevel, ' - ');
		}
		if (static::$logToConsole) {
			Debuger::addLogs($message, self::getLevelName($level), $traces);
		}
		$this->messages[] = [$message, $level, $category, microtime(true), $traces];
		if ($this->flushInterval > 0 && \count($this->messages) >= $this->flushInterval) {
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
		if (static::$logToFile) {
			\Yii::getLogger()->log($message, Logger::LEVEL_ERROR, $category);
		}
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
			$categories = \Config\Debug::$LOG_PROFILE_CATEGORIES ?? [];
			if ($categories && !\in_array($category, $categories)) {
				return;
			}
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
			$categories = \Config\Debug::$LOG_PROFILE_CATEGORIES ?? [];
			if ($categories && !\in_array($category, $categories)) {
				return;
			}
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
		if ('oneDay' === $mode) {
			$query->where(['>=', 'date', date('Y-m-d H:i:s', strtotime('-1 day'))]);
		} else {
			$query->limit(100);
		}
		if ($countMode) {
			return $query->count('*', $db);
		}
		$query->orderBy(['id' => SORT_DESC]);
		return $query->all($db);
	}

	/**
	 * Get last logs.
	 *
	 * @param bool|string[] $types
	 *
	 * @return string
	 */
	public static function getlastLogs($types = false)
	{
		$content = '';
		$i = 0;
		foreach (\Yii::getLogger()->messages as $message) {
			$level = \yii\log\Logger::getLevelName($message[1]);
			if (false !== $types && !\in_array($level, $types)) {
				continue;
			}
			$content .= "#$i [$level]";
			$category = $message[2] ?: '';
			if ($category) {
				$content .= "[$category]";
			}
			$content .= " {$message[0]}" . PHP_EOL;
			++$i;
		}
		return $content;
	}
}
