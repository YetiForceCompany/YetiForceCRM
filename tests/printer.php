<?php

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\PhptTestCase;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;

/**
 * Travis CI result printer class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
// @codeCoverageIgnoreStart
class YtResultPrinter extends PHPUnit\TextUI\ResultPrinter
{
	/**
	 * A test started.
	 *
	 * @param Test $test
	 */
	public function startTest(Test $test): void
	{
		if ($this->debug) {
			$this->write(str_replace(['Tests\\', '\\'], ['', ' '], \get_class($test)) . ' -> ' . $test->getName());
		}
	}

	/**
	 * A test ended.
	 *
	 * @param Test  $test
	 * @param float $time
	 */
	public function endTest(Test $test, float $time): void
	{
		if ($this->debug) {
			echo " - $time second(s) | Assertions: " . $test->getNumAssertions();
		}
		if (!$this->lastTestFailed) {
			$this->writeProgress('.');
		}
		if ($test instanceof TestCase) {
			$this->numAssertions += $test->getNumAssertions();
		} elseif ($test instanceof PhptTestCase) {
			$this->numAssertions++;
		}
		$this->lastTestFailed = false;
		if ($test instanceof TestCase) {
			if (!$test->hasExpectationOnOutput() && ($out = $test->getActualOutput())) {
				$this->write("\n++++++++++++++++   Test output   ++++++++++++++++++++++++++++\n");
				$this->write($out);
				$this->write("\n++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n");
			}
		}
		$this->write("\n");
	}

	/**
	 * @param TestResult $result
	 */
	public function printResult(TestResult $result): void
	{
		$this->write("\n==========================================================================================================");
		parent::printResult($result);
		$this->write("\n==========================================================================================================");
	}

	public function startTestSuite(TestSuite $suite): void
	{
		parent::startTestSuite($suite);
	}

	public function endTestSuite(TestSuite $suite): void
	{
		//printf("Ended all tests: %s.\n", $suite->getName());
		parent::endTestSuite($suite);
	}

	public function addError(Test $test, \Throwable $t, float $time): void
	{
		$time = round($time, 2);
		echo '! Test ' . $test->getName() . " error.\n";
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		parent::addError($test, $t, $time);
	}

	public function addWarning(Test $test, Warning $e, float $time): void
	{
		$time = round($time, 2);
		echo '! Test ' . $test->getName() . " warning.\n";
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		parent::addWarning($test, $e, $time);
	}

	public function addFailure(Test $test, AssertionFailedError $e, float $time): void
	{
		$time = round($time, 2);
		echo '! Test ' . $test->getName() . " failed.\n";
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		parent::addFailure($test, $e, $time);
	}

	public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
	{
		$time = round($time, 2);
		printf("addIncompleteTest: Test '%s' is incomplete.\n", $test->getName());
		parent::addIncompleteTest($test, $t, $time);
	}

	public function addRiskyTest(Test $test, \Throwable $t, float $time): void
	{
		printf("! Test %s is deemed risky.\n", $test->getName());
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		parent::addRiskyTest($test, $t, $time);
	}

	public function addSkippedTest(Test $test, \Throwable $t, float $time): void
	{
		$time = round($time, 2);
		printf("! Test '%s' has been skipped. ($time second(s))\n", $test->getName());
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		parent::addSkippedTest($test, $t, $time);
	}
}

// @codeCoverageIgnoreEnd
