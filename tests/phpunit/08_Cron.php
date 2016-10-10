<?php
/**
 * Cron test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class Cron extends TestCase
{

	public function test()
	{
		echo PHP_EOL;
		require 'cron/vtigercron.php';
	}
}
