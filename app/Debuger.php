<?php

namespace App;

/**
 * Debuger basic class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use DebugBar;
use DebugBar\DataCollector;

class Debuger
{
	/**
	 * @var \DebugBar\Debuger
	 */
	protected static $debugBar;

	/**
	 * Base path of files javascript.
	 */
	const BASE_PATH_JAVASCRIPT = 'vendor/yetiforce/debugbar/src/DebugBar/Resources';

	/**
	 * Initiating debugging console.
	 *
	 * @return \App\DebugBar\Debuger
	 */
	public static function initConsole()
	{
		$debugbar = new DebugBar\DebugBar();
		$debugbar->addCollector(new DataCollector\PhpInfoCollector());
		$debugbar->addCollector(new DataCollector\RequestDataCollector());
		$debugbar->addCollector(new DataCollector\TimeDataCollector());
		$debugbar->addCollector(new DataCollector\MemoryCollector());
		if (\AppConfig::debug('LOG_TO_CONSOLE')) {
			$debugbar->addCollector(new Debug\DebugBarLogs());
		}
		$debugbar->addCollector(new DataCollector\ExceptionsCollector());

		return static::$debugBar = $debugbar;
	}

	/**
	 * Function to get path of files javascript.
	 *
	 * @return string
	 */
	public static function getJavascriptPath()
	{
		return Layout::getPublicUrl(self::BASE_PATH_JAVASCRIPT);
	}

	/**
	 * Get Debuger instance.
	 *
	 * @return \App\DebugBar\Debuger
	 */
	public static function getDebugBar()
	{
		return static::$debugBar;
	}

	/**
	 * Checking is active debugging.
	 *
	 * @return bool
	 */
	public static function isDebugBar()
	{
		return isset(static::$debugBar);
	}

	public static function addLogs($message, $level, $traces)
	{
		if (isset(static::$debugBar['logs'])) {
			static::$debugBar['logs']->addMessage($message, $level, $traces);
		}
	}

	/**
	 * Initiating debugging.
	 */
	public static function init()
	{
		if (\AppConfig::debug('DISPLAY_DEBUG_CONSOLE') && static::checkIP()) {
			static::initConsole();
		}
		$targets = [];
		if (\AppConfig::debug('LOG_TO_FILE')) {
			$levels = \AppConfig::debug('LOG_LEVELS');
			$target = [
				'class' => 'App\Log\FileTarget',
			];
			if ($levels !== false) {
				$target['levels'] = $levels;
			}
			$targets['file'] = $target;
		}
		if (\AppConfig::debug('LOG_TO_PROFILE')) {
			$levels = \AppConfig::debug('LOG_LEVELS');
			$target = [
				'class' => 'App\Log\Profiling',
			];
			if ($levels !== false) {
				$target['levels'] = $levels;
			}
			$targets['profiling'] = $target;
		}
		\Yii::createObject([
			'class' => 'yii\log\Dispatcher',
			'traceLevel' => \AppConfig::debug('LOG_TRACE_LEVEL'),
			'targets' => $targets,
		]);
	}

	/**
	 * Checking user IP.
	 *
	 * @return bool
	 */
	public static function checkIP()
	{
		$ips = \AppConfig::debug('DEBUG_CONSOLE_ALLOWED_IPS');
		if ($ips === false || (\is_string($ips) && RequestUtil::getRemoteIP(true) === $ips) || (\is_array($ips) && \in_array(RequestUtil::getRemoteIP(true), $ips))) {
			return true;
		}
		return false;
	}

	/**
	 * Generates a backtrace.
	 *
	 * @param int    $minLevel
	 * @param int    $maxLevel
	 * @param string $sep
	 *
	 * @return string
	 */
	public static function getBacktrace($minLevel = 1, $maxLevel = 0, $sep = '#')
	{
		$trace = '';
		foreach (debug_backtrace() as $k => $v) {
			if ($k < $minLevel) {
				continue;
			}
			$l = $k - $minLevel;
			$args = '';
			if (isset($v['args'])) {
				foreach ($v['args'] as &$arg) {
					if (!is_array($arg) && !is_object($arg) && !is_resource($arg)) {
						$args .= var_export($arg, true);
					} elseif (is_array($arg)) {
						$args .= '[';
						foreach ($arg as &$a) {
							$val = $a;
							if (is_array($a) || is_object($a) || is_resource($a)) {
								$val = gettype($a);
								if (is_object($a)) {
									$val .= '(' . get_class($a) . ')';
								}
							}
							$args .= $val . ',';
						}
						$args = rtrim($args, ',') . ']';
					}
					$args .= ',';
				}
				$args = rtrim($args, ',');
			}
			$trace .= "$sep$l";
			if (isset($v['line'])) {
				$trace .= " {$v['file']} ({$v['line']})";
			}
			$trace .= '  >>  ' . (isset($v['class']) ? $v['class'] . '->' : '') . "{$v['function']}($args)" . PHP_EOL;
			unset($args, $val, $v, $k, $a);
			if ($maxLevel !== 0 && $l >= $maxLevel) {
				break;
			}
		}
		return rtrim(str_replace(ROOT_DIRECTORY . DIRECTORY_SEPARATOR, '', $trace), PHP_EOL);
	}
}
