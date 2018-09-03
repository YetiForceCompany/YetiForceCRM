<?php
/**
 * Reseting record number test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace Tests\Entity;

/**
 * Class RecordNumber.
 *
 * @codeCoverageIgnore
 */
class RecordNumber extends \App\Fields\RecordNumber
{
	/**
	 * @var array of dates
	 */
	public static $dates = [
		'2015-01-01',
		'2015-03-03',
		'2015-03-03',
		'2015-03-03',
		'2015-03-04',
		'2015-03-04',
		'2015-03-05',
		'2015-11-09',
		'2015-11-10',
		'2015-11-11',
		'2015-11-28',
		'2016-11-29',
		'2017-03-15',
		'2017-03-18',
		'2017-07-19',
		'2018-01-01',
		'2018-01-02',
		'2018-01-02',
		'2018-02-03',
		'2018-05-05'
	];

	/**
	 * @var int
	 */
	public static $currentDateIndex = 0;

	/**
	 * Date method mock for testing purposes.
	 *
	 * @param string   $format
	 * @param null|int $time
	 *
	 * @return false|string
	 */
	public static function date($format, $time = null)
	{
		if (!isset(self::$dates[self::$currentDateIndex])) {
			self::$currentDateIndex = 0;
		}
		return date($format, strtotime(self::$dates[self::$currentDateIndex]));
	}
}

class Z_ResetingRecordNumber extends \Tests\Base
{
	/**
	 * Database transaction pointer.
	 *
	 * @var \yii\db\Transaction
	 */
	private static $transaction;

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass()
	{
		static::$transaction = \App\Db::getInstance()->beginTransaction();
	}

	/**
	 * Test method "DateMock".
	 */
	public function testDateMock()
	{
		$this->assertCount(20, RecordNumber::$dates);
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$this->assertSame($date, RecordNumber::date('Y-m-d'));
		}
	}

	/**
	 * Test method "StandardNumber".
	 */
	public function testSequenceNumber()
	{
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$parts = explode('-', $date);
			$this->assertSame($parts[0], RecordNumber::getSequenceNumber('Y'));
			$this->assertSame($parts[0] . $parts[1], RecordNumber::getSequenceNumber('M'));
			$this->assertSame($parts[0] . $parts[1] . $parts[2], RecordNumber::getSequenceNumber('D'));
		}
	}

	/**
	 * Test method "IncrementNumberStandard".
	 */
	public function testIncrementNumberStandard()
	{
		RecordNumber::setNumber(95, 'F-I', 1);
		$originalRow = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => 95])->one();
		$this->assertSame('F-I', $originalRow['prefix']);
		$this->assertSame('', $originalRow['postfix']);
		$this->assertSame(null, $originalRow['reset_sequence']);
		$this->assertSame('', $originalRow['cur_sequence']);
		$actualNumber = $originalRow['cur_id'];
		foreach (RecordNumber::$dates as $index => $date) {
			$this->assertSame("F-I$actualNumber", RecordNumber::incrementNumber(95));
			$actualNumber++;
			$number = RecordNumber::getNumber(95, false);
			$this->assertSame($actualNumber, $number['sequenceNumber']);
			$this->assertSame(null, $number['reset_sequence']);
			$this->assertSame('', $number['cur_sequence']);
			$this->assertSame('F-I', $number['prefix']);
			$this->assertSame('', $number['postfix']);
		}
	}

	/**
	 * Test method "Parse".
	 * Test parsing method for record numbers on different dates.
	 */
	public function testParse()
	{
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$parts = explode('-', $date);
			$this->assertSame($parts[2] . '/1', RecordNumber::parse('{{DD}}/', 1, '', 0));
			$this->assertSame($parts[1] . '/1', RecordNumber::parse('{{MM}}/', 1, '', 0));
			$this->assertSame($parts[0] . '/1', RecordNumber::parse('{{YYYY}}/', 1, '', 0));
		}
	}

	/**
	 * Test method "IncrementNumberDay".
	 * Test record number resetting with new day.
	 */
	public function testIncrementNumberDay()
	{
		$parts = explode('-', RecordNumber::$dates[0]);
		$actualNumber = 1;
		$prefix = '{{YYYY}}-{{MM}}-{{DD}}/';
		$postfix = '';
		$resetSequence = 'D';
		$curSequence = ($parts[0] . $parts[1] . $parts[2]);
		$result = RecordNumber::setNumber(95, $prefix, $actualNumber, $postfix, 0, $resetSequence, $curSequence);
		$this->assertSame(1, $result);
		$originalRow = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => 95])->one();
		$this->assertSame($prefix, $originalRow['prefix']);
		$this->assertSame(0, $originalRow['leading_zeros']);
		$this->assertSame($postfix, $originalRow['postfix']);
		$this->assertSame($resetSequence, $originalRow['reset_sequence']);
		$this->assertSame($curSequence, $originalRow['cur_sequence']);
		$this->assertSame($actualNumber, $originalRow['cur_id']);
		$currentNumber = 1;
		$currentDate = '';
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$sequence = str_replace('-', '', $date);
			if ($sequence === $currentDate) {
				$currentNumber++;
			} else {
				$currentNumber = 1;
				$currentDate = $sequence;
			}
			$this->assertSame("$date/$currentNumber", RecordNumber::incrementNumber(95));
			$number = RecordNumber::getNumber(95);
			$this->assertSame($currentNumber + 1, $number['sequenceNumber']);
			$this->assertSame(0, $number['leading_zeros']);
			$this->assertSame($resetSequence, $number['reset_sequence']);
			$this->assertSame($sequence, $number['cur_sequence']);
			$this->assertSame($prefix, $number['prefix']);
			$this->assertSame($postfix, $number['postfix']);
		}
	}

	/**
	 * Test method "IncrementNumberMonth".
	 * Test record number resetting with new month.
	 */
	public function testIncrementNumberMonth()
	{
		$actualNumber = 1;
		$prefix = '{{YYYY}}-{{MM}}-{{DD}}/';
		$postfix = '';
		$resetSequence = 'M';
		$curSequence = '';
		$result = RecordNumber::setNumber(95, $prefix, $actualNumber, $postfix, 0, $resetSequence, $curSequence);
		$this->assertSame(1, $result);
		$originalRow = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => 95])->one();
		$this->assertSame($prefix, $originalRow['prefix']);
		$this->assertSame(0, $originalRow['leading_zeros']);
		$this->assertSame($postfix, $originalRow['postfix']);
		$this->assertSame($resetSequence, $originalRow['reset_sequence']);
		$this->assertSame($curSequence, $originalRow['cur_sequence']);
		$this->assertSame($actualNumber, $originalRow['cur_id']);
		$currentNumber = 1;
		$currentDate = '';
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$parts = explode('-', $date);
			$sequence = $parts[0] . $parts[1];
			if ($sequence === $currentDate) {
				$currentNumber++;
			} else {
				$currentNumber = 1;
				$currentDate = $sequence;
			}
			$this->assertSame("$date/$currentNumber", RecordNumber::incrementNumber(95));
			$number = RecordNumber::getNumber(95);
			$this->assertSame($currentNumber + 1, $number['sequenceNumber']);
			$this->assertSame($resetSequence, $number['reset_sequence']);
			$this->assertSame($sequence, $number['cur_sequence']);
			$this->assertSame($prefix, $number['prefix']);
			$this->assertSame(0, $number['leading_zeros']);
			$this->assertSame($postfix, $number['postfix']);
		}
	}

	/**
	 * Test method "IncrementNumberYear".
	 * Test record number resetting with new year.
	 */
	public function testIncrementNumberYear()
	{
		$actualNumber = 1;
		$prefix = '{{YYYY}}-{{MM}}-{{DD}}/';
		$postfix = '';
		$resetSequence = 'Y';
		$curSequence = '';
		$result = RecordNumber::setNumber(95, $prefix, $actualNumber, $postfix, 0, $resetSequence, $curSequence);
		$this->assertSame(1, $result);
		$originalRow = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => 95])->one();
		$this->assertSame($prefix, $originalRow['prefix']);
		$this->assertSame(0, $originalRow['leading_zeros']);
		$this->assertSame($postfix, $originalRow['postfix']);
		$this->assertSame($resetSequence, $originalRow['reset_sequence']);
		$this->assertSame($curSequence, $originalRow['cur_sequence']);
		$this->assertSame($actualNumber, $originalRow['cur_id']);
		$currentNumber = 1;
		$currentDate = '';
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$parts = explode('-', $date);
			$sequence = $parts[0];
			if ($sequence === $currentDate) {
				$currentNumber++;
			} else {
				$currentNumber = 1;
				$currentDate = $sequence;
			}
			$this->assertSame("$date/$currentNumber", RecordNumber::incrementNumber(95));
			$number = RecordNumber::getNumber(95);
			$this->assertSame($currentNumber + 1, $number['sequenceNumber']);
			$this->assertSame($resetSequence, $number['reset_sequence']);
			$this->assertSame($sequence, $number['cur_sequence']);
			$this->assertSame($prefix, $number['prefix']);
			$this->assertSame(0, $number['leading_zeros']);
			$this->assertSame($postfix, $number['postfix']);
		}
	}

	/**
	 * Test method "LeadingZeros"
	 * Test leading zeros in numbers generation.
	 */
	public function testLeadingZeros()
	{
		for ($leadingZeros = 0; $leadingZeros < 7; $leadingZeros++) {
			$actualNumber = 1;
			$prefix = '{{YYYY}}-{{MM}}-{{DD}}/';
			$postfix = '';
			$resetSequence = 'Y';
			$curSequence = '';
			$result = RecordNumber::setNumber(95, $prefix, $actualNumber, $postfix, $leadingZeros, $resetSequence, $curSequence);
			$this->assertSame(1, $result);
			$originalRow = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => 95])->one();
			$this->assertSame($prefix, $originalRow['prefix']);
			$this->assertSame($leadingZeros, $originalRow['leading_zeros']);
			$this->assertSame($postfix, $originalRow['postfix']);
			$this->assertSame($resetSequence, $originalRow['reset_sequence']);
			$this->assertSame($curSequence, $originalRow['cur_sequence']);
			$this->assertSame($actualNumber, $originalRow['cur_id']);
			$currentNumber = 1;
			$currentDate = '';
			foreach (RecordNumber::$dates as $index => $date) {
				RecordNumber::$currentDateIndex = $index;
				$parts = explode('-', $date);
				$sequence = $parts[0];
				if ($sequence === $currentDate) {
					$currentNumber++;
				} else {
					$currentNumber = 1;
					$currentDate = $sequence;
				}
				$currentNumber = \str_pad($currentNumber, $leadingZeros, '0', \STR_PAD_LEFT);
				$this->assertSame("$date/$currentNumber", RecordNumber::incrementNumber(95));
				$number = RecordNumber::getNumber(95);
				$this->assertSame($currentNumber + 1, $number['sequenceNumber']);
				$this->assertSame($resetSequence, $number['reset_sequence']);
				$this->assertSame($sequence, $number['cur_sequence']);
				$this->assertSame($prefix, $number['prefix']);
				$this->assertSame($leadingZeros, $number['leading_zeros']);
				$this->assertSame($postfix, $number['postfix']);
			}
		}
	}

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		static::$transaction->rollBack();
		\App\Cache::clear();
	}
}
