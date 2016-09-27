<?php namespace App;

/**
 * Debuger basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use DebugBar;
use DebugBar\DataCollector;
class Debuger
{

	protected static $debugBar;

	static public function init()
	{
		$debugbar = new DebugBar\DebugBar();
        $debugbar->addCollector(new DataCollector\PhpInfoCollector());
        $debugbar->addCollector(new DataCollector\RequestDataCollector());
        $debugbar->addCollector(new DataCollector\TimeDataCollector());
        $debugbar->addCollector(new DataCollector\MemoryCollector());
        $debugbar->addCollector(new DataCollector\ExceptionsCollector());
		return self::$debugBar = $debugbar;
	}

	static public function getDebugBar()
	{
		return self::$debugBar;
	}
}
