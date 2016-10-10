<?php namespace includes;

/**
 * Debuger basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use DebugBar;

class Debuger
{

	protected static $debugBar;

	static public function init()
	{
		$debugbar = new DebugBar\StandardDebugBar();
		return self::$debugBar = $debugbar;
	}

	static public function getDebugBar()
	{
		return self::$debugBar;
	}
}
