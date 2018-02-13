<?php
/**
 * Travis CI test script.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
// @codeCoverageIgnoreStart

class times implements PHPUnit\Framework\TestListener
{
    public function startTest(PHPUnit\Framework\Test $test)
    {
        //printf("Test %s started.\n", $test->getName());
        //echo "\n";
    }

    public function endTest(PHPUnit\Framework\Test $test, $time)
    {
        $time = round($time, 2);
        echo " - $time second(s) | Assertions: ".$test->getNumAssertions();
    }

    public function addError(PHPUnit\Framework\Test $test, \Exception $e, $time)
    {
        $time = round($time, 2);
        echo '! Test '.$test->getName()." error.\n";
        //echo "Exception Message: " . $e->getMessage() . "\n";
        //echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
    }

    public function addWarning(PHPUnit\Framework\Test $test, PHPUnit\Framework\Warning $e, $time)
    {
        $time = round($time, 2);
        echo '! Test '.$test->getName()." warning.\n";
        //echo "Exception Message: " . $e->getMessage() . "\n";
        //echo "Exception Trace:\n" . $e->getTraceAsString() . "\n";
    }

    public function addFailure(PHPUnit\Framework\Test $test, PHPUnit\Framework\AssertionFailedError $e, $time)
    {
        $time = round($time, 2);
        echo '! Test '.$test->getName()." failed.\n";
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
    }

    public function endTestSuite(PHPUnit\Framework\TestSuite $suite)
    {
        //printf("Ended all tests: %s.\n", $suite->getName());
    }
}

// @codeCoverageIgnoreEnd
