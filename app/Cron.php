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

/**
 * Class to handle Cron operations.
 */
class Cron
{
	/**
	 * Cron run start time in microtime.
	 *
	 * @var null|int Cron run start time in microtime
	 */
	public static $cronTimeStart = null;
	/**
	 * Script run start time in microtime.
	 *
	 * @var null|int Script run start time in microtime
	 */
	public static $scriptTimeStart = null;
	/**
	 * @var string Log files directory path
	 */
	public $logPath = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'logs' . \DIRECTORY_SEPARATOR . 'cron' . \DIRECTORY_SEPARATOR;
	/**
	 * @var bool|string Current log file name
	 */
	public $logFile = false;
	/**
	 * @var bool Logging enabled flag
	 */
	public static $logActive = false;
	/**
	 * @var bool Flag to keep log file after run finish
	 */
	public static $keepLogFile = false;

	/**
	 * Init and configure object.
	 *
	 * @throws \App\Exceptions\CacheException
	 */
	public function __construct()
	{
		static::$scriptTimeStart = microtime(true);
		static::generateStatusFile();
		YetiForce\Register::check();
		YetiForce\Status::send();
		if (!(static::$logActive = \AppConfig::debug('DEBUG_CRON'))) {
			return;
		}
		if (!is_dir($this->logPath) && !mkdir($this->logPath, 0777, true) && !is_dir($this->logPath)) {
			static::$logActive = false;
			Log::error("The mechanism of cron logs has been disabled !!!. No access to the log directory '{$this->logPath}'");
		}
		if (!$this->logFile) {
			$this->logFile = date('Ymd_Hi') . '.log';
		}
		$this->log('File start', 'info', false);
	}

	/**
	 * Add log message.
	 *
	 * @param string $message log information
	 * @param string $level   information type [info, warning, error]
	 * @param bool   $indent  add three spaces at message begin
	 */
	public function log(string $message, string $level = 'info', bool $indent = true)
	{
		if (!static::$logActive) {
			return;
		}
		if ($level === 'warning' || $level === 'error') {
			static::$keepLogFile = true;
		}
		if ($indent) {
			$message = '   ' . $message;
		}
		file_put_contents($this->logPath . $this->logFile, date('Y-m-d H:i:s') . " [{$level}] - {$message}" . PHP_EOL, FILE_APPEND);
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
			if (!static::$logActive) {
				return;
			}
			if (\file_exists($this->logPath . $this->logFile)) {
				unlink($this->logPath . $this->logFile);
			}
		} else {
			$this->log('------------------------------------' . PHP_EOL . \App\Log::getlastLogs(), 'info', false);
		}
	}

	/**
	 * Calculate current object run time.
	 *
	 * @return float|null
	 */
	public function getCronExecutionTime()
	{
		return static::$cronTimeStart ? round(microtime(true) - static::$cronTimeStart, 2) : null;
	}
}
