<?php
namespace App\TextParser;

/**
 * Base TextParser parser class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base
{

	/** @var string Class name */
	public $name = '';

	/** @var array Allowed modules */
	public $allowedModules;

	/** @var \App\TextParser TextParser instance */
	public $textParser;

	/** @var mixed */
	public $params;

	/**
	 * Construct
	 * @param \App\TextParser $textParser
	 * @param mixed $params
	 */
	public function __construct(\App\TextParser $textParser, $params = '')
	{
		$this->textParser = $textParser;
		$this->params = $params;
	}

	/**
	 * Check if function is activated
	 * @return boolean
	 */
	public function isActive()
	{
		if (isset($this->textParser->moduleName) && isset($this->allowedModules) && !in_array($this->textParser->moduleName, $this->allowedModules)) {
			return false;
		}
		return true;
	}
}
