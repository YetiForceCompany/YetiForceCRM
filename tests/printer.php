<?php
/**
 * Travis CI result printer class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\Test;

// @codeCoverageIgnoreStart
class YtResultPrinter extends \PHPUnit\TextUI\ResultPrinter
{

	/**
	 * A test started.
	 *
	 * @param Test $test
	 */
	public function startTest(Test $test)
	{
		if ($this->debug) {
			$this->write(
				\sprintf(
					"\n%s", \PHPUnit\Util\Test::describe($test)
				)
			);
		}
	}

	/**
	 * A testsuite started.
	 *
	 * @param TestSuite $suite
	 */
	public function startTestSuite(TestSuite $suite)
	{
		parent::startTestSuite($suite);
		echo PHP_EOL . '================   ' . $suite->getName() . '   ================';
	}
}

// @codeCoverageIgnoreEnd
