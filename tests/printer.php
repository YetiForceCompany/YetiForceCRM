<?php

use PHPUnit\Framework\PhptTestCase;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;

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
		$this->write(\get_class($test) . '::' . $test->getName());
	}

	/**
	 * A test ended.
	 *
	 * @param Test  $test
	 * @param float $time
	 */
	public function endTest(Test $test, float $time): void
	{
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
}

// @codeCoverageIgnoreEnd
