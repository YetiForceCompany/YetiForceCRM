<?php
/**
 * Debugger basic file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

use DebugBar\DataCollector;

/**
 * Debuger basic class.
 */
class Debuger
{
	/**
	 * @var \DebugBar\Debuger
	 */
	protected static $debugBar;

	/**
	 * Initiating debugging console.
	 *
	 * @return \App\DebugBar\Debuger
	 */
	public static function initConsole()
	{
		if (\App\Config::debug('DISPLAY_DEBUG_CONSOLE') && static::consoleIsActive()) {
			$debugbar = new Debug\DebugBar\DebugBar();
			$debugbar->addCollector(new DataCollector\PhpInfoCollector());
			$debugbar->addCollector(new DataCollector\ExceptionsCollector());
			$debugbar->addCollector(new DataCollector\RequestDataCollector());
			$debugbar->addCollector(new DataCollector\MemoryCollector());
			$debugbar->addCollector(new DataCollector\TimeDataCollector());
			if (\App\Config::debug('DISPLAY_LOGS_IN_CONSOLE')) {
				$debugbar->addCollector(new Debug\DebugBarLogs());
			}
			if (\App\Config::debug('DISPLAY_CONFIG_IN_CONSOLE')) {
				$debugbar->addCollector(new \DebugBar\DataCollector\ConfigCollector([
					'debug' => \App\Config::debug(),
					'developer' => \App\Config::developer(),
					'performance' => \App\Config::performance(),
					'api' => \App\Config::api(),
					'security' => \App\Config::security(),
					'search' => \App\Config::search(),
					'sounds' => \App\Config::sounds(),
					'relation' => \App\Config::relation(),
				]));
			}
			static::$debugBar = $debugbar;
		}
	}

	/**
	 * Get Debuger instance.
	 *
	 * @return \App\Debug\DebugBar\DebugBar
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
		$targets = [];
		if (\App\Config::debug('LOG_TO_FILE')) {
			$levels = \App\Config::debug('LOG_LEVELS');
			$target = [
				'class' => 'App\Log\FileTarget',
			];
			if (false !== $levels) {
				$target['levels'] = $levels;
			}
			$targets['file'] = $target;
		}
		if (\App\Config::debug('LOG_TO_PROFILE')) {
			$levels = \App\Config::debug('LOG_LEVELS');
			$target = [
				'class' => 'App\Log\Profiling',
			];
			if (false !== $levels) {
				$target['levels'] = $levels;
			}
			$targets['profiling'] = $target;
		}
		\Yii::createObject([
			'class' => 'yii\log\Dispatcher',
			'traceLevel' => \App\Config::debug('LOG_TRACE_LEVEL'),
			'targets' => $targets,
		]);
	}

	/**
	 * Checking console is active.
	 *
	 * @return bool
	 */
	public static function consoleIsActive()
	{
		$ips = \Config\Debug::$DEBUG_CONSOLE_ALLOWED_IPS;
		if (false === $ips || (\is_string($ips) && RequestUtil::getRemoteIP(true) === $ips) || (\is_array($ips) && \in_array(RequestUtil::getRemoteIP(true), $ips))) {
			if (\Config\Debug::$DEBUG_CONSOLE_ALLOWED_USERS && !\in_array(\App\User::getCurrentUserRealId(), \Config\Debug::$DEBUG_CONSOLE_ALLOWED_USERS)) {
				return false;
			}
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
					if (!\is_array($arg) && !\is_object($arg) && !\is_resource($arg)) {
						$args .= var_export($arg, true);
					} elseif (\is_array($arg)) {
						$args .= '[';
						foreach ($arg as &$a) {
							$val = $a;
							if (\is_array($a) || \is_object($a) || \is_resource($a)) {
								$val = \gettype($a);
								if (\is_object($a)) {
									$val .= '(' . \get_class($a) . ')';
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
				$trace .= " {$v['file']}:{$v['line']}";
			}
			$trace .= '  >>  ' . (isset($v['class']) ? $v['class'] . '->' : '') . "{$v['function']}($args)" . PHP_EOL;
			unset($args, $val, $v, $k, $a);
			if (0 !== $maxLevel && $l >= $maxLevel) {
				break;
			}
		}
		return rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $trace), PHP_EOL);
	}
}
