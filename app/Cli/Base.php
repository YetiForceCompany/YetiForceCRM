<?php
/**
 * Base cli file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
}
