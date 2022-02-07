<?php

namespace App\TextParser;

/**
 * Base TextParser parser class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base
{
	/** @var string Class name */
	public $name = '';

	/** @var array Allowed modules */
	public $allowedModules;

	/** @var \App\TextParser TextParser instance */
	public $textParser;

	/** @var mixed Additional params */
	public $params;

	/** @var mixed Parser type */
	public $type;

	/**
	 * Construct.
	 *
	 * @param \App\TextParser $textParser
	 * @param mixed           $params
	 */
	public function __construct(\App\TextParser $textParser, $params = '')
	{
		$this->textParser = $textParser;
		$this->params = $params;
	}

	/**
	 * Check if function is activated.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		if (isset($this->textParser->moduleName, $this->allowedModules) && !\in_array($this->textParser->moduleName, $this->allowedModules)) {
			return false;
		}
		if (isset($this->textParser->type) && $this->textParser->type !== $this->type) {
			return false;
		}
		return true;
	}
}
