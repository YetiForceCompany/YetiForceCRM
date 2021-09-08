<?php
/**
 * Base test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{
	/** @var mixed Last logs. */
	public $logs;

	/** @var bool Last logs. */
	private $logToFile;

	/**
	 * This method is called when a test method did not execute successfully.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param \Throwable $t
	 */
	protected function onNotSuccessfulTest(\Throwable $t): void
	{
		if (isset($this->logs)) {
			echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
			//var_export(array_shift($t->getTrace()));
			\print_r($this->logs);
			echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		}
		throw $t;
	}

	/**
	 * Disable system logs.
	 *
	 * @return void
	 */
	protected function disableLogs(): void
	{
		$this->logToFile = \App\Log::$logToFile;
		\App\Log::$logToFile = false;
	}

	/**
	 * Enable system logs.
	 *
	 * @return void
	 */
	protected function enableLogs(): void
	{
		\App\Log::$logToFile = $this->logToFile;
	}
}
