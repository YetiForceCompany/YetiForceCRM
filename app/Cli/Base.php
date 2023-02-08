<?php
/**
 * Base cli file.
 *
 * @package Cli
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Cli;

/**
 * Base cli class.
 */
abstract class Base
{
	/** @var \App\Cli Cli instance. */
	protected $cli;

	/** @var \League\CLImate\CLImate CLImate instance. */
	protected $climate;

	/** @var string Module Name */
	public $moduleName;

	/** @var bool Show only help info */
	public $helpMode = false;

	/**
	 * Construct.
	 *
	 * @param \App\Cli $cli
	 */
	public function __construct(\App\Cli $cli)
	{
		$this->cli = $cli;
		$this->climate = $cli->climate;
	}

	/**
	 * Show confirmation of action execution.
	 *
	 * @param string $message
	 * @param string $parentAction
	 * @param string $description
	 *
	 * @return bool Abort the action in which the function was called
	 */
	protected function confirmation(string $message, string $parentAction, string $description = ''): bool
	{
		$this->climate->arguments->add([
			'confirmation' => [
				'prefix' => 'c',
				'description' => 'Don\'t ask for confirmation',
			],
		]);
		if ($this->helpMode) {
			return true;
		}
		if ($description) {
			$this->climate->lightBlue($description);
		}
		if (!$this->climate->arguments->defined('confirmation') && !$this->climate->confirm($message)->confirmed()) {
			$this->cli->actionsList($parentAction);
			return true;
		}
		return false;
	}
}
