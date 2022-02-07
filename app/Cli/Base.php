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
}
