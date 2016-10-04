<?php namespace App;

/**
 * Debuger basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use DebugBar;
use DebugBar\DataCollector;
use Yii;

class Debuger
{

	protected static $debugBar;

	static public function initConsole()
	{
		$debugbar = new DebugBar\DebugBar();
		$debugbar->addCollector(new DataCollector\PhpInfoCollector());
		$debugbar->addCollector(new DataCollector\RequestDataCollector());
		$debugbar->addCollector(new DataCollector\TimeDataCollector());
		$debugbar->addCollector(new DataCollector\MemoryCollector());
		$debugbar->addCollector(new debug\DebugBarLogs());
		//$debugbar->addCollector(new DataCollector\ExceptionsCollector());
		return self::$debugBar = $debugbar;
	}

	static public function getDebugBar()
	{
		return self::$debugBar;
	}

	public static function addLogs($message, $level, $category, $time, $traces)
	{

		self::$debugBar["logs"]->addMessage($message, $category);
	}

	static public function initLogger()
	{
		$targets = [];
		if (\AppConfig::debug('LOG_TO_FILE')) {
			$levels = \AppConfig::debug('LOG_LEVELS');
			$target = [
				'class' => 'App\log\FileTarget'
			];
			if ($levels !== false) {
				$target['levels'] = $levels;
			}
			$targets['file'] = $target;
		}
		if (!empty($targets)) {
			Yii::createObject([
				'class' => 'yii\log\Dispatcher',
				'traceLevel' => \AppConfig::debug('LOG_TRACE_LEVEL'),
				'targets' => $targets
			]);
		}
	}
}
