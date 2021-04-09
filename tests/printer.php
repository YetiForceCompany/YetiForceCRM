<?php

/**
 * Tests result printer class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\PhptTestCase;

/** @codeCoverageIgnoreStart */
class YtResultPrinter extends PHPUnit\TextUI\DefaultResultPrinter
{
	/**
	 * Logs files to show.
	 *
	 * @var string[]
	 */
	private $logFiles = [
		'/var/log/fpm-php.www.log',
		'/var/log/php_error.log',
		// '/var/log/nginx/localhost_access.log',
		'/var/log/nginx/localhost_error.log',
		'/var/log/nginx/error.log',
		// '/var/log/mysql/localhost_access.log',
		'/var/log/mysql/error.log',
		'cache/logs/system.log',
		'cache/logs/errors.log',
		// 'cache/logs/request.log',
		'cache/logs/phpError.log',
		'cache/logs/Importer.log',
		'cache/logs/webserviceErrors.log',
		// 'cache/logs/webserviceDebug.log',
		'tests/records.log',
	];

	/**
	 * Get test name.
	 *
	 * @param PHPUnit\Framework\Test $test
	 *
	 * @return string
	 */
	public function getTestName(Test $test): string
	{
		return str_replace(['Tests\\', '\\'], ['', ' '], \get_class($test)) . ' -> ' . $test->getName();
	}

	/**
	 * A test started.
	 *
	 * @param PHPUnit\Framework\Test $test
	 *
	 *  @return void
	 */
	public function startTest(Test $test): void
	{
		if ($this->debug) {
			$this->write($this->getTestName($test));
		}
	}

	/**
	 * A test ended.
	 *
	 * @param PHPUnit\Framework\Test $test
	 * @param float                  $time
	 *
	 * @return void
	 */
	public function endTest(Test $test, float $time): void
	{
		if ($this->debug) {
			$time = round($time, 2);
			echo " - $time second(s) | Assertions: " . $test->getNumAssertions();
			if (!$this->lastTestFailed) {
				$this->writeProgress('.');
			}
		}
		if ($test instanceof TestCase) {
			$this->numAssertions += $test->getNumAssertions();
		} elseif ($test instanceof PhptTestCase) {
			++$this->numAssertions;
		}
		$this->lastTestFailed = false;
		if ($test instanceof TestCase) {
			if (!$test->hasExpectationOnOutput() && ($out = $test->getActualOutput())) {
				$this->writeWithColor('bold,fg-green', "+++++++  {$this->getTestName($test)} | Test output   ++++++++", false);
				$this->write(PHP_EOL . $out);
				$this->write(str_repeat('+', 100) . PHP_EOL);
			}
		}
		if ($this->debug) {
			$this->write(PHP_EOL);
		}
	}

	/**
	 * Print result.
	 *
	 * @param PHPUnit\Framework\TestResult $result
	 *
	 * @return void
	 */
	public function printResult(TestResult $result): void
	{
		$this->write(str_repeat('*', 140));
		parent::printResult($result);
		$this->write(PHP_EOL . str_repeat('*', 140));
		$this->showLogs();
	}

	/**
	 * Start test suite.
	 *
	 * @param PHPUnit\Framework\TestSuite $suite
	 *
	 * @return void
	 */
	public function startTestSuite(TestSuite $suite): void
	{
		parent::startTestSuite($suite);
	}

	/**
	 * End test suite.
	 *
	 * @param PHPUnit\Framework\TestSuite $suite
	 *
	 * @return void
	 */
	public function endTestSuite(TestSuite $suite): void
	{
		//printf("Ended all tests: %s.\n", $suite->getName());
		parent::endTestSuite($suite);
	}

	public function addError(Test $test, Throwable $t, float $time): void
	{
		$time = round($time, 2);
		$this->writeProgressWithColor('fg-red', '!!! Test ' . $test->getName() . ' error.');
		$this->write(PHP_EOL);
		$this->lastTestFailed = true;
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		// parent::addError($test, $t, $time);
	}

	public function addWarning(Test $test, Warning $e, float $time): void
	{
		$time = round($time, 2);
		$this->writeProgressWithColor('fg-yellow', '! Test ' . $test->getName() . ' warning !!!.');
		$this->write(PHP_EOL);
		$this->lastTestFailed = true;
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		// parent::addWarning($test, $e, $time);
	}

	public function addFailure(Test $test, AssertionFailedError $e, float $time): void
	{
		$this->writeProgressWithColor('bg-red, fg-white', '! Test ' . $this->getTestName($test) . ' failed !!!' . PHP_EOL . $e->__toString());
		$this->write(PHP_EOL);
		$this->lastTestFailed = true;
		$time = round($time, 2);
		// echo '!!! Test ' . $test->getName() . " failed.\n";
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		//parent::addFailure($test, $e, $time);
	}

	public function addIncompleteTest(Test $test, Throwable $t, float $time): void
	{
		$time = round($time, 2);
		printf("addIncompleteTest: Test '%s' is incomplete.\n", $test->getName());
		parent::addIncompleteTest($test, $t, $time);
	}

	public function addRiskyTest(Test $test, Throwable $t, float $time): void
	{
		printf("! Test %s is deemed risky.\n", $test->getName());
		//echo "Exception Message: " . $e->getMessage() . "\n";
		//echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
		parent::addRiskyTest($test, $t, $time);
	}

	public function addSkippedTest(Test $test, Throwable $t, float $time): void
	{
		$time = round($time, 2);
		$this->writeProgressWithColor('fg-cyan, bold', "! Test '{$this->getTestName($test)}' has been skipped. ($time second(s))\n" . PHP_EOL . $t->__toString());
		$this->lastTestFailed = true;
	}

	private function showLogs(): void
	{
		array_unshift($this->logFiles, '/var/log/php' . getenv('PHP_VER') . '-fpm.log');
		foreach ($this->logFiles as $file) {
			if (false === strpos($file, '/var/log')) {
				$file = realpath(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $file);
			}
			if (file_exists($file)) {
				$content = file_get_contents($file);
				if ($content) {
					$this->writeWithColor('bold,fg-green', "\nLogs:  $file", false);
					$this->write(PHP_EOL . str_repeat('-', 50) . PHP_EOL);
					echo $content;
					$this->write(str_repeat('+', 100));
				}
			}
		}
	}
}

// @codeCoverageIgnoreEnd
