<?php
/**
 * Base test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
namespace Tests\Init;

class Base extends \PHPUnit\Framework\TestCase
{

	/**
	 * @codeCoverageIgnore
	 */
	protected function onNotSuccessfulTest(\Throwable $t)
	{
		var_export(array_shift($t->getTrace()));
		throw $t;
	}
}
