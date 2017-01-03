<?php
/**
 * Travis CI test script
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

use PHPUnit\Framework\TestCase;
// @codeCoverageIgnoreStart
class Times implements PHPUnit_Framework_TestListener
{

	public function startTest(PHPUnit_Framework_Test $test)
	{
		//printf("Test %s started.\n", $test->getName());
		echo "\n";
	}

	public function endTest(PHPUnit_Framework_Test $test, $time)
	{
		$time = round($time, 2);
		echo " Time: $time second(s)";
	}

	public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		echo "Error in " . $test->getName() . " !!!\n";
	}

	public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
	{
		echo "Test " . $test->getName() . " failed. $time\n";
		echo "Exception Message: " . $e->getMessage() . "\n";
		echo "Exception Trace: " . $e->getTraceAsString() . "\n";
	}

	public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		$time = round($time, 2);
		printf("addIncompleteTest: Test '%s' is incomplete.\n", $test->getName());
	}

	public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		printf("Test %s is deemed risky.\n", $test->getName());
		echo "Exception Message: " . $e->getMessage() . "\n";
		echo "Exception Trace: " . $e->getTraceAsString() . "\n";
	}

	public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		$time = round($time, 2);
		printf("Test '%s' has been skipped. ($time second(s))\n", $test->getName());
		echo "Exception Message: " . $e->getMessage() . "\n";
		echo "Exception Trace: " . $e->getTraceAsString() . "\n";
	}

	public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
	{
		//printf("Started all tests: %s \n", $suite->getName());
	}

	public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
	{
		//printf("Ended all tests: %s.\n", $suite->getName());
	}
}
// @codeCoverageIgnoreEnd
