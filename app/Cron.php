<?php
/**
 * Cron.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace App;

class Cron
{
	/**
	 * Cron run start time in microtime.
	 *
	 * @var null|int Cron run start time in microtime
	 */
	public static $timeStart = null;
	/**
	 * @var string Log files directory path
	 */
	public static $logPath = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'logs' . \DIRECTORY_SEPARATOR . 'cron' . \DIRECTORY_SEPARATOR;
	/**
	 * @var bool Current log file name
	 */
	public static $logFile = false;
	/**
	 * @var bool Flag to keep log file after run finish
	 */
	public static $keepLogFile = false;

	/**
	 * Init and configure object.
	 *
	 * @throws \App\Exceptions\CacheException
	 */
	public function init()
	{
		static::$timeStart = microtime(true);
		static::generateStatusFile();
		if (!\AppConfig::debug('DEBUG_CRON')) {
			return;
		}
		if (!is_dir(static::$logPath) && !mkdir(static::$logPath, 0777, true) && !is_dir(static::$logPath)) {
			throw new \App\Exceptions\CacheException('ERR_CRON_LOG_DIRECTORY_CREATION_ERROR');
		}
		static::initLogFile();
	}

	/**
	 * Add log message.
	 *
	 * @param string $message log information
	 * @param string $level   information type [info, warning, error]
	 * @param bool   $indent  add three spaces at message begin
	 */
	public function log($message, $level = 'info', $indent = true)
	{
		if (!\AppConfig::debug('DEBUG_CRON')) {
			return;
		}
		if (\in_array($level, ['warning', 'error'])) {
			static::$keepLogFile = true;
		}
		if ($indent) {
			$message = '   ' . $message;
		}
		switch ($level) {
			case 'error':
				$code = 'E';
				break;
			case 'warning':
				$code = 'W';
				break;
			default:
				$code = 'I';
				break;
		}

		file_put_contents(static::$logPath . static::$logFile, date('Y-m-d H:i:s') . ' ' . $code . ' - ' . $message . PHP_EOL, FILE_APPEND);
	}

	/**
	 * Init variables and create/append a log file.
	 */
	protected static function initLogFile()
	{
		if (!\AppConfig::debug('DEBUG_CRON')) {
			return;
		}
		if (!static::$logFile) {
			static::$logFile = date('Ymd_Hi') . '.log';
		}
		file_put_contents(static::$logPath . static::$logFile, date('Y-m-d H:i:s') . ' I - File start' . PHP_EOL, FILE_APPEND);
	}

	/**
	 * Gather and save information for YetiForce Status module.
	 *
	 * @return bool|int
	 */
	public static function generateStatusFile()
	{
		return file_put_contents(ROOT_DIRECTORY . '/user_privileges/cron.php', '<?php return ' . Utils::varExport(array_merge(Utils\ConfReport::getAll(), ['last_start' => time()])) . ';');
	}

	/**
	 * Remove log file if no value information was stored.
	 */
	public function __destruct()
	{
		if (!static::$keepLogFile) {
			if (!\AppConfig::debug('DEBUG_CRON')) {
				return;
			}
			unlink(static::$logPath . static::$logFile);
		} else {
			static::log('------------------------------------' . PHP_EOL . \App\Log::getlastLogs(), 'info', false);
		}
	}

	/**
	 * Calculate current object run time.
	 *
	 * @return float|null
	 */
	public function getRunTime()
	{
		if (!static::$timeStart) {
			return null;
		}
		return round(microtime(true) - static::$timeStart, 2);
	}
}
