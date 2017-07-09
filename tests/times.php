<?php

/**
 * Travis CI test script
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
// @codeCoverageIgnoreStart
use PHPUnit\Framework\TestCase;

if (version_compare(PHP_VERSION, '7.0.0', '>=')) {

	class Times implements PHPUnit\Framework\TestListener
	{

		public function startTest(PHPUnit\Framework\Test $test)
		{
			//printf("Test %s started.\n", $test->getName());
			//echo "\n";
		}

		public function endTest(PHPUnit\Framework\Test $test, $time)
		{
			$time = round($time, 2);
			echo " - $time second(s) | Assertions: " . $test->getNumAssertions();
		}

		public function addError(PHPUnit\Framework\Test $test, \Exception $e, $time)
		{
			$time = round($time, 2);
			echo "! Test " . $test->getName() . " error.\n";
			//echo "Exception Message: " . $e->getMessage() . "\n";
			//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		}

		public function addWarning(PHPUnit\Framework\Test $test, PHPUnit\Framework\Warning $e, $time)
		{
			$time = round($time, 2);
			echo "! Test " . $test->getName() . " warning.\n";
			//echo "Exception Message: " . $e->getMessage() . "\n";
			//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		}

		public function addFailure(PHPUnit\Framework\Test $test, PHPUnit\Framework\AssertionFailedError $e, $time)
		{
			$time = round($time, 2);
			echo "! Test " . $test->getName() . " failed.\n";
			//echo "Exception Message: " . $e->getMessage() . "\n";
			//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		}

		public function addIncompleteTest(PHPUnit\Framework\Test $test, \Exception $e, $time)
		{
			$time = round($time, 2);
			printf("addIncompleteTest: Test '%s' is incomplete.\n", $test->getName());
		}

		public function addRiskyTest(PHPUnit\Framework\Test $test, \Exception $e, $time)
		{
			printf("! Test %s is deemed risky.\n", $test->getName());
			//echo "Exception Message: " . $e->getMessage() . "\n";
			//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		}

		public function addSkippedTest(PHPUnit\Framework\Test $test, \Exception $e, $time)
		{
			$time = round($time, 2);
			printf("! Test '%s' has been skipped. ($time second(s))\n", $test->getName());
			//echo "Exception Message: " . $e->getMessage() . "\n";
			//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
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

} else {

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
			echo " - $time second(s) | Assertions: " . $test->getNumAssertions();
		}

		public function addError(PHPUnit_Framework_Test $test, \Exception $e, $time)
		{
			$time = round($time, 2);
			echo "! Test " . $test->getName() . " error.\n";
			//echo "Exception Message: " . $e->getMessage() . "\n";
			//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		}

		public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
		{
			$time = round($time, 2);
			echo "! Test " . $test->getName() . " warning.\n";
			//echo "Exception Message: " . $e->getMessage() . "\n";
			//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		}

		public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
		{
			$time = round($time, 2);
			echo "! Test " . $test->getName() . " failed.\n";
			//echo "Exception Message: " . $e->getMessage() . "\n";
			//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		}

		public function addIncompleteTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
		{
			$time = round($time, 2);
			printf("addIncompleteTest: Test '%s' is incomplete.\n", $test->getName());
		}

		public function addRiskyTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
		{
			printf("! Test %s is deemed risky.\n", $test->getName());
			//echo "Exception Message: " . $e->getMessage() . "\n";
			//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		}

		public function addSkippedTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
		{
			$time = round($time, 2);
			printf("! Test '%s' has been skipped. ($time second(s))\n", $test->getName());
			//echo "Exception Message: " . $e->getMessage() . "\n";
			//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
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

}

// @codeCoverageIgnoreEnd
