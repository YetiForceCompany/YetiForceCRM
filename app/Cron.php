<?php
/**
 * Cron.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Class to handle Cron operations.
 */
class Cron
{
	/**
	 * @var int status disabled
	 */
	const STATUS_DISABLED = 0;
	/**
	 * @var int status enabled
	 */
	const STATUS_ENABLED = 1;
	/**
	 * @var int status running
	 */
	const STATUS_RUNNING = 2;
	/**
	 * @var int status completed
	 */
	const STATUS_COMPLETED = 3;

	/**
	 * Cron run start time in microtime.
	 *
	 * @var int|null Cron run start time in microtime
	 */
	public static $cronTimeStart = null;

	/**
	 * Script run start time in microtime.
	 *
	 * @var int|null Script run start time in microtime
	 */
	public static $scriptTimeStart = null;

	/**
	 * @var string Log files directory path
	 */
	public $logPath = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'logs' . \DIRECTORY_SEPARATOR . 'cron' . \DIRECTORY_SEPARATOR;

	/**
	 * @var bool|string Current log file name
	 */
	public $logFile = false;

	/**
	 * @var bool Logging enabled flag
	 */
	public static $logActive = false;

	/** @var bool Flag to keep log file after run finish */
	public static $keepLogFile = false;

	/** @var int Max execution cron time. */
	private static $maxExecutionCronTime;

	/** @var bool Register enabled flag */
	public static $registerIsActive = true;

	/** @var bool Watchdog enabled flag */
	public static $watchdogIsActive = true;

	/** @var bool Shop enabled flag */
	public static $shopIsActive = true;

	/** @var bool ConfReport enabled flag */
	public static $confReportIsActive = true;

	/**
	 * Init and configure object.
	 *
	 * @throws \App\Exceptions\CacheException
	 */
	public function __construct()
	{
		static::$scriptTimeStart = microtime(true);
		static::generateStatusFile();
		if (self::$watchdogIsActive) {
			YetiForce\Watchdog::send();
		}
		if (self::$registerIsActive) {
			YetiForce\Register::check();
		}
		if (self::$shopIsActive) {
			YetiForce\Shop::generateCache();
		}
		if (!(static::$logActive = Config::debug('DEBUG_CRON'))) {
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
		if ('error' === $level) {
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
		$all = [];
		if (self::$confReportIsActive) {
			$all = Utils\ConfReport::getAll();
		}
		$all['last_start'] = time();
		return file_put_contents(ROOT_DIRECTORY . '/app_data/cron.php', '<?php return ' . Utils::varExport($all) . ';', LOCK_EX);
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
			$this->log('------------------------------------' . PHP_EOL . Log::getlastLogs(), 'info', false);
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

	/**
	 * Update cron task status by name.
	 *
	 * @param int    $status
	 * @param string $name
	 *
	 * @return void
	 */
	public static function updateStatus(int $status, string $name): void
	{
		switch ((int) $status) {
			case self::STATUS_DISABLED:
			case self::STATUS_ENABLED:
			case self::STATUS_RUNNING:
				break;
			default:
				throw new Exceptions\AppException('Invalid status');
		}
		Db::getInstance()->createCommand()->update('vtiger_cron_task', ['status' => $status], ['name' => $name])->execute();
	}

	/**
	 * Get max execution cron time.
	 *
	 * @return int
	 */
	public static function getMaxExecutionTime(): int
	{
		if (isset(self::$maxExecutionCronTime)) {
			return self::$maxExecutionCronTime;
		}
		$maxExecutionTime = (int) Config::main('maxExecutionCronTime');
		$iniMaxExecutionTime = (int) ini_get('max_execution_time');
		if (0 !== $iniMaxExecutionTime && $iniMaxExecutionTime < $maxExecutionTime) {
			$maxExecutionTime = $iniMaxExecutionTime;
		}
		return self::$maxExecutionCronTime = $maxExecutionTime;
	}

	/**
	 * Check max execution cron time.
	 *
	 * @return bool
	 */
	public function checkCronTimeout(): bool
	{
		return time() >= (self::getMaxExecutionTime() + self::$cronTimeStart);
	}

	/**
	 * Check if it is active function.
	 *
	 * @param string $className
	 *
	 * @return bool
	 */
	public static function checkActive(string $className): bool
	{
		return (new Db\Query())
			->from('vtiger_cron_task')
			->where(['status' => [self::STATUS_ENABLED, self::STATUS_RUNNING], 'handler_class' => $className])
			->exists();
	}
}
