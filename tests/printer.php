<?php
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;

/**
 * Travis CI result printer class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
			$this->write("\n" . \get_class($test) . '::' . $test->getName());
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
		$debug = $this->debug;
		$this->debug = false;

		parent::endTest($test, $time);

		if ($this->debug) {
			$this->write('  |');
		}
		$this->debug = $debug;
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
