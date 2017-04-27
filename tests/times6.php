<?php
/**
 * Travis CI test script
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Michał Lorencik <m.lorencik@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

// @codeCoverageIgnoreStart
class Times implements PHPUnit\Framework\TestListener
{

	public function startTest(PHPUnit\Framework\Test $test)
	{
		//printf("Test %s started.\n", $test->getName());
		echo "\n";
	}

	public function endTest(PHPUnit\Framework\Test $test, $time)
	{
		$time = round($time, 2);
		echo " Time: $time second(s)";
	}

	public function addError(PHPUnit\Framework\Test $test, Exception $e, $time)
	{
		echo "Error in " . $test->getName() . " !!!\n";
	}

	public function addWarning(PHPUnit\Framework\Test $test, PHPUnit\Framework\Warning $e, $time)
	{
		echo "Warning in " . $test->getName() . " !!!\n";
	}

	public function addFailure(PHPUnit\Framework\Test $test, PHPUnit\Framework\AssertionFailedError $e, $time)
	{
		echo "Test " . $test->getName() . " failed. $time\n";
		echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace: " . $e->getTraceAsString() . "\n";
	}

	public function addIncompleteTest(PHPUnit\Framework\Test $test, Exception $e, $time)
	{
		$time = round($time, 2);
		printf("addIncompleteTest: Test '%s' is incomplete.\n", $test->getName());
	}

	public function addRiskyTest(PHPUnit\Framework\Test $test, Exception $e, $time)
	{
		printf("Test %s is deemed risky.\n", $test->getName());
		echo "Exception Message: " . $e->getMessage() . "\n";
		echo "Exception Trace: " . $e->getTraceAsString() . "\n";
	}

	public function addSkippedTest(PHPUnit\Framework\Test $test, Exception $e, $time)
	{
		$time = round($time, 2);
		printf("Test '%s' has been skipped. ($time second(s))\n", $test->getName());
		echo "Exception Message: " . $e->getMessage() . "\n";
		echo "Exception Trace: " . $e->getTraceAsString() . "\n";
	}

	public function startTestSuite(PHPUnit\Framework\TestSuite $suite)
	{
		//printf("Started all tests: %s \n", $suite->getName());
	}

	public function endTestSuite(PHPUnit\Framework\TestSuite $suite)
	{
		//printf("Ended all tests: %s.\n", $suite->getName());
	}
}

// @codeCoverageIgnoreEnd
