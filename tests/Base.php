<?php
/**
 * Base test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests;

abstract class Base extends \PHPUnit\Framework\TestCase
{
	/**
	 * Last logs.
	 *
	 * @var mixed
	 */
	public $logs;

	/**
	 * @codeCoverageIgnore
	 */
	protected function onNotSuccessfulTest(\Throwable $t)
	{
		echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		//var_export(array_shift($t->getTrace()));
		if (isset($this->logs)) {
			var_export($this->logs);
		}
		echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		throw $t;
	}
}
