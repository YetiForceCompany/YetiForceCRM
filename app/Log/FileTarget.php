<?php
/**
 * FileTarget file records log messages in a file.
 *
 * @package Log
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Qiang Xue <qiang.xue@gmail.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Log;

use Yii;
use yii\base\InvalidConfigException;

/**
 * FileTarget class records log messages in a file.
 */
class FileTarget extends \yii\log\FileTarget
{
	/**
	 * @var bool whether log files should be rotated when they reach a certain [[maxFileSize|maximum size]].
	 *           Log rotation is enabled by default. This property allows you to disable it, when you have configured
	 *           an external tools for log rotation on your server
	 *
	 * @since 2.0.3
	 */
	public $enableRotation = false;

	/**
	 * @var array list of the PHP predefined variables that should be logged in a message.
	 *            Note that a variable must be accessible via `$GLOBALS`. Otherwise it won't be logged
	 */
	public $logVars = ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'];

	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function init()
	{
		if (null === $this->logFile) {
			$this->logFile = ROOT_DIRECTORY . '/cache/logs/system.log';
		} else {
			$this->logFile = Yii::getAlias($this->logFile);
		}
		if ($this->maxLogFiles < 1) {
			$this->maxLogFiles = 1;
		}
		if ($this->maxFileSize < 1) {
			$this->maxFileSize = 1;
		}
	}

	/**
	 * Writes log messages to a file.
	 *
	 * @throws InvalidConfigException if unable to open the log file for writing
	 */
	public function export()
	{
		$text = implode("\n", array_map([$this, 'formatMessage'], $this->messages));
		if (false === ($fp = fopen($this->logFile, 'a'))) {
			throw new InvalidConfigException("Unable to append to log file: {$this->logFile}");
		}
		flock($fp, LOCK_EX);
		if ($this->enableRotation) {
			// clear stat cache to ensure getting the real current file size and not a cached one
			// this may result in rotating twice when cached file size is used on subsequent calls
			clearstatcache();
		}
		if ($this->enableRotation && filesize($this->logFile) > $this->maxFileSize * 1024) {
			$this->rotateFiles();
			flock($fp, LOCK_UN);
			fclose($fp);
			file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);
		} else {
			fwrite($fp, $text);
			flock($fp, LOCK_UN);
			fclose($fp);
		}
		if (null !== $this->fileMode) {
			chmod($this->logFile, $this->fileMode);
		}
	}

	/**
	 * Formats a log message for display as a string.
	 *
	 * @param array $message the log message to be formatted.
	 *                       The message structure follows that in [[Logger::messages]]
	 *
	 * @return string the formatted message
	 */
	public function formatMessage($message)
	{
		[$text, $level, $category, $timestamp] = $message;
		$level = \yii\log\Logger::getLevelName($level);
		if (!\is_string($text)) {
			// exceptions may not be serializable if in the call stack somewhere is a Closure
			if ($text instanceof \Throwable || $text instanceof \Exception) {
				$text = (string) $text;
			} else {
				$text = \yii\helpers\VarDumper::export($text);
			}
		}
		$traces = '';
		if (isset($message[4])) {
			$traces = $message[4];
		}
		if ('' !== $category) {
			$category = '[' . $category . ']';
		}
		$micro = explode('.', $timestamp);
		$micro = end($micro);

		return date('Y-m-d H:i:s', (int) $timestamp) . ".{$micro} [$level][$category] - $text"
			. (empty($traces) ? '' : "\n" . $traces);
	}

	/**
	 * Generates the context information to be logged.
	 * The default implementation will dump user information, system variables, etc.
	 *
	 * @return string the context information. If an empty string, it means no context information
	 */
	protected function getContextMessage()
	{
		if (ROOT_DIRECTORY !== getcwd()) {
			chdir(ROOT_DIRECTORY);
		}
		$result = '';
		$anonymization = new \App\Anonymization();
		try {
			foreach (\yii\helpers\ArrayHelper::filter($GLOBALS, $this->logVars) as $key => $value) {
				if ($value) {
					$result .= "\n\${$key} = " . \App\Utils::varExport($anonymization->setData($value)->anonymize()->getData());
				}
			}
		} catch (\Throwable $th) {
			$result .= "\nError while saving logs: 'GLOBALS': \n" . $th->__toString();
		}
		$result .= "\n_HEADERS = " . \App\Utils::varExport(getallheaders());
		if ('test' !== \App\Config::main('systemMode')) {
			try {
				foreach (\App\Utils\ConfReport::getAllErrors(true) as $key => $value) {
					$result .= "\n\${$key} = " . \App\Utils::varExport($value);
				}
			} catch (\Throwable $th) {
				$result .= "\nError while saving logs: 'ConfReport::getAllErrors': \n" . $th->__toString();
			}
		}
		$result .= PHP_EOL;
		return $result . "====================================================================================================================================\n";
	}
}
