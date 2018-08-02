<?php

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;

/**
 * Travis CI test script.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
// @codeCoverageIgnoreStart

class YtTimes implements PHPUnit\Framework\TestListener
{
	public function startTest(Test $test): void
	{
		//printf("Test %s started.\n", $test->getName());
		//echo "\n";
	}

	public function endTest(Test $test, float $time): void
	{
		$time = round($time, 2);
		echo " - $time second(s) | Assertions: " . $test->getNumAssertions();
	}

	public function addError(Test $test, \Throwable $t, float $time): void
	{
		$time = round($time, 2);
		echo '! Test ' . $test->getName() . " error.\n";
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
	}

	public function addWarning(Test $test, Warning $e, float $time): void
	{
		$time = round($time, 2);
		echo '! Test ' . $test->getName() . " warning.\n";
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
	}

	public function addFailure(Test $test, AssertionFailedError $e, float $time): void
	{
		$time = round($time, 2);
		echo '! Test ' . $test->getName() . " failed.\n";
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
	}

	public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
	{
		$time = round($time, 2);
		printf("addIncompleteTest: Test '%s' is incomplete.\n", $test->getName());
	}

	public function addRiskyTest(Test $test, \Throwable $t, float $time): void
	{
		printf("! Test %s is deemed risky.\n", $test->getName());
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
	}

	public function addSkippedTest(Test $test, \Throwable $t, float $time): void
	{
		$time = round($time, 2);
		printf("! Test '%s' has been skipped. ($time second(s))\n", $test->getName());
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
	}

	public function startTestSuite(TestSuite $suite): void
	{
	}

	public function endTestSuite(TestSuite $suite): void
	{
		//printf("Ended all tests: %s.\n", $suite->getName());
	}
}

// @codeCoverageIgnoreEnd
